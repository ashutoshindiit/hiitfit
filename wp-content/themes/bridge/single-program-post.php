<?php  extract(bridge_qode_get_blog_single_params()); ?>
<?php get_header(); ?>
<?php 
global $post;
$user = get_current_user_id();
$postID = "last_viewed_program";
$user_id_with_post = $user."_".$postID;
$vimeo_api_token = get_option('vimeo_api_token');

//echo $video_id_with_post;

if (get_post_type( $post->ID ) == 'program-post' )
    update_post_meta( $post->ID, $user_id_with_post, current_time('mysql') );

	//$finalmeta = get_post_meta( $post->ID );

	//print("<pre>".print_r($finalmeta,true)."</pre>");
$terms = get_the_terms( $post->ID , 'program_category' );
foreach ( $terms as $term ) {
$cat = $term->name;
}
?>
<?php if($cat == "Live"){ ?>
	<div class="wrapper">
	<div class="wrapper_inner">
		<div class="content main-section-single-list">
				<div class="container">	
				    	
					<?php 
					$video_type = get_field('video_type');
					$video_id = get_field('video_id');

					$imgid = $video_id;
					$imgid1 = 446827718;
					$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$imgid1.".php"));
					$vimeo_url_img = $hash[0]['thumbnail_large']; 

					function extractVideoID($url){
						$regExp = "/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/";
						preg_match($regExp, $url, $video);
						return $video[7];
					}
					
					function getYouTubeThumbnailImage($video_id) {
						return "https://i3.ytimg.com/vi/$video_id/hqdefault.jpg"; //pass 0,1,2,3 for different sizes like 0.jpg, 1.jpg
					}
					
					if($video_type == 'youtube' && $video_id != '')
					{
					?>
					<div class="video-banner">
					<iframe src="https://www.youtube.com/embed/<?php echo $video_id;?>" width="1200" height="660" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
					</div>
					<?php 
					}
					elseif ($video_type == 'vimeo' && $video_id != '') {
					?>
					<div class="video-banner">
					<iframe src="https://player.vimeo.com/video/<?php echo $video_id;?>" width="1200" height="660" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
					</div>
					<?php
					}
					else
					{
					?>
					<?php the_post_thumbnail(); ?>
					<?php
					}

					?>	
					
					</div>
					<div class="post-content-row">
						<div class="container_inner">	
					
				    <div class="post-content-custom-data row">
				    		
				    	<div class=" post-cont-part">
				    		<div class="post-content-title">	
					<h2 class="post-cont-head"><?php echo get_the_title();?></h2>
				    </div>
				    	<div class="custom-field-data">
				    		<div class="custom-field-row">

				    	<?php 
				    	$day__meditations_number = get_field('day__meditations_number');
				    	$day__meditations_title = get_field('day__meditations_title');
				    	$mins__day = get_field('mins__day');
				    	$mins__day_title = get_field('mins__day_title');
				    	$type = get_field('type');
				    	$equipment_needed = get_field('equipment_needed');
				    	$add_schedule_pdf_file = get_field('add_schedule_pdf_file'); 
				    	$url_pdf = $add_schedule_pdf_file['url'];
				    	
				    	?>
				    	
				    		<?php 
				    		if($day__meditations_number != '')
				    		{
				    			echo "<div class='cust-field-col'><div class='duration cust-field-title'>Duration</div>".$day__meditations_number.' '.$day__meditations_title."</div>";
				    		}
				    		?>
				    		<?php 
				    		if($url_pdf != '')
				    		{
				    			echo "<div class='cust-field-col'><div class='schedule-cls cust-field-title'>Schedule</div><a href='".$url_pdf."' target='_blank' class='download-btn'>Download</a></div>";
				    		}
				    		else
				    		{
				    		//echo "<div class='cust-field-col'><div class='schedule-cls cust-field-title'>Schedule</div><a href='#' target='_blank' class='download-btn'>Download</a></div>";	
				    		}
				    		?>
				    		<?php 
				    		if($equipment_needed != '')
				    		{
				    			echo "<div class='cust-field-col'><div class='schedule-cls cust-field-title'>Equipment Needed</div>".$equipment_needed."</div>";
				    		}
				    		?>
				       		</div>
				    	</div>
					    	<div class="description-section-cls"><?php echo get_the_content(); ?></div>
							<div> <button><a target="_blank" href="<?php echo get_field('live_program_link') ?>">Go Live</a></button></div>
					    </div>
				    </div>
				</div>
				</div>
					
			    </div>
		  
		</div>
	</div>
</div>
 <?php }else{?>
<div class="wrapper">
	<div class="wrapper_inner">
		<div class="content main-section-single-list">
				<div class="container">	
				    	
					<?php 
					$video_type = get_field('video_type');
					$video_id = get_field('video_id');

					$imgid = $video_id;
					$imgid1 = 446827718;
					$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$imgid1.".php"));
					$vimeo_url_img = $hash[0]['thumbnail_large']; 

					function extractVideoID($url){
						$regExp = "/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/";
						preg_match($regExp, $url, $video);
						return $video[7];
					}
					
					function getYouTubeThumbnailImage($video_id) {
						return "https://i3.ytimg.com/vi/$video_id/hqdefault.jpg"; //pass 0,1,2,3 for different sizes like 0.jpg, 1.jpg
					}
					
					if($video_type == 'youtube' && $video_id != '')
					{
					?>
					<div class="video-banner">
					<iframe src="https://www.youtube.com/embed/<?php echo $video_id;?>" width="1200" height="660" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
					</div>
					<?php 
					}
					elseif ($video_type == 'vimeo' && $video_id != '') {
					?>
					<div class="video-banner">
					<iframe src="https://player.vimeo.com/video/<?php echo $video_id;?>" width="1200" height="660" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
					</div>
					<?php
					}
					else
					{
					?>
					<?php the_post_thumbnail(); ?>
					<?php
					}

					?>	
					<?php if (have_posts()) : ?>
					<?php while (have_posts()) : the_post(); ?>
					</div>
					<div class="post-content-row">
						<div class="container_inner">	
					
				    <div class="post-content-custom-data row">
				    		
				    	<div class=" post-cont-part">
				    		<div class="post-content-title">	
					<h2 class="post-cont-head"><?php echo get_the_title();?></h2>
				    </div>
				    	<div class="custom-field-data">
				    		<div class="custom-field-row">

				    	<?php 
				    	$day__meditations_number = get_field('day__meditations_number');
				    	$day__meditations_title = get_field('day__meditations_title');
				    	$mins__day = get_field('mins__day');
				    	$mins__day_title = get_field('mins__day_title');
				    	$type = get_field('type');
				    	$equipment_needed = get_field('equipment_needed');
				    	$add_schedule_pdf_file = get_field('add_schedule_pdf_file'); 
				    	$url_pdf = $add_schedule_pdf_file['url'];
				    	
				    	?>
				    	
				    		<?php 
				    		if($day__meditations_number != '')
				    		{
				    			echo "<div class='cust-field-col'><div class='duration cust-field-title'>Duration</div>".$day__meditations_number.' '.$day__meditations_title."</div>";
				    		}
				    		?>
				    		<?php 
				    		if($url_pdf != '')
				    		{
				    			echo "<div class='cust-field-col'><div class='schedule-cls cust-field-title'>Schedule</div><a href='".$url_pdf."' target='_blank' class='download-btn'>Download</a></div>";
				    		}
				    		else
				    		{
				    		//echo "<div class='cust-field-col'><div class='schedule-cls cust-field-title'>Schedule</div><a href='#' target='_blank' class='download-btn'>Download</a></div>";	
				    		}
				    		?>
				    		<?php 
				    		if($equipment_needed != '')
				    		{
				    			echo "<div class='cust-field-col'><div class='schedule-cls cust-field-title'>Equipment Needed</div>".$equipment_needed."</div>";
				    		}
				    		?>
				       		</div>
				    	</div>
					    	<div><div class="description-section-cls"><?php echo get_the_content(); ?></div></div>
					    	<div class="sidebar" style="width: 100%; margin-top: 7%;">
					    	<div class="related-all-video-section">
								<?php if( have_rows('related_video_section') ): ?>
							    <div class="related-video-content-section">
							    	<div class="video-main-title-cls h4">Videos</div>
							    	<div class="col-md-12">
							    		<?php 
									    // Loop through rows.
									    while( have_rows('related_video_section') ) : the_row();

									        // Load sub field value.
								         	$day = get_sub_field('day');
									        $select_related_video_type = get_sub_field('select_related_video_type');
									        $related_video_id = get_sub_field('related_video_id');
									        $related_video_title = get_sub_field('related_video_title');
									        $related_video_mins__day = get_sub_field('related_video_mins__day');
									        $related_video_mins__day_title = get_sub_field('related_video_mins__day_title');
									        $related_video_workout_title = get_sub_field('related_video_workout_title');
											$related_video_thumbnail = get_sub_field('related_video_thumbnail');
									        // Do something...

									    // End loop.
									    ?>
									    
									    <?php if(!empty($day)){ ?>
									    	<div class="post-item full-w">
									        	<div class="video-main-title-cls h4"><?php echo $day; ?></div>
									    	</div>
									    <?php }else{ } ?>
									    <div class="post-item half-w">
									        <div class="post-img">
									        	
									        	
									        <?php
											// update_sub_field
									        /*youtube thumbnail image get path in youtube */
											$video_url = 'YOUTUBE_VIDEO_URL';
											$video_id1 = '';
											$thumbnail_youtube =  '';
											$vimeo_url_img1 = '';
											if($select_related_video_type == 'Youtube')
											{
												$video_id1 = extractVideoID("https://www.youtube.com/embed/".$related_video_id);
												if ($related_video_thumbnail) {
													$thumbnail_youtube = $related_video_thumbnail; 
												}
												else {
													$thumbnail_youtube =  getYouTubeThumbnailImage($video_id1);
													update_sub_field('related_video_thumbnail',$thumbnail_youtube);
												}
											}
											elseif ($select_related_video_type == 'vimeo') {
												/*vimeo thumbnail image get path in vimeo */
												$vimeo_url_img1 = '';
												try {
													if ($related_video_thumbnail) {
														$vimeo_url_img1 = $related_video_thumbnail; 
													}
													else {
														require_once ABSPATH . WPINC . '/http.php';
														$url = "https://vimeo.com/api/v2/video/".$related_video_id.".json";
														$response = wp_remote_get($url);
														$json = json_decode( $response['body'], true );
														if ( false !== $json && $json[0]) {
															$vimeo_url_img1 = $json[0]['thumbnail_large']; 
															update_sub_field('related_video_thumbnail',$vimeo_url_img1);
														}
														else {
															$url = "https://api.vimeo.com/videos/".$related_video_id."/pictures/?sizes=200x150";
															$args = array(
																'headers' => array(
																'Content-Type' => 'application/json',
																'Authorization' => 'Bearer ' . $vimeo_api_token
																)
															);
															
															$response = wp_remote_get(
																$url,
																$args
															);
														
															$json = json_decode( $response['body'], true );
															if ( false !== $json && $json['data']) {
																$vimeo_url_img1 = $json['data'][0]['sizes'][0]['link_with_play_button'];
																update_sub_field('related_video_thumbnail',$vimeo_url_img1);
															}
														}
														
													}
												} catch ( Exception $ex ) {
												}	
												/* end vimeo */
											}
											
											/* end */

											 
									       		if($select_related_video_type == 'Youtube')
												{
												?>
												<a class="fancybox-media program_video" onclick="count('<?php echo $related_video_id ?>','<?php echo $id ?>')" href="https://www.youtube.com/embed/<?php echo $related_video_id; ?>"><img src='<?php echo $thumbnail_youtube; ?>' class="" /></a>
												
												<!-- <iframe src="https://www.youtube.com/embed/<?php echo $related_video_id;?>" width="400" height="180" frameborder="0" allowfullscreen="allowfullscreen"></iframe> -->
												
												<?php 
												}
												elseif ($select_related_video_type == 'vimeo') {
												?>
												<a class="fancybox-media program_video" onclick="count('<?php echo $related_video_id ?>','<?php echo $id ?>')" href="https://player.vimeo.com/video/<?php echo $related_video_id; ?>"><img src='<?php echo $vimeo_url_img1; ?>' /></a>
												
													<!-- <iframe src="https://player.vimeo.com/video/<?php echo $related_video_id;?>" width="400" height="180" frameborder="0" allowfullscreen="allowfullscreen"></iframe> -->
												<?php
												}
												else
												{
												?>
												
												<?php
												}
												?>
									        </div>
									        <div class=" post-cont">
									        	<div class="related-sub-title"><h2><?php echo $related_video_title;?></h2></div>
									        	<div class="related-video-time">
									        		<?php echo $related_video_mins__day.' '. $related_video_mins__day_title;?> | <?php echo $related_video_workout_title; ?>
									        	</div>
									        	<div class="related-calender-section">
									        		<a href="#">Add to Calendar</a>
									        	</div>

									        </div>
									    </div>
									    <?php endwhile; ?>
									</div>
								</div>
								<?php endif; ?>
							</div>
						</div>
					    </div>
					    		<div class="sidebar mobile">
						    	<div class="related-all-video-section">
						    			<?php if( have_rows('related_video_section') ): ?>
									    <div class="related-video-content-section">
									    	<div class="video-main-title-cls h4">Recently Viewed Videos</div>
									    	<div class="col-md-12">
								    		<?php
								    		$embededVideo = array();
								    		if( have_rows('related_video_section') ):
								    		while( have_rows('related_video_section') ): the_row(); 
								    			$embededVideo[] = array(
								    				'id' => get_the_ID(),
								    				'select_related_video_type' => get_sub_field('select_related_video_type'),
								    				'related_video_id' => get_sub_field('related_video_id'),
								    				'related_video_title' => get_sub_field('related_video_title'),
								    				'related_video_mins__day' => get_sub_field('related_video_mins__day'),
										        	'related_video_mins__day_title' => get_sub_field('related_video_mins__day_title'),
										        	'related_video_workout_title' => get_sub_field('related_video_workout_title'),
													'related_video_thumbnail' => get_sub_field('related_video_thumbnail'),
										        	'video_id_with_postid' => get_sub_field('related_video_id') .'_'. get_the_ID(),
										        	'video_view_count' => get_post_meta(get_the_ID(), get_sub_field('related_video_id') .'_'. get_the_ID(), true)
								    			);
								    		endwhile;
											endif;
											//print_r($embededVideo);
											function array_sort($array, $on, $order=SORT_ASC){

											    $new_array = array();
											    $sortable_array = array();

											    if (count($array) > 0) {
											        foreach ($array as $k => $v) {
											            if (is_array($v)) {
											                foreach ($v as $k2 => $v2) {
											                    if ($k2 == $on) {
											                        $sortable_array[$k] = $v2;
											                    }
											                }
											            } else {
											                $sortable_array[$k] = $v;
											            }
											        }

											        switch ($order) {
											            case SORT_ASC:
											                asort($sortable_array);
											                break;
											            case SORT_DESC:
											                arsort($sortable_array);
											                break;
											        }

											        foreach ($sortable_array as $k => $v) {
											            $new_array[$k] = $array[$k];
											        }
											    }

											    return $new_array;
											}

											$short_list = array_sort($embededVideo, 'video_view_count', SORT_DESC);

											
											foreach ( $short_list as $var ) { ?>
												<div class="post-item">
										        <div class=" post-img">
										        <?php
													$video_url = 'YOUTUBE_VIDEO_URL';
													$video_id1 = '';
													$thumbnail_youtube =  '';
													$vimeo_url_img1 = '';
													if($var['select_related_video_type'] == 'Youtube')
													{
														$video_id1 = extractVideoID("https://www.youtube.com/embed/".$var['related_video_id']);
														if ($var['related_video_thumbnail']) {
															$thumbnail_youtube = $var['related_video_thumbnail']; 
														}
														else {
															$thumbnail_youtube =  getYouTubeThumbnailImage($video_id1);
															update_sub_field('related_video_thumbnail',$thumbnail_youtube, $var['id']);
														}
													}
													elseif ($var['select_related_video_type'] == 'vimeo') {
														/*vimeo thumbnail image get path in vimeo */
														$vimeo_url_img1 = '';
														try {
															if ($var['related_video_thumbnail']) {
																$vimeo_url_img1 = $var['related_video_thumbnail']; 
															}
															else {
																require_once ABSPATH . WPINC . '/http.php';
																$url = "https://vimeo.com/api/v2/video/".$var['related_video_id'].".json";
																$response = wp_remote_get($url);
																$json = json_decode( $response['body'], true );
																if ( false !== $json && $json[0]) {
																	$vimeo_url_img1 = $json[0]['thumbnail_large']; 
																	update_sub_field('related_video_thumbnail',$vimeo_url_img1, $var['id']);
																}
																else {
																	$url = "https://api.vimeo.com/videos/".$var['related_video_id']."/pictures/?sizes=200x150";
																	$args = array(
																		'headers' => array(
																		'Content-Type' => 'application/json',
																		'Authorization' => 'Bearer ' . $vimeo_api_token
																		)
																	);
																	
																	$response = wp_remote_get(
																		$url,
																		$args
																	);
																
																	$json = json_decode( $response['body'], true );
																	if ( false !== $json && $json['data']) {
																		$vimeo_url_img1 = $json['data'][0]['sizes'][0]['link_with_play_button']; 
																		update_sub_field('related_video_thumbnail',$vimeo_url_img1, $var['id']);
																	}
																}
																
															}
														} catch ( Exception $ex ) {
														}	
														/* end vimeo */
													}

										       		if($var['select_related_video_type'] == 'Youtube')
													{
													?>
													<a class="fancybox-media program_video" onclick="count('<?php echo $var['related_video_id'] ?>','<?php echo $var['id'] ?>')" href="https://www.youtube.com/embed/<?php echo $var['related_video_id']; ?>"><img src='<?php echo $thumbnail_youtube; ?>' class="" /></a>
													
													<!-- <iframe src="https://www.youtube.com/embed/<?php echo $related_video_id;?>" width="400" height="180" frameborder="0" allowfullscreen="allowfullscreen"></iframe> -->
													
													<?php 
													}
													elseif ($var['select_related_video_type'] == 'vimeo') {
													?>
													<a class="fancybox-media program_video" onclick="count('<?php echo $var['related_video_id'] ?>','<?php echo $var['id'] ?>')" href="https://player.vimeo.com/video/<?php echo $var['related_video_id']; ?>"><img src='<?php echo $vimeo_url_img1; ?>' /></a>
													
														<!-- <iframe src="https://player.vimeo.com/video/<?php echo $related_video_id;?>" width="400" height="180" frameborder="0" allowfullscreen="allowfullscreen"></iframe> -->
													<?php
													}
													else
													{
													?>
													
													<?php
													}
													?>
										        </div>
										        <div class=" post-cont">
										        	<div class="related-sub-title"><h2><?php echo $var['related_video_title'] ;?></h2></div>
										        	<div class="related-video-time">
										        		<?php echo $var['related_video_mins__day'].' '. $var['related_video_mins__day_title'];?> | <?php echo $var['related_video_workout_title']; ?>
										        	</div>
										        	<div class="related-calender-section">
										        		<a href="#">Add to Calendar</a>
										        	</div>

										        </div>
										    </div>
											<?php }
											//print_r($short_list);
								    		?>
									    	</div>
										</div>
									<?php endif; ?>
								</div></div>
				    </div>
				</div>
				</div>
					<?php endwhile; ?>
					<?php endif; ?>	

			    </div>
		  
		</div>
	</div>
</div>
<?php } ?>
<script type="text/javascript">
	jQuery(document).ready(function() {
	jQuery('.fancybox-media').fancybox({
		openEffect  : 'none',
		closeEffect : 'none',
		helpers : {
			media : {}
		}
	});
});
</script>
<script type="text/javascript">
	function count(videoid,postid){
		var data = {
			action: 'video_postmeta_input',
			video_id: videoid,
			post_id: postid
		};
		var ajaxurl = my_ajax_object.ajax_url;  //WHAT IS THIS?!?!
		jQuery.post(ajaxurl, data, function(response) {
		});
	}
</script>

<?php get_footer(); ?>	