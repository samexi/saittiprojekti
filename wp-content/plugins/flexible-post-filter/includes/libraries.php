<?php

// ==================================================================
// Set the Plugin Option Variables 
// =================================================================

function set_variables () {

//Get WP Categories
global $jqs_categories;
$jqs_categories = array();  
$jqs_categories_args = array('hide_empty' => 1, 'builtin' => false); 
$jqs_categories_obj = get_categories($jqs_categories_args);
foreach ($jqs_categories_obj as $jqs_cat) {
    $jqs_categories[$jqs_cat->cat_ID] = $jqs_cat->cat_name;}
$categories_tmp = array_unshift($jqs_categories);


global $FPFpluginname; 
$FPFpluginname = 'Plugin';

global $FPFshortname;
$FPFshortname = 'jqs_';

global $FPFoptions;
$FPFoptions = array (
 
array(
  'name' => 'Plugin',
  'type' => 'header'
  ),


// Set the General Options

array('type'=>'class','class'=>'<div id="filter">'), 

array(
'type' => 'title',
'heading' => 'General Options',
'note' => 'Create a Portfolio and Filter Your Posts',
), 


array (
'name' => 'Categories',
'id' => $FPFshortname.'select_categories',
'type' => 'multiselect',
'options' => $jqs_categories,
),

array(
'type' => 'sub-title',
'heading' => 'CSS Options',
'note' => 'Select the Button Color & Alignment of the Top Filter Menu',
), 

array(
  'name' => 'Top Menu - Alignment',
  'id' => $FPFshortname.'button_alignment',
  'type' => 'select',
  'options' => array('Center', 'Right', 'Left'),
),

array(
  'name' => 'Thumbnail - Border Color',
  'id' => 'fpf_thumbnail_border',
  'type' => 'color',
  'std' => '#f9f9f9',
  ),

array(
  'name' => 'Button - Background Color',
  'id' => 'fpf_button_background',
  'type' => 'color',
  'std' => '#d8d8d8',
  ),
  
  
  array(
  'name' => 'Button - Select Color',
  'id' => 'fpf_button_selection',
  'type' => 'color',
  'std' => '#1076ea',
  ),
  
  array(
  'name' => 'Button - Hover Color',
  'id' => 'fpf_button_hover',
  'type' => 'color',
  'std' => '#1bb783',
  ),
  
    array(
  'name' => 'Button - Text Color',
  'id' => 'fpf_button_text',
  'type' => 'color',
  'std' => '#000000',
  ),


array('type'=>'class','class'=>'</div>'),


//Set the Caption Options 
 
array('type'=>'class','class'=>'<div id="caption">'),

array(
'type' => 'title',
'heading' => 'Caption Options',
'note' => 'Choose What Elements to Display Below Post Thumbnails',
), 

array(
  'name' => 'Show Post Title',
  'id' => $FPFshortname.'feature_enable_title',
  'type' => 'checkbox',
  'options' => array('Include Post Title in Caption'),
),
 
array(
  'name' => 'Show Post Author',
  'id' => $FPFshortname.'feature_enable_author',
  'type' => 'checkbox',
  'options' => array('Include Post Author in Caption'),
),
 
array(
  'name' => 'Show Post Date',
  'id' => $FPFshortname.'feature_enable_date',
  'type' => 'checkbox',
  'options' => array('Include Post Date in Caption'),
),


array('type'=>'class','class'=>'</div>'),

);

	}

add_action( 'init', 'set_variables', 999 );
?>