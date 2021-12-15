<?php
/**
* A Simple Category Template
*/
 
get_header();
$catid = get_the_id();
$category_data = get_queried_object();
$taxonomy_data = $category_data->taxonomy;
$parent_category_id = $category_data->term_id;

?> 
 
<section id="primary" class="site-content">
<div id="content" role="main">
  <div class="category-banner">
          <?php $image_banner = get_field('add_category_banner', $taxonomy_data . '_' . $parent_category_id); ?>  
          <?php 
          if(!empty($image_banner))
          {
          ?>
          <img src="<?php echo $image_banner; ?>" class="full-image-section"> 
          <?php } ?>
          </div>
            <div class="sub-cat-cont-main">
                        <div class="container_inner">
                                  <header class="archive-header">
                                  <h1 class="archive-title"><?php single_cat_title( '', true ); 
                                  ?></h1>
                                  <?php
                                  // Display optional category description
                                   if ( category_description() ) : ?>
                                  <div class="archive-meta"><?php echo category_description(); ?></div>
                                  <?php endif; ?>
                                  </header>

                                  <?php
                                  if( $parent_category_id != 0 )
                                  {

                                  ?>
                                  <div class="main-post-data-cls">
                                   <?php
                                     $args1 = array(

                                         'hierarchical' => 1,

                                         'show_option_none' => '',

                                         'hide_empty' => 0,

                                         'parent' => $parent_category_id,

                                       'taxonomy' => 'program_category'

                                     );

                                    $subcats = get_categories($args1);
                                    if(!empty($subcats))
                                    {
                                    echo '<div class="subcategory_main_section">';
                                    $i=1;
                                    foreach ($subcats as $key => $value) {
                                      if($i == 1)
                                      {
                                        $class_active_btn = "active";
                                      }
                                      else
                                      {
                                        $class_active_btn = "";
                                      }
                                      echo "<div class='sub-cat-btn'><a href='".get_category_link( $value->term_id )."' class= 'subcategory_btn_cls ".$class_active_btn."'>".$value->name."</a></div>";
                                    $i++;
                                    }
                                    echo '</div>';
                                    }
                                    else
                                    {
                                    echo '<div class="programs-category category-related-post-cls">';  
                                    while ( have_posts() ) : the_post();
                                    ?>
                                        <div class="program-cat content-section-cls">
                                          <div class="program-cat-in">
                                            <div class="feature-image-section fea-pro-img"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_post_thumbnail('custom-image-size');?></a></div>  
                                            <div class="program-cont-dtl"><a class="program-cat-name" href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></div>
                                          </div>
                                        </div>
                                    <?php
                                    endwhile; 
                                    echo '</div>';
                        }
                        ?>
                        </div>
                        <?php
                        }
                        ?>

                        <!-- <?php 

                        // Check if there are any posts to display
                        if ( have_posts() ) : ?> -->
                         

                        <!-- <?php
                        // The Loop
                        while ( have_posts() ) : the_post(); ?>
                        <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                        <small><?php the_time('F jS, Y') ?> by <?php the_author_posts_link() ?></small>
                         
                        <div class="entry">
                        <?php the_content(); ?>
                         
                         <p class="postmetadata"><?php
                          comments_popup_link( 'No comments yet', '1 comment', '% comments', 'comments-link', 'Comments closed');
                        ?></p>
                        </div>
                         
                        <?php endwhile; 
                         
                        else: ?>
                        <p>Sorry, no posts matched your criteria.</p>
                         
                         
                        <?php endif; ?> -->
                        </div>
  </div>
</div>
</section>
 
<?php
$term = get_query_var( 'term' );$term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy') );
/*print_r($term);*/
?>
 
<?php //get_sidebar(); ?>
<?php get_footer(); ?>