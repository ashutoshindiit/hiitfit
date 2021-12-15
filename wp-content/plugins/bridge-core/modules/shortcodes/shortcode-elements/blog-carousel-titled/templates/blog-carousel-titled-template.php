<?php
$pager = rand(1,9999);
?>
<div class="qode-blog-carousel-titled" <?php echo bridge_qode_get_inline_attrs($holder_data); ?> data-pager="pager_<?php echo $pager?>" >
		
	<div class="qode-bct-title-holder" <?php echo bridge_qode_get_inline_style($title_style); ?>>
		<a class="qode-bct-caroufredsel-prev" href="#">
			<span class="qode-bct-caroufredsel-nav-inner">
				<span class="qode-bct-caroufredsel-nav-icon-holder">
					<span class="arrow_carrot-left"></span>
				</span>
			</span>
		</a>
			<?php if($title): ?>
					<<?php echo esc_attr($title_tag); ?>>
						<?php echo esc_attr($title); ?>
					</<?php echo esc_attr($title_tag); ?>>
			<?php endif; ?>
	<a class="qode-bct-caroufredsel-next" href="#">
		<span class="qode-bct-caroufredsel-nav-inner">
			<span class="qode-bct-caroufredsel-nav-icon-holder">
				<span class="arrow_carrot-right"></span>
			</span>
		</span>
	</a>
	</div>
	<?php if($blog_query->have_posts()): ?>
		<div class="qode-bct-posts-holder ">
		<div class="qode-bct-posts">
			<?php while($blog_query->have_posts()): ?>
				<?php $blog_query->the_post(); ?>
					<div class="qode-bct-post">
						<?php 
						$bridge_qode_post_format = get_post_format();
						
						if($bridge_qode_post_format == 'video')
						{
							?>
						<div class="qode-bct-post-image">
    <?php $_video_type = get_post_meta(get_the_ID(), "video_format_choose", true);?>
    <?php if($_video_type == "youtube") { ?>
        <iframe name="fitvid-<?php the_ID(); ?>"  src="//www.youtube.com/embed/<?php echo get_post_meta(get_the_ID(), "video_format_link", true);  ?>?wmode=transparent" wmode="Opaque" width="805" height="210" allowfullscreen></iframe>
    <?php } elseif ($_video_type == "vimeo"){ ?>
        <iframe name="fitvid-<?php the_ID(); ?>" src="//player.vimeo.com/video/<?php echo get_post_meta(get_the_ID(), "video_format_link", true);  ?>?title=0&amp;byline=0&amp;portrait=0" frameborder="0" width="800" height="210" allowfullscreen></iframe>
    <?php } elseif ($_video_type == "self"){ ?>
        <div class="video">
            <div class="mobile-video-image" style="background-image: url(<?php echo get_post_meta(get_the_ID(), "video_format_image", true);  ?>);"></div>
            <div class="video-wrap"  >
                <video class="video" poster="<?php echo get_post_meta(get_the_ID(), "video_format_image", true);  ?>" preload="auto">
                    <?php if(get_post_meta(get_the_ID(), "video_format_webm", true) != "") { ?> <source type="video/webm" src="<?php echo get_post_meta(get_the_ID(), "video_format_webm", true);  ?>"> <?php } ?>
                    <?php if(get_post_meta(get_the_ID(), "video_format_mp4", true) != "") { ?> <source type="video/mp4" src="<?php echo get_post_meta(get_the_ID(), "video_format_mp4", true);  ?>"> <?php } ?>
                    <?php if(get_post_meta(get_the_ID(), "video_format_ogv", true) != "") { ?> <source type="video/ogg" src="<?php echo get_post_meta(get_the_ID(), "video_format_ogv", true);  ?>"> <?php } ?>
                    <object width="320" height="240" type="application/x-shockwave-flash" data="<?php echo get_template_directory_uri(); ?>/js/flashmediaelement.swf">
                        <param name="movie" value="<?php echo get_template_directory_uri(); ?>/js/flashmediaelement.swf" />
                        <param name="flashvars" value="controls=true&file=<?php echo get_post_meta(get_the_ID(), "video_format_mp4", true);  ?>" />
                        <img itemprop="image" src="<?php echo get_post_meta(get_the_ID(), "video_format_image", true);  ?>" width="1920" height="800" title="<?php echo esc_html__('No video playback capabilities', 'bridge'); ?>" alt="<?php echo esc_html__('Video thumb', 'bridge'); ?>" />
                    </object>
                </video>
            </div></div>
    <?php } ?>
						
</div>
						
						<?php
						}
						else{
							if(has_post_thumbnail()) : ?>
							<div class="qode-bct-post-image">
								<a href="<?php the_permalink() ?>" itemprop="url">
									<?php the_post_thumbnail($thumb_size); ?>
								</a>
							</div>
							<?php endif;
						}
						
						
						
						
						
						?>
						<div class="qode-bct-post-text">
							<<?php echo esc_attr($posts_title_tag); ?> class="qode-bct-post-title entry_title" itemprop="name">
								<a href="<?php the_permalink() ?>" itemprop="url"><?php the_title(); ?></a>
							</<?php echo esc_attr($posts_title_tag); ?>>
							<?php $excerpt = ($params['excerpt_length'] !== '' && $params['excerpt_length'] > 0) ? substr(get_the_excerpt(), 0, intval($params['excerpt_length'])).'...' : get_the_excerpt(); ?>
							<p itemprop="description" class = "qode-bct-post-excerpt"> <?php print wp_kses_post($excerpt); ?></p>
							<div class="qode-bct-post-date entry_date updated" itemprop="dateCreated">
								<?php the_time(get_option('date_format')); ?>
								<meta itemprop="interactionCount" content="UserComments: <?php echo get_comments_number(bridge_qode_get_page_id()); ?>"/>
							</div>
						</div>
					</div>
			<?php endwhile; ?>
		</div>
		</div>
<div id="pager_<?php echo $pager?>" class="sliderpager"></div>
	<?php endif; ?>
</div>