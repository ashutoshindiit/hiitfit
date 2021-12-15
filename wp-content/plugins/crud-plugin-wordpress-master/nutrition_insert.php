<?php
/**
 * Created by PhpStorm.
 * User: lcom53-two
 * Date: 2/12/2018
 * Time: 2:25 PM
 */
function nutrition_insert()
{
    //echo "insert page";
    ?>
<table>
    <thead>
    <tr>
        <th> <h2> Add New Nutrition</h2></th>
        
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
            <input type="text" name="title">
        </td>
    </tr>
    <tr>
        <td>Time Duration:</td>
        <td>
            <input type="text" name="time_duration">
        </td>
    </tr>
    <tr>
        <td>File</td>
        <td><input type="file" name="file"></td>
    </tr>
    <tr>
        <td>Thubnail img</td>
        <td><input type="file" name="thumbnail_img"></td>
    </tr>
    <tr>
        <td><input type="submit" value="Insert" name="ins"></td>
    </tr>
    </form>
    </tbody>
</table>
<?php
    if(isset($_POST['ins'])){
        global $wpdb;
        
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
        $wpdb->insert(  $table_name,$data );
        echo "inserted";
        wp_redirect( admin_url('admin.php?page=Nutrition_Listing'),301 );
        ?>
       <!-- <meta http-equiv="refresh" content="1; url=http://localhost/wordpressmyplugin/wordpress/wp-admin/admin.php?page=Nutrition_Listing" />-->
        <?php
        exit;
    }
}

?>