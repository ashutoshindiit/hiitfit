<?php 

/* Creating api to get plans */
include_once get_template_directory().'/firejwt/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

add_action( 'rest_api_init', 'hf_get_plans' );
add_action( 'rest_api_init', 'hf_card_payment' );
add_action( 'rest_api_init', 'hf_check_trial_mode' );
add_action( 'rest_api_init', 'hf_check_active_plan' );
add_action( 'rest_api_init', 'hf_term_and_conditions_plan' );
add_action( 'rest_api_init', 'paypal_payment' );
add_action( 'rest_api_init', 'hf_add_reminder' );
add_action( 'rest_api_init', 'hf_get_reminder' );
add_action( 'rest_api_init', 'hf_delete_reminder' );
add_action( 'rest_api_init', 'hf_update_reminder' );
add_action( 'rest_api_init', 'hf_profile_update' );
add_action( 'rest_api_init', 'hf_change_password' );
add_action( 'rest_api_init', 'hf_get_pofile_data' );
add_action( 'rest_api_init', 'hf_live_check' );
add_action( 'rest_api_init', 'hf_stripe_payment' );
add_action( 'rest_api_init', 'hf_general_api' );

function hf_general_api()
{
    register_rest_route(
        'custom-plugin', '/hf_jwt_token/',
        array(
        'methods'  => 'GET',
        'callback' => 'hf_is_apple_expire_callback',
        )
    );  
}

function hf_is_apple_expire_callback($request){
      //$url = "https://api.storekit.itunes.apple.com/inApps/v1/subscriptions/1000000899860324";
        $respose_data = array();
        $user_id = $request['user_id'];
        $lang = $request['lang'];
        $purchaseToken= get_user_meta($user_id,'app_purchase_token',true);   
        if(empty($purchaseToken)){
            return;
        }
        //$url = "https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/".$purchaseToken;
        $url = "https://api.storekit.itunes.apple.com/inApps/v1/subscriptions/".$purchaseToken;
        $expireTime = '';
        $token = hf_jwt_token_callback();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
        "Authorization: Bearer ".$token,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        $info = curl_getinfo($curl);
        $result = json_decode($resp);
        if($result){
          if(isset($result->data[0]->lastTransactions[0]->signedTransactionInfo) && $result->data[0]->lastTransactions[0]->signedTransactionInfo){
              $data = $result->data[0]->lastTransactions[0]->signedTransactionInfo;
              $data = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $data)[1]))));
              $expireTime = $data->expiresDate;
              update_user_meta($user_id,'ios_original_transactionId',$data->originalTransactionId); 
              $respose_data['test1'] = $data;
          }
        }
        
        if($expireTime){
            $expireTime = date('Y-m-d H:i:s', $expireTime/1000. - date("Z"));
            $expire = strtotime($expireTime);
            $today = time();
            if($today >= $expire){
               $respose_data['is_active'] = false;
            } else {
                $respose_data['is_active'] = true;
            }    
        }    
        
        $respose_data['test'] = $result;
        $respose_data['previous_member'] = FALSE;
        $user = new MeprUser( $user_id );
        if($user->ID){
            $recent_trans =  $user->recent_transactions();
            if($recent_trans){
                $respose_data['status'] = 1;
                $respose_data['previous_member'] = TRUE;
            }else{
                $respose_data['status'] = 77;
                $respose_data['previous_member'] = FALSE;
            }    
            
            if(isset($request['tz']) && $request['tz']){
                date_default_timezone_set($request['tz']);
            }
            $respose_data['expire'] = '';
            if(isset($expire) && $expire){
                $respose_data['expire'] =  date("d-m-Y, g:i a",$expire);
            }
            
    
        }
        $user = new MeprUser( $user_id );
        wp_set_current_user($user_id);
        $subsdata = MeprUtils::get_currentuserinfo();
        $subscriptions = $subsdata->active_product_subscriptions();
        $subsdata = array();
        if($subscriptions){
        $subscriptions = array_unique($subscriptions);
            foreach($subscriptions as $subscription){
                    $post = get_post($subscription); //assuming $id has been initialized
                    setup_postdata($post);
                    $post_data = array();
                    $post_data = $post;
                    if($lang){
                        $translations = pll_get_post_translations($subscription);
                        $id = $translations[$lang];
                        if( $id ){
                            $post->post_title = get_the_title($id);
                        }
                    }                
                    $post->meta = get_post_meta($post->ID);
                    $subsdata[0] = $post;
                    wp_reset_postdata();
                    
            }
        }    
        $respose_data['membership'] = $subsdata;
        $respose_data['appType'] = 'ios';
        return $respose_data;        
      
}

function hf_jwt_token_callback()
{
 
$privateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQgBnlL6Sq47UhA5Jyv
RF3vblFBnlEiwSoWmUSlNHSdGOegCgYIKoZIzj0DAQehRANCAAQYzxHJBN3LD+Gv
m4Fj5MscdxqUP/DF3MBNRfl2uP8hL90P4CqclrTQxAvIfPxTP8KDI5YiVtDKYLj0
7hTQ+sX4
-----END PRIVATE KEY-----
EOD;
	$Key_ID = '65X697CH44';
	$payload = array(
		"iss" => "ce8119d1-edd9-46ea-867a-81a3978ca50b",
		"exp" => strtotime('now') + 60*60,
		"aud" => "appstoreconnect-v1",
		"nonce" => "6edffe66-b482-11eb-8529-0242ac130003",
		"bid" => "com.hiitfit.app"
	);

	$jwt = JWT::encode($payload, $privateKey,'ES256', $Key_ID);

    return $jwt;

}

function hf_stripe_payment()
{
    register_rest_route(
        'custom-plugin', '/hf_stripe_payment/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_stripe_payment_callback',
        )
    );  
    register_rest_route(
        'custom-plugin', '/hf_stripe_payment_token/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_stripe_payment_token_callback',
        )
    );  
    
    register_rest_route(
        'custom-plugin', '/in_app_purchase/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_in_app_purchase_callback',
        )
    );  
    
    register_rest_route(
        'custom-plugin', '/is_app_expire/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_is_app_expire_callback',
        )
    ); 
    
    register_rest_route(
        'custom-plugin', '/live_tab/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_is_live_tab_callback',
        )
    ); 
}

function hf_is_live_tab_callback()
{
   
    $res['status'] = (get_option('hf_live_tab')) ? get_option('hf_live_tab') : 0 ;
    return wp_send_json($res); 
}


function hf_is_android_expire_callback($request)
{
    $data = array();
    $user_id = $request['user_id'];
    $lang = $request['lang'];
    $appid='com.hiitfit.app';
    $productID= get_user_meta($user_id,'app_store_id',true);
    $purchaseToken= get_user_meta($user_id,'app_purchase_token',true);
    if(empty($purchaseToken)){
        return;
    }
    //$refreshToken='1//04lMGwZr4amBsCgYIARAAGAQSNwF-L9Irr0Q1Ewz-1YyyHFpZdROYrs_Ctep710dR5mbUHknWW5aCDl2taooLe-gtHDJo-as0n5k';
    $refreshToken='1//04k1q2W_n_ztjCgYIARAAGAQSNwF-L9IrB13gmI3zU-k6dEeLqpbFudt8819XeaHs91OD63ZLDWyWHVM_rYUFpzkLq-vS7guk96U';
    
    $clientSecret='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';
    $clientID='1092801683270-9emu7mmd2bbgs9hfvl3gghi2mdaugjr6.apps.googleusercontent.com';
    
    $ch = curl_init();
    $TOKEN_URL = "https://accounts.google.com/o/oauth2/token";
    $VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/".
        $appid."/purchases/subscriptions/".
        $productID."/tokens/".$purchaseToken;
    
    $input_fields = 'refresh_token='.$refreshToken.
        '&client_secret='.$clientSecret.
        '&client_id='.$clientID.
        '&grant_type=refresh_token';
    
    //Request to google oauth for authentication
    curl_setopt($ch, CURLOPT_URL, $TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
   
    $result = json_decode($result, true);
    
    if (!isset($result["access_token"]) || !$result["access_token"]) {
     //error   
         return wp_send_json( $result);
    }
    
    //request to play store with the access token from the authentication request
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$VALIDATE_URL."?access_token=".$result["access_token"]);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $result = json_decode($result, true);

    if (isset( $result["error"]) || $result["error"] != null) {
        return $result;
    }
    $data['test'] = $result;
    $expireTime = date('Y-m-d H:i:s', $result["expiryTimeMillis"]/1000. - date("Z"));
    $expire = strtotime($expireTime);
    $today = time();
    if($today >= $expire){
       $data['is_active'] = false;
    } else {
        $data['is_active'] = true;
    }
    
    $user = new MeprUser( $user_id );
    if($user->ID){
        $recent_trans =  $user->recent_transactions();
        if($recent_trans){
            $data['status'] = 1;
            $data['previous_member'] = TRUE;
        }else{
            $data['status'] = 77;
            $data['previous_member'] = FALSE;
        }    
        if(isset($request['tz']) && $request['tz']){
        date_default_timezone_set($request['tz']);
        }
        $data['expire'] = date("d-m-Y, g:i a",$expire);  
    }
    $user = new MeprUser( $user_id );
    wp_set_current_user($user_id);
    $subsdata = MeprUtils::get_currentuserinfo();
    $subscriptions = $subsdata->active_product_subscriptions();
    $subsdata = array();
    if($subscriptions){
    $subscriptions = array_unique($subscriptions);
        foreach($subscriptions as $subscription){
                $post = get_post($subscription); //assuming $id has been initialized
                setup_postdata($post);
                $post_data = array();
                $post_data = $post;
                if($lang){
                    $translations = pll_get_post_translations($subscription);
                    $id = $translations[$lang];
                    if( $id ){
                        $post->post_title = get_the_title($id);
                    }
                }                
                $post->meta = get_post_meta($post->ID);
                $subsdata[0] = $post;
                wp_reset_postdata();
                
        }
    }    
    $data['membership'] = $subsdata;
    $data['appType'] = 'android'; 
    return $data;
}

function hf_stripe_payment_token_callback($request)
{
    require get_template_directory().'/vendor/autoload.php'; 
    $stripe = \Stripe\Stripe::setApiKey('sk_test_51JkNhYJATzc8RL1emoR7G7HfuNM6M568p4A0U4VqEL1craaTK4WU6GnQ6dvUm8LdliXjRh9y9gH6YaKO5u0bdTf800XMLHJULr');
    try{
    $data['data'] = \Stripe\Token::create(array(
      "card" => array(
        "number" => "4242424242424242",
        "exp_month" => 1,
        "exp_year" => 2022,
        "cvc" => "314"
      )
    ));
} catch(\Stripe\Exception\CardException $e) {  
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\RateLimitException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\AuthenticationException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $data['error']=  $e->getMessage();
    } catch (Exception $e) {
        $data['error']=  $e->getMessage();
    }    

    return wp_send_json($data);
}

function get_ios_expire_date($paymentToken, $user_id){
        $respose_data = array();
        $purchaseToken= $paymentToken;
        if(empty($purchaseToken)){
            return;
        }
        $expire = '';
        //$url = "https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/".$purchaseToken;
        $url = "https://api.storekit.itunes.apple.com/inApps/v1/subscriptions/".$purchaseToken;
        
        $expireTime = '';
        $token = hf_jwt_token_callback();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
        "Authorization: Bearer ".$token,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        $info = curl_getinfo($curl);
        $result = json_decode($resp);
        if($result){
          if(isset($result->data[0]->lastTransactions[0]->signedTransactionInfo) && $result->data[0]->lastTransactions[0]->signedTransactionInfo){
              $data = $result->data[0]->lastTransactions[0]->signedTransactionInfo;
              $data = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $data)[1]))));
              $expireTime = $data->expiresDate;
              update_user_meta($user_id,'ios_original_transactionId',$data->originalTransactionId); 
          }
        }
        
        if($expireTime){
            $expireTime = date('Y-m-d H:i:s', $expireTime/1000. - date("Z"));
            $expire = strtotime($expireTime);
        }   
        
        return $expire;
}

function get_android_expire_date($paymentToken,$store_id)
{
    $data = array();
    $appid='com.hiitfit.app';
    $productID= $store_id;
    $purchaseToken= $paymentToken;
    $expire = '';
    if(empty($purchaseToken)){
        return;
    }
    $refreshToken='1//04lMGwZr4amBsCgYIARAAGAQSNwF-L9Irr0Q1Ewz-1YyyHFpZdROYrs_Ctep710dR5mbUHknWW5aCDl2taooLe-gtHDJo-as0n5k';
    
    $clientSecret='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';
    $clientID='1092801683270-9emu7mmd2bbgs9hfvl3gghi2mdaugjr6.apps.googleusercontent.com';
    
    $ch = curl_init();
    $TOKEN_URL = "https://accounts.google.com/o/oauth2/token";
    $VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/".
        $appid."/purchases/subscriptions/".
        $productID."/tokens/".$purchaseToken;
    
    $input_fields = 'refresh_token='.$refreshToken.
        '&client_secret='.$clientSecret.
        '&client_id='.$clientID.
        '&grant_type=refresh_token';
    
    //Request to google oauth for authentication
    curl_setopt($ch, CURLOPT_URL, $TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
   
    $result = json_decode($result, true);
    
    if (!isset($result["access_token"]) || !$result["access_token"]) {
     //error   
         return '';
    }
    
    //request to play store with the access token from the authentication request
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$VALIDATE_URL."?access_token=".$result["access_token"]);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $result = json_decode($result, true);

    if (isset( $result["error"]) || $result["error"] != null) {
        return $result;
    }
    if(isset($result["expiryTimeMillis"]) && $result["expiryTimeMillis"]){
        $expireTime = date('Y-m-d H:i:s', $result["expiryTimeMillis"]/1000. - date("Z"));
        $expire = strtotime($expireTime);
    }
    return $expire;
}

function hf_in_app_purchase_callback($request)
{
    global $wpdb;
    $history_data = array();
    $history_data['user_id'] = $request['user_id'];
    $history_data['purchase_token'] = $request['purchase_token'];
    $history_data['data'] = serialize($request);
    $wpdb->insert('wp_user_history', $history_data);
    
    $store_id  = $request['store_id'];
    $membership_id = get_post_id_by_store_id( $store_id );
    $purchaseToken = $request['purchase_token'];
    $payment_type = $request['payment_type'];
    $expire = '';
    if($payment_type == 'ios' && $purchaseToken){
        $expire = get_ios_expire_date($purchaseToken, $request['user_id']);
    }elseif($payment_type == "android" && $purchaseToken){
        $expire = get_android_expire_date($purchaseToken,$store_id);
    }
    $expiry_date = $request['expiry_date'];
    $user_id = $request['user_id'];
    if(!$user_id) return apiError('User id does not exist');
    if($membership_id){
        update_user_meta($user_id,'app_payment_type',$payment_type);
        update_user_meta($user_id,'app_purchase_token',$purchaseToken);
        update_user_meta($user_id,'app_store_id',$store_id);
        $user = get_userdata($user_id);
	    $data['status'] = true;
	    $data['plan_id'] = $membership_id;
        $complete_str = 'complete';
        
        $sub = new MeprSubscription();
        $sub->user_id = $user_id;
        $sub->product_id = $membership_id;
        $sub->price = $request['price'];
        $sub->total = $request['total'];
        $sub->status = MeprSubscription::$active_str;
        $sub_id = $sub->store();        

        
        $txn = new MeprTransaction();
        $txn->user_id    = $user_id;
        $txn->product_id = $membership_id;
        $txn->trans_num = $purchaseToken;
        $txn->response =  $purchaseToken;
        $txn->gateway    = $payment_type;
        $txn->subscription_id = $sub_id;
        $sub->price = $request['price'];
        $sub->total = $request['total'];
        $txn->status    = MeprTransaction::$complete_str;
        if($expire){
            $txn->expires_at = gmdate('Y-m-d H:i:s', $expire);
        }
        $upgrade = $txn->is_upgrade();
        $downgrade = $txn->is_downgrade();
        $txn->maybe_cancel_old_sub();        
        $txn->store();
        $obj = MeprTransaction::get_one_by_trans_num($purchaseToken);
        if(is_object($obj) and isset($obj->id)) {
            $txn = new MeprTransaction();
            $txn->load_data($obj);
            $usr = $txn->user();
            $txn->status    = MeprTransaction::$complete_str;
            $upgrade = $txn->is_upgrade();
            $downgrade = $txn->is_downgrade();
            $txn->maybe_cancel_old_sub();
            $result = $txn->store();
            $prd = $txn->product();
        }  
    }else{
        return apiError("Store id does not exist.");
    }
    return wp_send_json( $data);
}

function apiError($msg)
{
    $data = array();
    $data['status'] = false;
    $data['message'] = $msg;
    return wp_send_json( $data);
}



function get_post_id_by_store_id($value) {
	global $wpdb;
	$key = 'store_id';
	$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."'");
	if (is_array($meta) && !empty($meta) && isset($meta[0])) {
		$meta = $meta[0];
	}		
	if (is_object($meta)) {
		return $meta->post_id;
	}
	else {
		return false;
	}
}

function hf_stripe_payment_callback($request)
{
    $token = $request['token'];
    $user_id = $request['user_id'];
    $amount = $request['amount'];
    $membership_id  = $request['membership_id'];
    $data = array();
    require get_template_directory().'/vendor/autoload.php'; 
    $stripe = \Stripe\Stripe::setApiKey('sk_test_51JkNhYJATzc8RL1emoR7G7HfuNM6M568p4A0U4VqEL1craaTK4WU6GnQ6dvUm8LdliXjRh9y9gH6YaKO5u0bdTf800XMLHJULr');
    try {
        
        $user = get_userdata($user_id);
        if(!$user){
            $data['error'] = "User not exist!";
            return wp_send_json($data);
        }
        $user_data = array(
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email'     => $user->user_email,
            'user_login' => $user->user_login,
        );
        $customer_id = get_user_meta($user_id,'stripe_customer_id',true);
        if($customer_id){
             try {
                $customer_data = \Stripe\Customer::retrieve($customer_id);
                 
                if(!$customer_data){
                    $customer_id = '';
                }
            }catch(Exception $e) { 
                $customer = \Stripe\Customer::create(array( 
        			'name' => $user->display_name,
                    'email' => $user->user_email, 
                    'source'  => $token 
                ));  
                $customer_id = $customer->id;
                update_user_meta($user_id,'stripe_customer_id',$customer_id);
        	} 
        }
        
        if(!$customer_id)
        {
            $customer = \Stripe\Customer::create(array( 
    			'name' => $user->display_name,
                'email' => $user->user_email, 
                'source'  => $token 
            ));  
            $customer_id = $customer->id;
            update_user_meta($user_id,'stripe_customer_id',$customer_id);
        }else{
           \Stripe\Customer::update(
              $customer_id,
              ['source'  => $token ]
            );            
        }
        $membership =  new MeprProduct($membership_id);
        if(!isset($membership->ID) && !$membership->ID){
            $data['status'] = false;
            $data['error']=  "Membership id not exist"; 
        }
        $plan_id = get_post_meta($membership_id,'_stripe_plan_id',true);
        if($plan_id)
        {   
            try{
                $plan_data = \Stripe\Plan::create($plan_id);  
            }catch(Exception $e) { 
                if( $membership->period_type == 'months' )
                    $interval = 'month';
                else if( $membership->period_type == 'years' )
                    $interval = 'year';
                else if( $membership->period_type == 'weeks' )
                    $interval = 'week';
                    
                $amount = $membership->price*100;
                $plan_data = array( 
        			"product" => [ 
        				"name" => $membership->post_title 
        			], 
        			"amount" => $amount, 
        			"currency" => 'USD', 
        			"interval" => $interval, 
        			"interval_count" => $membership->period
        		);  
        		
                if($membership->trial == 1 && $membership->trial_days > 0)
                {
                    $plan_data['trial_period_days'] = $membership->trial_days;
                }
                
        		$plan = \Stripe\Plan::create($plan_data);        
        		$plan_id = $plan->id;
        		add_post_meta($membership_id,'_stripe_plan_id',$plan_id);                
            }
                  
        }
        if(!$plan_id)
        {
            if( $membership->period_type == 'months' )
                $interval = 'month';
            else if( $membership->period_type == 'years' )
                $interval = 'year';
            else if( $membership->period_type == 'weeks' )
                $interval = 'week';
                
            $amount = $membership->price*100;
            $plan_data = array( 
    			"product" => [ 
    				"name" => $membership->post_title 
    			], 
    			"amount" => $amount, 
    			"currency" => 'USD', 
    			"interval" => $interval, 
    			"interval_count" => $membership->period
    		);  
    		
            if($membership->trial == 1 && $membership->trial_days > 0)
            {
                $plan_data['trial_period_days'] = $membership->trial_days;
            }
            
    		$plan = \Stripe\Plan::create($plan_data);        
    		$plan_id = $plan->id;
    		add_post_meta($membership_id,'_stripe_plan_id',$plan_id);
    		
        }
        if($plan_id && $customer_id)
        {
    		$subscription = \Stripe\Subscription::create(array( 
    			"customer" => $customer_id, 
    			"items" => array( 
    				array( 
    					"plan" => $plan_id, 
    				), 
    			),
    			"metadata" => array(
    			            "Payment Mode" => "Apple Pay"
    			 )
    		));    
    		if($subscription){
    		    $data['status'] = true;
    		    $data['subscription']  = $subscription;
                $complete_str = 'complete';
                $txn = new MeprTransaction();
                $txn->user_id    = $user_id;
                $txn->product_id = $membership_id;
                $txn->trans_num = $subscription->id;
                $txn->response = json_encode($subscription);
                $txn->gateway    = "API Stripe";
                $txn->subscription_id = $subscription->customer;
                $txn->store();
                $obj = MeprTransaction::get_one_by_trans_num($subscription->id);
                if(is_object($obj) and isset($obj->id)) {
                    $txn = new MeprTransaction();
                    $txn->load_data($obj);
                    $usr = $txn->user();
                    $txn->status    = MeprTransaction::$complete_str;
                    $txn->response  = json_encode($subscription);
                    $upgrade = $txn->is_upgrade();
                    $downgrade = $txn->is_downgrade();
            
                    $txn->maybe_cancel_old_sub();
                    $result = $txn->store();
                    $prd = $txn->product();
                }       
    		}
        }
        return wp_send_json( $data);

    } catch(\Stripe\Exception\CardException $e) {  
        $data['status'] = false;
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\RateLimitException $e) {
        $data['status'] = false;
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        $data['status'] = false;
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\AuthenticationException $e) {
        $data['status'] = false;
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        $data['status'] = false;
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $data['status'] = false;
        $data['error']=  $e->getMessage();
    } catch (Exception $e) {
        $data['status'] = false;
        $data['error']=  $e->getMessage();
    }    

    return wp_send_json($data);    
}

function hf_live_check()
{
    register_rest_route(
        'custom-plugin', '/hf_live_check/',
        array(
        'methods'  => 'GET',
        'callback' => 'hf_live_check_callback',
        )
    );
}

function hf_live_check_callback()
{

    $data = get_option('hf_live_check');
    $res['data'] = $data;
    return wp_send_json($res); 
}

function paypal_payment()
{
    register_rest_route(
        'custom-plugin', '/store_paypal_payment_info/',
        array(
        'methods'  => 'POST',
        'callback' => 'store_paypal_payment_info_callback',
        )
    );
}

function hf_get_plans()
{
    register_rest_route(
        'custom-plugin', '/get_plans/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_get_plan_callback',
        )
    );
    
    register_rest_route(
        'custom-plugin', '/test_get_plans/',
        array(
        'methods'  => 'POST',
        'callback' => 'test_get_plan',
        )
    );
}

function test_get_plan()
{
    $membership =  new MeprProduct(1581);
    return wp_send_json($membership->rec); 
}

function hf_card_payment(){
    register_rest_route(
        'custom-plugin', '/card_payment/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_card_payment_callback',
        )
    );
}

function hf_check_trial_mode(){
    register_rest_route(
        'custom-plugin', '/check_trial_mode/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_check_trial_mode_callback',
        )
    );
}


function hf_check_active_plan(){
    register_rest_route(
        'custom-plugin', '/check_active_plan/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_check_active_plan_callback',
        )
    );
}

function hf_term_and_conditions_plan(){
    register_rest_route(
        'custom-plugin', '/term_and_conditions/',
        array(
        'methods'  => 'GET',
        'callback' => 'hf_term_and_conditions_callback',
        )
    );
}

function hf_add_reminder()
{
    register_rest_route(
        'custom-plugin', '/add_reminder/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_add_reminder_callback',
        'args' => array(
            'user_id' => array(
                'required' => true,
                'type' => 'integer',
            ),
            'post_id' => array(
                'required' => true,
                'type' => 'integer',
            ), 
            'video_id' => array(
                'required' => true,
                'type' => 'integer',
            ),   
            'post_name' => array(
                'required' => true,
            ),      
            'title' => array(
                'required' => true,
            ),         
            'start_date' => array(
                'required' => true,
            ),         
            'end_date' => array(
                'required' => true,
            ),              
        )
        )
    );
}

function hf_get_reminder()
{
    register_rest_route(
        'custom-plugin', '/get_reminder/',
        array(
        'methods'  => 'POST',
        'callback' => 'hf_get_reminder_callback',
        'args' => array(
            'user_id' => array(
                'required' => true,
                'type' => 'integer',
            )         
        )
        )
    );
}

function hf_delete_reminder()
{
    register_rest_route(
        'custom-plugin', '/delete_reminder/(?P<id>\d+)',
        array(
        'methods'  => 'DELETE',
        'callback' => 'hf_delete_reminder_callback',
        )
    );    
}

function hf_update_reminder()
{
    register_rest_route(
        'custom-plugin', '/update_reminder/(?P<id>\d+)',
        array(
            'methods'  => 'PUT',
            'callback' => 'hf_update_reminder_callback',
            'args' => array(
                'user_id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
                'post_id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),   
                'post_name' => array(
                    'required' => true,
                ),      
                'title' => array(
                    'required' => true,
                ),         
                'start_date' => array(
                    'required' => true,
                ),           
                'end_date' => array(
                    'required' => true,
                ),            
            )
        )
    );    
}

function hf_profile_update()
{
    register_rest_route(
        'custom-plugin', '/profile_update/(?P<id>\d+)',
        array(
            'methods'  => 'POST',
            'callback' => 'hf_profile_update_callback',
        )
    );    
}

function hf_change_password()
{
    register_rest_route(
        'custom-plugin', '/change_password/(?P<id>\d+)',
        array(
            'methods'  => 'POST',
            'callback' => 'hf_change_password_callback',
        )
    );    
}

function hf_get_pofile_data()
{
    register_rest_route(
        'custom-plugin', '/get_pofile_data/(?P<id>\d+)',
        array(
            'methods'  => 'POST',
            'callback' => 'hf_get_pofile_data_callback',
        )
    );    
}

function hf_change_password_callback($request){
    $user_id = $request['id'];
    $userData = get_userdata($user_id);
    if(!$userData){
        $data['status'] = 0;
        $data['msg'] = "User is not exists";
    }else{
        if(!isset($request['oldpassword']) || !$request['oldpassword']){
            $data['status'] = 0;
            $data['msg'] = "Old password is required.";
        }elseif(!isset($request['newpassword']) || !$request['newpassword']){
            $data['status'] = 0;
            $data['msg'] = "New password is required.";           
        }else{
            $user = get_user_by('id', $user_id);
            $x = wp_check_password( $request['oldpassword'] , $user->user_pass, $user_id );
            if($x)
            {
                $udata['ID'] = $user->data->ID;
                $udata['user_pass'] = $request['newpassword'];
                $uid = wp_update_user( $udata );
                if($uid) 
                {
                    $data['status'] = 1;
                    $data['msg'] = "The password has been updated successfully";   
                } else {
                    $data['status'] = 0;
                    $data['msg'] = "Sorry! Failed to update your account details.";    
                }
            } 
            else 
            {
                $data['status'] = 2;
                $data['msg'] = "Old Password doesn't match the existing password";    
            }            
        }
    }
    $res['data'] = $data;
    return wp_send_json($res); 
}

function hf_get_pofile_data_callback($request){
    $user_id = $request['id'];
    $userData = get_userdata($user_id);
    if(!$userData){
        $data['status'] = 0;
        $data['msg'] = "User is not exists";
    }else{
        $image = get_user_meta($user_id,'profile_pic',true);
        $image = ($image) ? wp_get_attachment_url($image) : '';
        $userData->data->profile_pic =  $image;
        $userData->data->signup_agrrement =   get_user_meta($user_id,'signup_agrrement',true);
        $userData->data->user_gender =   get_user_meta($user_id,'user_gender',true);
        $userData->data->user_weight =   get_user_meta($user_id,'user_weight',true);
        $userData->data->user_height =   get_user_meta($user_id,'user_height',true);
        $userData->data->dob =   get_user_meta($user_id,'dob',true);
        $data['status'] = 1;
        $data['userData'] =  $userData;
    }
    $res['data'] = $data;
    return wp_send_json($res); 
}

function hf_profile_update_callback($request){
    
    global $wpdb;
    $user_id = $request['id'];
    $userData = get_userdata($user_id);
    if(!$userData){
        $data['status'] = 0;
        $data['msg'] = "User is not exists";
    }else{

        if(isset($request["fullname"])){
            $wpdb->update($wpdb->users, array('display_name' => esc_attr( $request['fullname'] ) ), array('ID' => $user_id));
        }
        if(isset($request["email"]) && $request["email"]){
            if(email_exists($request["email"]) && $userData->user_email != $request["email"]){
                $result['status'] = 0;
                $result['msg'] = "Sorry, that email already exists!";
                $response['data'] = $result;
                return wp_send_json($response);
            }else{
                $wpdb->update($wpdb->users, array('user_login' => esc_attr( $request['email'] ) ,'user_email' => esc_attr( $request['email'] )), array('ID' => $user_id));
            }            
        }
        if(isset($_FILES['file']) &&  $_FILES['file']){
            $wordpress_upload_dir = wp_upload_dir();
            $i = 1; 
            $profilepicture = $_FILES['file'];
            $new_file_path = $wordpress_upload_dir['path'] . '/' . $profilepicture['name'];
            $new_file_mime = mime_content_type( $profilepicture['tmp_name'] );
            
            if( empty( $profilepicture ) )
                die( 'File is not selected.' );
            
            if( $profilepicture['error'] )
                die( $profilepicture['error'] );
                
            if( $profilepicture['size'] > wp_max_upload_size() )
                die( 'It is too large than expected.' );
                
            if( !in_array( $new_file_mime, get_allowed_mime_types() ) )
                die( 'WordPress doesn\'t allow this type of uploads.' );
                
            while( file_exists( $new_file_path ) ) {
                $i++;
                $new_file_path = $wordpress_upload_dir['path'] . '/' . $i . '_' . $profilepicture['name'];
            }
        
            if( move_uploaded_file( $profilepicture['tmp_name'], $new_file_path ) ) {
                
            
                $upload_id = wp_insert_attachment( array(
                    'guid'           => $new_file_path, 
                    'post_mime_type' => $new_file_mime,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $profilepicture['name'] ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ), $new_file_path );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );

                update_user_meta($user_id, 'profile_pic', $upload_id);
            
            }
        }
        if(isset($request["dob"]) && $request["dob"]){
            update_user_meta($user_id, 'dob', $request["dob"]);
        }
        $data['status'] = 1;
        $data['msg'] = "User profile has been updated successfully";
        
        $userNewData = get_userdata($user_id);
        $userNewData->dob = get_user_meta( $user_id, 'dob', true);
        $userNewData->data->display_name = $request["fullname"];
        $profile_image =  get_user_meta( $user_id, 'profile_pic', true);
        if(!empty($profile_image)){
            $userNewData->profile_image = wp_get_attachment_image_url($profile_image);
        }else{
            $userNewData->profile_image = "";
        }
        $data['userData'] =  $userNewData;
    }
    $res['data'] = $data;
    return wp_send_json($res); 
}

function hf_update_reminder_callback($request)
{
    $reminder_id =  $request['id'];
    global $wpdb;
    $table_name  = $wpdb->prefix."reminder";
    $description = (isset($request['description'])) ? $request['description'] : '';
    $res = $wpdb->query( $wpdb->prepare("UPDATE $table_name 
               SET user_id = %s, post_id =%s, post_name = %s, title = %s, video_id = %s, start_date = %s, end_date = %s, description=%s 
            WHERE id = %s",$request['user_id'],$request['post_id'],$request['post_name'],$request['title'],$request['video_id'],$request['start_date'],$request['end_date'],$description, $reminder_id)
    );   
    if($res){
        $result['status'] = 1;
        $result['message'] = 'Reminder has been updated successfully !';
    }else{
        $result['status'] = 0;
        $result['message'] = 'Reminder id does no exists!';       
    }
    return wp_send_json($result);   
}

function hf_delete_reminder_callback($request){
    $id = $request['id'];
    global $wpdb;
    $tablename = $wpdb->prefix.'reminder';
    $res = $wpdb->delete( $tablename, array( 'id' => $id ) );
    if($res){
        $result['status'] = 1;
        $result['message'] = 'Reminder has been deleted successfully !';
    }else{
        $result['status'] = 0;
        $result['message'] = 'Reminder id does no exists!';       
    }
    return wp_send_json($result);    
}

function hf_add_reminder_callback($request){
    $result = array();
    global $wpdb;
    $tablename = $wpdb->prefix.'reminder';
    $description = (isset($request['description'])) ? $request['description'] : '';
    $result_check  = $wpdb->insert( 
        $tablename, 
        array(
            'user_id'=>$request['user_id'],
            'post_id'=>$request['post_id'],
            'video_id'=>$request['video_id'],
            'post_name'=>$request['post_name'],
            'title'=>$request['title'], 
            'start_date'=>$request['start_date'], 
            'end_date'=>$request['end_date'], 
            'description'=>$description, 
        ),
        array( '%s', '%s', '%s', '%s', '%s', '%s' ) 
    );    
    if($result_check){
        $result['status'] = 1;
        $result['message'] = 'Reminder has been added successfully !';
     }else{
        $result['status'] = 0;
        $result['message'] = 'Unable to save data. Something went wrong!';       
     }
    return wp_send_json($result);
}

function hf_get_reminder_callback($request){
    $result = array();
    global $wpdb;
    $tablename = $wpdb->prefix.'reminder';
    $id =  $request['user_id'];
    $sql = "SELECT * FROM $tablename where user_id =$id";
    
    if(isset($request['month']) && $request['month']){
        $month = $request['month'];
        $sql .= " AND MONTH(start_date) =  $month";       
    }
    if(isset($request['year']) && $request['year']){
        $year = $request['year'];
        $sql .= " AND YEAR(start_date) =  $year";
    }
    $res = $wpdb->get_results($sql); 
    $data = array();
    $related_video = '';
    if($res){
        foreach($res as $key => $r){
            $videos = get_field('related_video_section',$r->post_id);
            foreach($videos as $video){
                if($video['related_video_id'] ==$r->video_id){
                    $related_video = $video;
                }
            }
            $r->related_video_section =  $related_video;
            $data[] = $r;
        }
    }
    $result['status'] = 1;
    $result['data'] = $data;
    return wp_send_json($result);
}

function store_paypal_payment_info_callback($request){
    
    $data = $request;
    $complete_str = 'complete';
    $charge = 'data';
    $data['subscription_id'] = 24;
    
    $txn = new MeprTransaction();
    $txn->user_id    = $data['user_id'];
    $txn->product_id = $data['product_id'];
    $txn->amount =  ($data['amount']/100);
    $txn->total =  ($data['amount']/100);
    $txn->trans_num = $data['trans_num'];
    $txn->response = json_encode($charge);
    $txn->gateway    = $data['gateway'];
    $txn->subscription_id = $data['subscription_id'];
    $txn->store();
    $obj = MeprTransaction::get_one_by_trans_num($data['trans_num']);
    if(is_object($obj) and isset($obj->id)) {
        $txn = new MeprTransaction();
        $txn->load_data($obj);
        $usr = $txn->user();

        $txn->status    = MeprTransaction::$complete_str;
        $txn->response  = json_encode($charge);
        

        // This will only work before maybe_cancel_old_sub is run
        $upgrade = $txn->is_upgrade();
        $downgrade = $txn->is_downgrade();
        $txn->maybe_cancel_old_sub();
        $result = $txn->store();
        $prd = $txn->product();
        $data['data']['membership']['transaction_id'] = $result;
        
        $res['status'] = 1;
        $res['meprTransaction_id'] = $result;
        //$res['prd'] = $prd;
        //$res['usr'] = $usr;
      }
    return wp_send_json($res);
}


function hf_get_plan_callback($request)
{
    $args['post_type'] = 'memberpressproduct';                       
    $args['posts_per_page'] = '-1';    
    $args['orderby'] = 'date';    
    $args['order'] = 'ASC';  
    if(isset($request['lang']) && $request['lang']){
        $args['lang'] = $request['lang']; 
    }    
    $appType = '';
    if(isset($request['appType']) && $request['appType']){
        $appType = $request['appType']; 
    }   
    $plans = new WP_Query($args);
   
    $plans_data = array();
    if($plans->have_posts() ) {
        while($plans->have_posts() ) {
            $plans->the_post();
            $post_id = get_the_ID();
            if($post_id != 1581){
                if($appType){
                    $appTypeData = get_post_meta($post_id,'app_plan',true);
                    if(!in_array($appType,$appTypeData)){
                        continue;
                    }
                }
                
                $data = array();
                $membership =  new MeprProduct($post_id);
                $data = (array) $membership->rec; 
                $data['id'] = $post_id;
                $data['term'] = MeprProductsHelper::format_currency($membership, true, null, false);;
                $data['title'] = get_the_title();
                $data['price'] = get_post_meta($post_id,'_mepr_product_price',true);
                $data['storeID'] = get_post_meta($post_id,'store_id',true);
                $data['period'] = get_post_meta($post_id,'_mepr_product_period',true);
                $data['period_type'] = get_post_meta($post_id,'_mepr_product_period_type',true);
                $plans_data[] = $data;                
            }
        }
    }

    return wp_send_json(['status'=>true,'data'=>$plans_data]);

}
/* Creating a payment for memebership */

function hf_card_payment_callback($request){
    $card_number    = $request['card_number'];
    $exp_month      = $request['exp_month'];
    $exp_year       = $request['exp_year'];
    $cvc            = $request['cvc'];
    $membership_id  = $request['membership_id'];
    $user_id        = $request['user_id'];
    $amount         = $request['amount'];

    if(!$membership_id){
        $data['error'] = "Membership ID is required";
        return wp_send_json($data);
    }

    if(!$user_id){
        $data['error'] = "User ID is required";
        return wp_send_json($data);
    } 

    if(!$amount){
        $data['error'] = "Amount is required";
        return wp_send_json($data);
    }

    require get_template_directory().'/vendor/autoload.php'; 
    $stripe = \Stripe\Stripe::setApiKey('sk_test_51JkNhYJATzc8RL1emoR7G7HfuNM6M568p4A0U4VqEL1craaTK4WU6GnQ6dvUm8LdliXjRh9y9gH6YaKO5u0bdTf800XMLHJULr');
    try {
        $token = \Stripe\Token::create(array(
            "card" => array(
              "number" => $card_number,
              "exp_month" => $exp_month,
              "exp_year" => $exp_year,
              "cvc" =>  $cvc
            )
        ));
        $user = get_userdata($user_id);
        if(!$user){
            $data['error'] = "User not exist!";
            return wp_send_json($data);
        }
        $user_data = array(
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'amount'    => $amount,
            'email'     => $user->user_email,
            'user_login' => $user->user_login,
        );
        
        $stripe_amount = $amount * 100;

		$customer = \Stripe\Customer::create(array(
            'name' => $user->display_name,
            'email' => $user->user_email,
            'source'  => $token,
        ));

        $charge = \Stripe\Charge::create([
            'customer' =>  $customer->id,
            'amount' => $stripe_amount,
            'currency' => 'usd',
            'description' => 'Subscription Charge',
        ]);

        if(!empty($charge)){
            $data['data']['transaction']['id']= $charge->id;
            $data['data']['transaction']['status']= $charge->status;
            $data['data']['transaction']['amount']= $charge->amount;
            $data['data']['transaction']['customer']= $charge->customer;
            $txn = new MeprTransaction();
            $txn->user_id    = $user_id;
            $txn->product_id = $membership_id;
            $txn->amount =  ($charge->amount/100);
            $txn->total =  ($charge->amount/100);
            $txn->trans_num = $charge->id;
            $txn->response = json_encode($charge);
            $txn->gateway    = "API Stripe";
            $txn->subscription_id = $charge->customer;
            $txn->store();
            $obj = MeprTransaction::get_one_by_trans_num($charge->id);
            if(is_object($obj) and isset($obj->id)) {
                $txn = new MeprTransaction();
                $txn->load_data($obj);
                $usr = $txn->user();
        
                $txn->status    = MeprTransaction::$complete_str;
                $txn->response  = json_encode($charge);
        
                // This will only work before maybe_cancel_old_sub is run
                $upgrade = $txn->is_upgrade();
                $downgrade = $txn->is_downgrade();
        
                $txn->maybe_cancel_old_sub();
                $result = $txn->store();
                $prd = $txn->product();
                $data['data']['membership']['transaction_id'] = $result;
             }            
            //$data['data']['membership'] = hf_create_memberpress_membership($user_data);
        }

    } catch(\Stripe\Exception\CardException $e) {  
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\RateLimitException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\AuthenticationException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        $data['error']=  $e->getMessage();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $data['error']=  $e->getMessage();
    } catch (Exception $e) {
        $data['error']=  $e->getMessage();
    }    

    return wp_send_json($data);
}

function hf_create_memberpress_membership($user_data){
    $url = 'http://dev.indiit.solutions/hiitfit/wp-json/mp/v1/members';
    $ch = curl_init($url);
    $data_string = json_encode(
      [
        'email'               => $user_data['email'],
        'username'            => $user_data['user_login'],
        'first_name'          => $user_data['first_name'],
        'last_name'           => $user_data['last_name'],
        'send_welcome_email'  => true, // Trigger a welcome email - this only works if adding a transaction (below)
        'transaction'         => [
          'membership'  => $membership_id, // ID of the Membership
          'amount'      => $user_data['amount'],
          'total'       => $user_data['amount'],
          'tax_amount'  => '0.00',
          'tax_rate'    => '0.000',
          'trans_num'   => 'mp-txn-' . uniqid(),
          'status'      => 'complete',
          'gateway'     => 'free',
          'created_at'  => gmdate( 'c' ),
        ]
      ]
    );
    
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_string );
    
    $header = array();
    $header[] = 'MEMBERPRESS-API-KEY: XHF7Zl2tgi'; // Your API KEY from MemberPress Developer Tools Here
    $header[] = 'Content-Type: application/json';
    $header[] = 'Content-Length: ' . strlen($data_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    
    $response = curl_exec($ch);
    
    if(curl_errno($ch)){
      throw new Exception(curl_error($ch));
    }

    return json_decode($response);
    
}

function hf_check_trial_mode_callback($request){
    $user_id = $request['user_id'];
    if(!$user_id){
        $data['error'] = "User ID is required";
        return wp_send_json($data);        
    }
    $reg_days_ago = 14;
    $cu = get_userdata($user_id);
    return ( isset( $cu->data->user_registered ) && strtotime( $cu->data->user_registered ) > strtotime( sprintf( '-%d days', $reg_days_ago ) ) ) ? TRUE : FALSE;
}

function hf_check_active_plan_callback($request){
    
    $user_id = $request['user_id'];
    $lang = $request['lang'];
    if(!$user_id){
        $data['error'] = "User ID is required";
        return wp_send_json($data);        
    }
    $payment_type = get_user_meta($user_id,'app_payment_type',true);
    
     
    if($payment_type == 'android'){
    
       $data =  hf_is_android_expire_callback($request);
       if(empty($data)) goto webmem;
       return wp_send_json($data);   
    }
    if($payment_type == 'ios'){
       $data =  hf_is_apple_expire_callback($request);
       if(empty($data)) goto webmem;
       return wp_send_json($data);   
    }    
    webmem:
    $user = new MeprUser( $user_id );
    wp_set_current_user($user_id);
    $subsdata = MeprUtils::get_currentuserinfo();
    $subscriptions = $subsdata->active_product_subscriptions();
    $subsdata = array();
    if($subscriptions){
    $subscriptions = array_unique($subscriptions);
        foreach($subscriptions as $subscription){
                $post = get_post($subscription); //assuming $id has been initialized
                setup_postdata($post);
                $post_data = array();
                $post_data = $post;
                if($lang){
                    $translations = pll_get_post_translations($subscription);
                    $id = $translations[$lang];
                    if( $id ){
                        $post->post_title = get_the_title($id);
                    }
                }
                
                $post->meta = get_post_meta($post->ID);
                $subsdata[] = $post;
                wp_reset_postdata();
                break;
        }
    }
    
    
    if($user->ID){
        $recent_trans =  $user->recent_transactions();
        $data = array();  
        $data['is_active'] = $user->is_active();
        if($recent_trans){
            $data['status'] = 1;
            $data['previous_member'] = TRUE;
        }else{
            $data['status'] = 77;
            $data['previous_member'] = FALSE;
        }
        $expiration = $user->active_product_subscriptions('transactions', true);
        foreach($expiration as $rec){
            $expire_date = MeprAppHelper::format_date($rec->expires_at, __('Never','memberpress'));
            if(isset($request['tz']) && $request['tz']){
            date_default_timezone_set($request['tz']);
            }
            $data['expire'] = date("d-m-Y, g:i a", strtotime($expire_date));
        }
    }else{
        $data['error'] = "User Not exist";
    }
    $data['membership'] = $subsdata;
     $data['appType'] = $payment_type;
    return wp_send_json($data);
}

function hf_term_and_conditions_callback(){
    WPBMap::addAllMappedShortcodes();
    global $post;
    $post = get_post (1019);
    $output['title'] = $post->post_title;
    $output['content'] = apply_filters( 'the_content', $post->post_content );
    return wp_send_json($output);
}

function hitfit_register_settings() {;
   register_setting( 'myplugin_options_group', 'hf_live_check', 'myplugin_callback' );
    register_setting( 'myplugin_options_group', 'hf_live_tab', 'myplugin_callback' );
}
add_action( 'admin_init', 'hitfit_register_settings' );

function hitfit_register_options_page() {
  add_options_page('Hiitfit Setting', 'Hiitfit Setting', 'manage_options', 'hiitfit_setting', 'hitfit_options_page');
}
add_action('admin_menu', 'hitfit_register_options_page');

function hitfit_options_page()
{
    ?>
      <div>
      <?php screen_icon(); ?>
      <h2>Settings</h2>
      <form method="post" action="options.php">
      <?php settings_fields( 'myplugin_options_group' ); ?>
      <table>
      <tr valign="top">
      <th scope="row"><label for="hf_live_check">Live Check</label></th>
      <td><input type="text" id="hf_live_check" name="hf_live_check" value="<?php echo get_option('hf_live_check'); ?>" /></td>
      </tr>
       <tr valign="top">
      <th scope="row"><label for="hf_live_tab">Live Check</label></th>
      <td><input type="checkbox" id="hf_live_tab" name="hf_live_tab" value="1" <?php echo (get_option('hf_live_tab')) ? 'checked' : ''; ?> /></td>
      </tr>
      </table>
      <?php  submit_button(); ?>
      </form>
      </div>
    <?php
} 

?>