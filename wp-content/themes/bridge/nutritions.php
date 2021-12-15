<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
global $wpdb;
$table_name = $wpdb->prefix . 'nutrition'; // do not forget about tables prefix
$message = '';
$notice = '';
$default = array(
	'type' => '',
	'title' => '',
	'time_duration' => '',
	'path' => '',
	'thumbnail_img' => '',
	'status' => '',
	'lang' => '',
);

if($_REQUEST['page'] == "nutritions" && $_REQUEST['action'] == "edit" && !empty($_REQUEST['id'])){
	if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
		$default = array(
		    'id' => '',
			'type' => '',
			'title' => '',
			'time_duration' => '',
			'path' => '',
			'thumbnail_img' => '',
			'status' => '',
			'lang' => '',
		);
		$item = shortcode_atts($default, $_REQUEST);
		$item_valid = robwines_validate_nutrition_data($item);
        if ($item_valid === true) {
			$result = $wpdb->update($table_name, $item, array('id' => $item['id']));
			$message = __('<p>Nutrition was successfully updated</p>', 'robwines');		
			$message .= __('<p><a href="'.get_site_url().'/wp-admin/admin.php?page=rob-nutrition">&larr; Back to nutrition</a></p>', 'robwines');		
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }		
	} else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'robwines');
            }
        }
    }
	?>
	<div id="col-container" class="wp-clearfix">
		<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h1 class="wp-heading-inline"><?php _e('Edit Nutrition', 'robwines')?></h1>
			<?php if (!empty($notice)): ?>
			<div id="notice" class="error"><p><?php echo $notice ?></p></div>
			<?php endif;?>
			<?php if (!empty($message)): ?>
			<div id="message" class="updated"><p><?php echo $message ?></p></div>
			<?php endif;?>
		</div>
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<form method="post" action="" class="validate">
						<div class="form-field form-required nutrition-name-wrap">
							<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
							<input type="hidden" name="id" value="<?php echo $_REQUEST['id'] ?>"/>
							<label for="nutrition-name">Name</label>
							<input name="title" id="tag-name" type="text" value="<?php echo $item['title'] ?>" size="40" aria-required="true" required>
							<p>The name is how it appears on your site.</p>
						</div>
						<div class="form-field">
							<label for="nutrition-state">Type</label>
							<select name="type" class="robwine-select-init" style="width:95%;">
								<option value="diet_tips" <?php echo ($item['type'] == "diet_tips" )? "selected" : ""; ?>>Diet Tips</option>
								<option value="type_of_diet" <?php echo ($item['type'] == "type_of_diet" )? "selected" : ""; ?>>Type of Diet</option>							
							</select>
						</div>
						<div class="form-field form-required">
							<label for="nutrition-description">Duration</label>
							<input name="time_duration" id="tag-duration" type="text" value="<?php echo $item['time_duration'] ?>" size="40" aria-required="true" required>							
						</div>		
						<div class="form-field region-thumbnail-wrap">
							<label>Thumbnail</label>
							<?php 
							if(!empty($item['thumbnail_img'])){
								$robwines_thumbnail = $item['thumbnail_img'];			
							}else{
								$robwines_thumbnail = get_template_directory_uri().'/img/image-placeholder.jpg';	
							}							
							?>
							<div id="robwines_thumbnail" style="float: left; margin-right: 10px;"><img data-placeholder="<?php echo get_template_directory_uri().'/img/image-placeholder.jpg'; ?>" src="<?php echo $robwines_thumbnail; ?>" width="60px" height="60px"></div>
							<div style="line-height: 60px;">
								<input type="hidden" id="robwines_thumbnail_id" name="thumbnail_img" value="<?php echo $item['thumbnail_img']; ?>">
								<button type="button" datatype="img_upload" class="upload_image_button button">Upload/Add image</button>
								<button type="button" class="remove_image_button button" <?php if(empty($item['image_id'])) { ?> style="display: none;" <?php } ?>>Remove image</button>
							</div>
							<div class="clear"></div>			
						</div>
						
						<div class="form-field region-thumbnail-wrap">
							<label>Path</label>
							<?php 
							if(!empty($item['path'])){
								$path_name = basename($item['path']);
								$robwines_path = get_site_url().'/wp-includes/images/media/document.png';

							}else{
								$robwines_path  = get_template_directory_uri().'/img/image-placeholder.jpg';	
							}							
							?>
							<div id="nutrition_path_thumb" style="float: left; margin-right: 10px;"><img data-placeholder="<?php echo get_template_directory_uri().'/img/image-placeholder.jpg' ?>" src="<?php echo $robwines_path; ?>" width="60px" height="60px"></br><span class="nutri_file_name"><?php echo $path_name; ?></span></div>
							<div style="line-height: 60px;">
								<input type="hidden" id="hitfit_path_file" name="path" value="<?php echo $item['path']; ?>">
								<button type="button" datatype="path_upload" class="upload_path_button button">Upload/Add File</button>
								<button type="button" class="remove_path_button button" style="display: none;">Remove File</button>
							</div>
							<div class="clear"></div>			
						</div>


						<div class="form-field nutrition-active-wrap">
							<input type="hidden" name="status" value="0">
							Active <input type="checkbox" name="status" value="1" <?php echo ($item['status'] == true) ? "checked":""; ?>>		
						</div>
						<div class="form-field">
							<label for="language-state">Language</label>
							<select name="lang" class="robwine-select-init" style="width:95%;">
								<option value="en" <?php echo ($item['lang'] == "en" )? "selected" : ""; ?>>English</option>
								<option value="ar" <?php echo ($item['lang'] == "ar" )? "selected" : ""; ?>>Arabic</option>							
							</select>
						</div>
						<p class="submit">
							<input type="submit" name="nutrition-submit" id="submit" class="button button-primary" value="Update Nutrition">
						</p>
					</form>
				</div>
			</div>			
		</div>			
	</div>	
	<?php 
}else{
	
if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
	$item = shortcode_atts($default, $_REQUEST);
    $item_valid = robwines_validate_nutrition_data($item);
	if ($item_valid === true) {
		$result = $wpdb->insert($table_name, $item);
		$item['id'] = $wpdb->insert_id;
		if ($result) {
			$message = __('Nutrition successfully saved', 'robwines');
		} else {
			$notice = __('There was an error while saving item', 'robwines');
		}
	} else {
		// if $item_valid not true it contains error message(s)
		$notice = $item_valid;
	}	
}

class Robwines_Table_List_Table extends WP_List_Table
{

    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'nutrition',
            'plural' => 'nutritions',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_title($item)
    {
		
        $actions = array(
            'edit' => sprintf('<a href="?page=%s&action=edit&id=%s">%s</a>', $_REQUEST['page'],$item['id'], __('Edit', 'robwines')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'robwines')),
        );

        return sprintf('%s %s',
            $item['title'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
	
    function column_thumbnail_img($item)
    {
		if(!empty($item['thumbnail_img'])){
			return sprintf('<img src="'.$item['thumbnail_img'].'" width="48" height="48">');			
		}else{
			return sprintf('<img src="'.get_template_directory_uri().'/img/image-placeholder.jpg" width="48" height="48">');	
		}
    }

    function column_created_at($item)
    {
        return date("d/m/Y H:i:s", strtotime($item['created_at']));
    }

	function column_status($item)
    {
        return ($item['status'] == 1) ? 'Active' : 'Inactive';
    }
	
	function column_path($item)
	{
		$file = $item['path'];
		if($file){
			return '<a href="'.$file.'" target="_blank">Download</a>';			
		}else{
			return "-";
		}
	}

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
			'title' => __('Title', 'robwines'),
            'time_duration' => __('Duration', 'robwines'),
            'path' => __('Path', 'robwines'),
            'thumbnail_img' => __('Image', 'robwines'),
            'type' => __('Type', 'robwines'),
            'status' => __('Status', 'robwines'),
            'lang' => __('Language', 'robwines'),
            'created_at' => __('Date', 'robwines'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
        );
        return $sortable_columns;
    }

  
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }


    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nutrition'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

	protected function get_views() { 
	  $views = array();
	   $current = ( !empty($_REQUEST['statusvar']) ? $_REQUEST['statusvar'] : 'all');
 
	   //All link
	//    $class = ($current == 'all' ? ' class="current"' :'');
	//    $arr_params = array( 'statusvar', 'action', 'id');
	//    $all_url = remove_query_arg($arr_params);
	//    $views['all'] = "<a href='{$all_url }' {$class} >All</a>";
 
	   //Active link
	//    $active_url = remove_query_arg(array('action','id'),add_query_arg('statusvar','active'));
	//    $class = ($current == 'active' ? ' class="current"' :'');
	//    $views['active'] = "<a href='{$active_url}' {$class} >Active</a>";
 
	   //Inactive
	//    $inavtive_url = remove_query_arg(array('action','id'),add_query_arg('statusvar','inactive'));
	//    $class = ($current == 'inactive' ? ' class="current"' :'');
	//    $views['inavtive'] = "<a href='{$inavtive_url}' {$class} >Inactive</a>";
 
	   return $views;
	}

	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}
	 
		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}
	 
		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}
	 
		$output = '<span class="displaying-num">' . sprintf(
			/* translators: %s: Number of items. */
			_n( '%s item', '%s items', $total_items ),
			number_format_i18n( $total_items )
		) . '</span>';
	 
		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();
	 
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	 
		$current_url = remove_query_arg( $removable_query_args, $current_url );
	 
		$page_links = array();
	 
		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';
	 
		$disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;
	 
		if ( 1 == $current ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( 2 == $current ) {
			$disable_first = true;
		}
		if ( $total_pages == $current ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $total_pages - 1 == $current ) {
			$disable_last = true;
		}
	 
		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}
	 
		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}
	 
		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s pages</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = $total_pages_before . sprintf(
			/* translators: 1: Current page, 2: Total pages. */
			_x( '%1$s of %2$s', 'paging' ),
			$html_current_page,
			$html_total_pages
		) . $total_pages_after;
	 
		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}
	 
		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}
	 
		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';
	 
		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";
	 
		echo $this->_pagination;
	}
	
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nutrition'; // do not forget about tables prefix

        $per_page = 50; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);
		$statusvar = ( isset($_REQUEST['statusvar']) ? $_REQUEST['statusvar'] : '');
		if($statusvar != '') {
			$search_custom_vars= "AND active LIKE '%" . esc_sql( ($wpdb->esc_like( $statusvar ) == 'active') ? 1:0 ) . "%'";
		} else	{
			$search_custom_vars = '';
		}       
   	    $search = '';		
		if ( ! empty( $_REQUEST['s'] ) ) {
            $search = "AND title LIKE '%" . esc_sql( $wpdb->esc_like( $_REQUEST['s'] ) ) . "%'";
        }	
		
        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE 1=1 {$search} {$search_custom_vars}");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';
		

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
		$this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE 1=1 {$search} {$search_custom_vars} ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}


    $table = new Robwines_Table_List_Table();
    $table->prepare_items();
    if ('delete' === $table->current_action()) {
		$count = (is_array($_REQUEST['id'])) ? count($_REQUEST['id']) : 1;
		$message =  sprintf(__('Items Deleted: %d', 'robwines'), $count);
	}
    ?>
	<div id="col-container" class="wp-clearfix">
		<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h1 class="wp-heading-inline"><?php _e('Nutrition', 'robwines')?></h1>
			<?php if (!empty($notice)): ?>
			<div id="notice" class="error"><p><?php echo $notice ?></p></div>
			<?php endif;?>
			<?php if (!empty($message)): ?>
			<div id="message" class="updated"><p><?php echo $message ?></p></div>
			<?php endif;?>
			<?php if (!empty($message_deleted)): ?>
			<div id="message" class="updated"><p><?php echo $message_deleted ?></p></div>
			<?php endif;?>
		</div>
		<div id="col-left">	
			<div class="col-wrap">
				<div class="form-wrap">
					<h2>Add New Nutrition</h2>
					<form method="post" action="<?php echo remove_query_arg(array('action','id')); ?>" class="validate">
						<div class="form-field form-required nutrition-name-wrap">
						    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
							<label for="nutrition-name">Title</label>
							<input name="title" id="tag-name" type="text" value="" size="40" aria-required="true" required>
							<p>The name is how it appears on your site.</p>
						</div>
						<div class="form-field">
							<label for="nutrition-state">Type</label>
							<select name="type" class="robwine-select-init" style="width:95%;">
								<option value="diet_tips">Diet Tips</option>
								<option value="type_of_diet">Type of Diet</option>							
							</select>
						</div>
						<div class="form-field form-required">
							<label for="nutrition-duration">Duration</label>
							<input name="time_duration" id="tag-duration" type="text" value="" size="40" aria-required="true" required>
						</div>
						<div class="form-field region-thumbnail-wrap">
							<label>Thumbnail</label>
							<div id="robwines_thumbnail" style="float: left; margin-right: 10px;"><img data-placeholder="<?php echo get_template_directory_uri().'/img/image-placeholder.jpg' ?>" src="<?php echo get_template_directory_uri().'/img/image-placeholder.jpg' ?>" width="60px" height="60px"></div>
							<div style="line-height: 60px;">
								<input type="hidden" id="robwines_thumbnail_id" name="thumbnail_img" value="">
								<button type="button" datatype="img_upload" class="upload_image_button button">Upload/Add image</button>
								<button type="button" class="remove_image_button button" style="display: none;">Remove image</button>
							</div>
							<div class="clear"></div>			
						</div>
						
						<div class="form-field region-thumbnail-wrap">
							<label>Path</label>
							<div id="nutrition_path_thumb" style="float: left; margin-right: 10px;"><img data-placeholder="<?php echo get_template_directory_uri().'/img/image-placeholder.jpg' ?>" src="<?php echo get_template_directory_uri().'/img/image-placeholder.jpg' ?>" width="60px" height="60px"></br><span class="nutri_file_name"></span></div>
							<div style="line-height: 60px;">
								<input type="hidden" id="hitfit_path_file" name="path" value="">
								<button type="button" datatype="path_upload" class="upload_path_button button">Upload/Add File</button>
								<button type="button" class="remove_path_button button" style="display: none;">Remove File</button>
							</div>
							<div class="clear"></div>			
						</div>

						<div class="form-field nutrition-active-wrap">
							Active <input type="checkbox" name="status" value="1" checked>		
						</div>
						
						<div class="form-field">
							<label for="lang-state">Language</label>
							<select name="lang" class="robwine-select-init" style="width:95%;">
								<option value="en">English</option>
								<option value="ar">Arabic</option>							
							</select>
						</div>
						
						<p class="submit">
							<input type="submit" name="nutrition-submit" id="submit" class="button button-primary" value="Add New Nutrition">
						</p>
					</form>
				</div>
			</div>		
		</div>
		
		<div id="col-right">
			<div class="wrap">
				<?php $table->views(); ?>
				<form method='post'>
					<input type='hidden' name='page' value='rob-nutrition' />
					<?php $table->search_box('search', 'search_id'); ?>
				</form>
				<form id="persons-table" method="GET">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
					<?php $table->display(); ?>
				</form>

			</div>
		</div>
	</div>
	<?php
}
function robwines_validate_nutrition_data($item){
    $messages = array();
    if (empty($item['title'])) $messages[] = __('Title is required', 'robwines');
    if (empty($messages)) return true;
    return implode('<br />', $messages);		
}
?>