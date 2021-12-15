<?php
/*
Plugin Name: Live training opration
Plugin URI: https://www.davidangulo.xyz/wp/portfolio/
Description: A simple plugin that allows you to perform Create (INSERT), Read (SELECT), Update and Delete operations.
Version: 1.0.0
Author: David Angulo
Author URI: https://www.davidangulo.xyz/wp/
License: GPL2
*/
register_activation_hook( __FILE__, 'crudOperationsTable');
function crudOperationsTable() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = 'wp_live_training';
  
    $result = $wpdb->get_results("SELECT * FROM $table_name");
  
  $sql = "CREATE TABLE `$table_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(220) DEFAULT NULL,
  `level` varchar(220) DEFAULT NULL,
  `coach_name` varchar(220) DEFAULT NULL,
  `zoom_link` varchar(220) DEFAULT NULL,
  `image` varchar(220) DEFAULT NULL,
  `days_of_training` varchar(220) DEFAULT NULL,
  `total_days_of_training` varchar(220) DEFAULT NULL,
  `time_from` varchar(220) DEFAULT NULL,
  `duration_of_days` varchar(220) DEFAULT NULL,
  `price` varchar(220) DEFAULT NULL,
  PRIMARY KEY(id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}
add_action('admin_menu', 'addAdminPageContent');
function addAdminPageContent() {
  $livescript = add_menu_page('Live Training', 'Live Training', 'manage_options' ,__FILE__, 'liveTrainingPage');
}

function my_enqueue_live($hook) {
    wp_enqueue_script('my_custom_script', plugin_dir_url(__FILE__) . '/myscript.js');
}

add_action('admin_enqueue_scripts', 'my_enqueue_live');

function live_get_status_data() {
    $status = $_POST['status'];
    $id = $_POST['live_id'];
    global $wpdb;
    $dbData['status'] = $status;
    $wpdb->update('wp_live_training', $dbData, array('id' => $id));
  exit();
}

add_action( 'wp_ajax_nopriv_live_get_status_data', 'live_get_status_data' );
add_action( 'wp_ajax_live_get_status_data', 'live_get_status_data' );

function liveTrainingPage() {
   
  global $wpdb;
  $table_name = 'wp_live_training';
  if (isset($_POST['newsubmit'])) {
    
    $imageName = '';
    if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    if( $_FILES['file']['error'] === UPLOAD_ERR_OK )
    {
        $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
        $userImg = wp_handle_upload( $_FILES['file'], $upload_overrides );
        $imageName = $userImg['url'];
    }
    
    $title = $_POST['title'];
    $level = $_POST['level'];
    $coach_name = $_POST['coach_name'];
    $zoom_link = $_POST['zoom_link'];
    $days_of_training = $_POST['days_of_training'];
    $total_days_of_training = $_POST['total_days_of_training'];
    $duration_of_days = $_POST['duration_of_days'];
    $price = $_POST['price'];
    $time_from = $_POST['time_from'];
    $lang = $_POST['lang'];
    $status = ($_POST['status'] == 1) ? '1' : '0';
    $wpdb->insert($table_name, 
                array(
                        'title' => $title, 
                        'lang'  => $lang,
                        'status'  => $status,
                        'level' => $level, 
                        'coach_name' => $coach_name, 
                        'zoom_link' => $zoom_link, 
                        'days_of_training' => $days_of_training,
                        'total_days_of_training'=>$total_days_of_training,
                        'duration_of_days'=>$duration_of_days,
                        'price'=>$price,
                        'time_from'=>$time_from,
                        'image'=>$imageName)); 
    echo "<script>location.replace('admin.php?page=adminpagearranger/live_training.php');</script>";
  }
  
    if(isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $title = $_POST['title'];
    $level = $_POST['level'];
    $coach_name = $_POST['coach_name'];
    $zoom_link = $_POST['zoom_link'];
    $days_of_training = $_POST['days_of_training'];
    $total_days_of_training = $_POST['total_days_of_training'];
    $duration_of_days = $_POST['duration_of_days'];
    $price = $_POST['price'];
    $time_from = $_POST['time_from'];
    $status = ($_POST['status'] == 1) ? '1' : '0';
    if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    if( $_FILES['file']['error'] === UPLOAD_ERR_OK )
    {
        $upload_overrides = array( 'test_form' => false ); #if you don’t pass 'test_form' => FALSE the upload will be rejected
        $userImg = wp_handle_upload( $_FILES['file'], $upload_overrides );
        $dbData['image'] = $userImg['url'];
    }
    $dbData['title'] = $title;
    $dbData['level'] = $level;
    $dbData['coach_name'] = $coach_name;
    $dbData['zoom_link'] = $zoom_link;
    $dbData['days_of_training'] = $days_of_training;
    $dbData['total_days_of_training'] = $total_days_of_training;
    $dbData['duration_of_days'] = $duration_of_days;
    $dbData['price'] = $price;
    $dbData['time_from'] = $time_from;
    $dbData['status'] = $status;
    $wpdb->update('wp_live_training', $dbData, array('id' => $id));
    echo "<script>location.replace('admin.php?page=adminpagearranger/live_training.php');</script>";
  }
  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $wpdb->query("DELETE FROM $table_name WHERE id='$del_id'");
    echo "<script>location.replace('admin.php?page=adminpagearranger/live_training.php');</script>";
  }
  ?>
  <div class="wrap">
    <h2>Live Training List </h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="5%">Title</th>
          <th width="5%">Level</th>
          <th width="5%">Coach Name</th>
          <th width="5%">Zoom link</th>
          <th width="5%">Image</th>
          <th width="5%">Days of training</th>
          <th width="5%">Total days of training</th>
          <th width="5%">Duration of days</th>
          <th width="5%">Time from</th>
          <th width="5%">Price</th>
          <th width="5%">Language</th>
          <th width="5%">Active</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post" enctype='multipart/form-data'>
          <tr>
              <td><input type="text" id="title" name="title"></td>
              <td><input type="text" id="level" name="level"></td>
              <td><input type="text" id="coach_name" name="coach_name"></td>
              <td><input type="text" id="zoom_link" name="zoom_link"></td>
              <td><input type="file" id="file" name="file"></td>
              <td><input type="text" id="days_of_training" name="days_of_training"></td>
              <td><input type="text" id="total_days_of_training" name="total_days_of_training"></td>
              <td><input type="text" id="duration_of_days" name="duration_of_days"></td>
              <td><input type="text" id="time_from" name="time_from"></td>
              <td><input type="text" id="price" name="price"></td>
              <td><select name="lang"><option value="en">English</option><option value="ar">Arabic</option></select></td>
              <td><input type="checkbox" id="status" name="status" value="1" checked></td>
              <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
          $result = $wpdb->get_results("SELECT * FROM $table_name");
          
          foreach ($result as $print) {
            $status = ($print->status == 1) ? 'checked' : '';
            echo "<tr>
                    <td width='5%'>$print->title</td>
                    
                    <td width='5%'>$print->level</td>
                    
                    <td width='5%'>$print->coach_name</td>
                    
                    <td width='5%'>$print->zoom_link</td>
                    
                    <td width='5%'><img src='$print->image' style='width: 100%;'></td>
                    
                    <td width='5%'>$print->days_of_training</td>
                    
                    <td width='5%'>$print->total_days_of_training</td>
                    
                    <td width='5%'>$print->duration_of_days</td>
                    
                    <td width='5%'>$print->time_from</td>
                    
                    <td width='5%'>$print->price</td>
                    
                    <td width='5%'>$print->lang</td>
                     <td width='5%'><input type='checkbox' data-id='$print->id' class='update-status-live' id='status' name='status' value='1' $status></td>
                    <td width='5%'>
                    
                    <a href='admin.php?page=adminpagearranger/live_training.php&upt=$print->id'><button type='button'>UPDATE</button></a>
                    
                    <a href='admin.php?page=adminpagearranger/live_training.php&del=$print->id'><button type='button'>DELETE</button></a>
                    
                    </td>
                </tr>";
          }
        ?>
      </tbody>  
    </table>
    <br>
    <br>
    <?php
      if (isset($_GET['upt'])) {
        $id = $_GET['upt'];
        $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
        foreach($result as $print) {
          $name = $print->name;
          $email = $print->email;
          $status = ($print->status == 1) ? 'checked' : '';
        }
        echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='5%'>Id</th>
              <th width='5%'>Title</th>
              <th width='5%'>Level</th>
              <th width='5%'>Coach Name</th>
              <th width='5%'>Zoom link</th>
              <th width='5%'>Image</th>
              <th width='5%'>Days of training</th>
              <th width='5%'>Total days of training</th>
              <th width='5%'>Duration of days</th>
              <th width='5%'>Price</th>
              <th width='5%'>Time_from</th>
              <th width='5%'>Lamguage</th>
              <th width='5%'>Active</th>
              <th width='5%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post' enctype='multipart/form-data'>
              <tr>
                <td width='5%'>$print->id <input type='hidden' id='uptid' name='uptid' value='$print->id'></td>
                <td width='5%'><input type='text' id='title' name='title' value='$print->title'></td>
                <td width='5%'><input type='text' id='level' name='level' value='$print->level'></td>
                <td width='5%'><input type='text' id='coach_name' name='coach_name' value='$print->coach_name'></td>
                <td width='5%'><input type='text' id='zoom_link' name='zoom_link' value='$print->zoom_link'></td>
                <td width='5%'><input type='file' id='file' name='file'><img src='$print->image' style='width:100%;'></td>
                <td width='5%'><input type='text' id='days_of_training' name='days_of_training' value='$print->days_of_training'></td>
                <td width='5%'><input type='text' id='total_days_of_training' name='total_days_of_training' value='$print->total_days_of_training'></td>
                <td width='5%'><input type='text' id='duration_of_days' name='duration_of_days' value='$print->duration_of_days'></td>
                <td width='5%'><input type='text' id='price' name='price' value='$print->price'></td>
                <td width='5%'><input type='text' id='time_from' name='time_from' value='$print->time_from'></td>
                <td width='5%'><select name='lang'><option value='en' >English</option><option value='ar' >Arabic</option></select></td>
                <td width='5%'><input type='checkbox' id='status' name='status' value='1' $status></td>
                <td width='5%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=adminpagearranger/live_training.php'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
      }
    ?>
  </div>
  <?php
}