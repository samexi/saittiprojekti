<?php
/*
  Plugin Name: Flexible Post Filter
  Description: Create a Fully Responsive Porfolio to Display and Filter Your Posts by Category.
  Author: LMcGuire, WPinHarmony
  Version: 1.0
  
___________________________________________________________________

Copyright 2014

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

	
// ==================================================================
// Register & Define Plugin Files
// =================================================================	

define('define_path',plugin_dir_path(__FILE__ ));

	require_once(define_path . 'includes/admin.php');
	require_once(define_path . 'includes/libraries.php');
	    
	
function register_filter_style() {
	wp_register_style( 'filter-style', plugins_url( 'css/fpf.css', __FILE__));
	wp_enqueue_style( 'filter-style' );	
}
add_action( 'wp_enqueue_scripts', 'register_filter_style' );


function register_admin_style() {
	wp_register_style( 'admin-style', plugins_url( 'css/admin.css', __FILE__));	
	wp_enqueue_style( 'admin-style' );	
}
add_action( 'admin_enqueue_scripts', 'register_admin_style' );


function register_filter_scripts() {
		
        wp_register_script('quicksand-js', plugins_url('js/jquery.quicksand.1.4.js', __FILE__), array('jquery'));
        wp_register_script('easing-js', plugins_url('js/jquery.easing.1.3.js', __FILE__), array('jquery'));
		wp_register_script('fpf-admin', plugins_url('js/fpf.js', __FILE__), array('jquery'));
       
		
        wp_enqueue_script('quicksand-js');
        wp_enqueue_script('easing-js');
        wp_enqueue_script('fpf-admin');
      
    }

add_action( 'wp_enqueue_scripts', 'register_filter_scripts' );

// ==================================================================
// Add Font Awesome
// ==================================================================

add_action( 'wp_enqueue_scripts', 'webendev_load_font_awesome', 99 );

function webendev_load_font_awesome() {
	if ( ! is_admin() ) {
 
		wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', null, '4.0.3' );
 
	} 
}

// ==================================================================
// Add the CSS Options
// =================================================================

function filter_posts_customize_options () {?>

 <style type="text/css">
  <?php //Set the CSS Options
  
 		 if( get_option('fpf_button_background') ) { ?>ul.filterOptions li a {background: <?php echo get_option('fpf_button_background'); ?>;}<?php } ?>
 		 
 		  <?php if( get_option('fpf_thumbnail_border') ) { ?>.view {border: 10px solid <?php echo get_option('fpf_thumbnail_border'); ?>;}<?php } ?>	
  
 		 <?php if( get_option('fpf_button_text') ) { ?>ul.filterOptions li a {color: <?php echo get_option('fpf_button_text'); ?>;}<?php } ?>	
  
 		 <?php if( get_option('fpf_button_hover') ) { ?>ul.filterOptions li a:hover {background: <?php echo get_option('fpf_button_hover'); ?>;}<?php } ?>
  
 		 <?php if( get_option('fpf_button_selection') ) { ?>ul.filterOptions li.active a {background: <?php echo get_option('fpf_button_selection'); ?>;}<?php } ?>
   
 		 <?php if( get_option('jqs_button_alignment') ) { ?>ul.filterOptions {text-align: <?php echo get_option('jqs_button_alignment'); ?>;}<?php } ?>

</style> 
<?php
}

add_action ('wp_head', 'filter_posts_customize_options');



// ==================================================================
// Quicksand Function with Options
// =================================================================
function flexible_post_filter() { 
global $jqs_categories, $FPFpluginname, $FPFshortname, $FPFoptions;

 //Get the Categories Option
						
$FPF_categories = get_option ('jqs_select_categories');	
					
?>

<div class="options-container">
<ul class="filterOptions">
<li class="active"><a href="#" class="all">All</a></li>
 <?php
 
 //Filter by Categories


						// Get the Category				
						$types = $FPF_categories;
						
						// set a count to the amount of categories in our taxonomy
						$count = count($types); 
						
						// set a count value to 0
						$i=0;
						
						// test if the count has any categories
						if ($count > 0) {
							
							// break each of the categories into individual elements
							foreach ($types as $type) {
							
							$term = get_term_by('name',$type,'category');
								
								// increase the count by 1
								$i++;
								
								// rewrite the output for each category
								$type_list .= '<li><a href="#" class="'. $term->slug .'">' . $term->name . '</a></li>';
								
								// if count is equal to i then output blank
								if ($count != $i)
								{
									$type_list .= '';
								}
								else 
								{
									$type_list .= '';
								}
							}
							
							// print out each of the categories in our new format
							echo $type_list;
						}
?>					
					
</ul>


<ul class="ourHolder">
	
<?php

//Enable Pagination
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

//Pass the Selected Categories to the Loop
$categories = get_option('jqs_select_categories');
$cats = array();
foreach ($categories as $category) {
$term = get_term_by ('name',$category,'category'); 
$cats[] = $term->term_id;
}

//Start the Loop 
$wpbp = new WP_Query(array( 'post_type' => 'post', 'category__in' => $cats, 'posts_per_page' => '-1', 'paged' => $paged) ); 

if ($wpbp->have_posts()) : while ($wpbp->have_posts()) : $wpbp->the_post();

$types = get_the_category( get_the_ID(), $FPF_categories); 

?>

<li id="item" data-id="id-<?php echo $count; ?>" data-type="<?php foreach ($types as $type) { echo $type->slug. ' '; }?>">

			<div class="view">
			<div class="thumbnail"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >
			<?php the_post_thumbnail(); ?>
			</a></div></div>
					
            
            <div class="caption">
            
               <?php 
            //Include the Title Below the Thumbnail
            if (get_option('jqs_feature_enable_title') ) {?> 
            <div class="title">
            <?php echo get_the_title(); ?>
            </div>
           <?php }
           else {}
           ?>
          
           
             <?php 
             //Include the Author Below the Thumbnail
             if (get_option('jqs_feature_enable_author') ) {?> 
            <div class="author">
            <i class="fa fa-user"></i>
            <?php echo get_the_author(); ?>
            </div>
           <?php }
           else {}
           ?>
           
           <?php 
           //Include the Date Below the Thumbnail 
           if (get_option('jqs_feature_enable_date') ) {?> 
            <div class="date">
            <i class="fa fa-bookmark-o"></i>
            <?php echo get_the_date('d M Y @ h:ia'); ?>
            </div>
           <?php }
           else {}
           ?>
         
           
</div>           
</li>
			<?php $count++; // Increase the count by 1 ?>		
			<?php endwhile; ?>	
			</ul>
			<?php endif;// END the Wordpress Loop ?>
			<?php wp_reset_query(); // Reset the Query Loop?>
</div>
</div>


<?php
  }

add_shortcode('flexible-post' , 'flexible_post_filter')
?>