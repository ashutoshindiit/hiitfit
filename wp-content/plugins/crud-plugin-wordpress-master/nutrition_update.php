<?php
//echo "update page";
function nutrition_update(){
    //echo "update page in";
    $i=$_GET['id'];
    global $wpdb;
    $table_name = $wpdb->prefix . 'nutrition';
    $nutrition = $wpdb->get_results("SELECT * from $table_name where id=$i");
    $nutrition[0]->id;
    ?>
    <table>
        <thead>
        <tr>
            <th><h2>Update Nutrition Data</h2></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
                <form name="frm" action="#" method="post" enctype="multipart/form-data">
                <tr>
                    <td>Type:</td>
                    <td>
                        <select name="type">
                            <option value="diet_tips">Diet tips</option>
                            <option value="type_of_diet">Type of diet</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Title
                    </td>
                    <td>
                        <input type="text" name="title"  value="<?php echo $nutrition[0]->title; ?>">
                    </td>
                </tr>
                <tr>
                    <td>Time Duration:</td>
                    <td>
                        <input type="text" name="time_duration" value="<?php echo $nutrition[0]->time_duration; ?>">
                    </td>
                </tr>
                <tr>
                    <td>File</td>
                    <td>
                        <a href="javascript:void(0)" download="<?php echo $nutrition[0]->path; ?>">Click to download</a>
                        <br>
                        <input type="file" name="file">
                    </td>
                </tr>
                <tr>
                    <td>Thubnail img</td>
                    <td>
                        <img src="<?php echo  $nutrition[0]->thumbnail_img; ?>">
                        <br>
                        <input type="file" name="thumbnail_img">
                    </td>
                </tr>
                <tr>
                    <td><input type="submit" value="Update" name="upd"></td>
                </tr>
                </form>
        </tbody>
    </table>
    <?php

if(isset($_POST['upd'])){
        global $wpdb;
        $id=$_GET['id'];
        $table_name = $wpdb->prefix . 'nutrition';
        
        $data = [
        'title' => $_POST['title'],
        'type' => $_POST['type'],
        'time_duration' => $_POST['time_duration'],
        ];
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['file']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg = wp_handle_upload( $_FILES['file'], $upload_overrides );
            $data['path'] = $userImg['url'];
        }
        if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if( $_FILES['thumbnail_img']['error'] === UPLOAD_ERR_OK )
        {
            $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
            $userImg1 = wp_handle_upload( $_FILES['thumbnail_img'], $upload_overrides );
            $data['thumbnail_img'] = $userImg1['url'];
        }
        $wpdb->update(  $table_name,$data ,array('id'=>$id));
        wp_redirect( admin_url('admin.php?page=Nutrition_Listing'),301 );
        ?>
        <?php
        exit;
    }
    }
?>