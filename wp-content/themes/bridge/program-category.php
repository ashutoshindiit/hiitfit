<?php 
/* Template Name: program-category */

get_header();
?>
<?php

$args = array(
       'hierarchical' => 1,
       'show_option_none' => '',
       'hide_empty' => 0,
       'taxonomy' => 'program_category'
    );
$subcats = get_categories($args);
/*foreach ($subcats as $category)
{

}
*/
?>
<?php
get_footer();
?>