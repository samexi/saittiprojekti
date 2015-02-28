<?php

class simple_cck_fields_settings extends wp_simple_cck_build {
 
 function field_settings() {
   $setting = $_REQUEST['setting'];
   $content_type_id = $_REQUEST['content_type_id'];
   if($content_type_id > 0) {
    $_SESSION['content_type_id'] = $content_type_id;
   }
   $content_type_id = $this->content_type_id;
   if($content_type_id == 0) {
    $content_type_id = $_SESSION['content_type_id'];
   }
   
   switch($setting) {
    case 'set_done':
      $this->field_settings_save();
      $this->redirect($this->build_url('&act=attr&content_type_id='.$this->content_type_id));
    break;
    default:
      $this->settings_form();
    break;
  }
   
 }
 
 function get_post_data($id) {
  global $wpdb;
  $post_type = $wpdb->get_var("select post_type from ".$wpdb->prefix."posts where ID = '$id'");
  if(!$post_type) return;
  
  $content_type_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$post_type'");
  if(!$content_type_id) return;
  $fields = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields where content_type_id = '$content_type_id'");
  $array = array();
  for($i =0; $i < count($fields);++$i) {
   if($fields[$i]->field_type == 'image') {
     $attach_id = get_post_meta($id,$fields[$i]->field_name,true);
     $attached_post = get_post($attach_id);
     $images_arr = wp_get_attachment_image_src($attach_id,'large');
     $array[$fields[$i]->field_name] = array('image_data' => $images_arr,'alt' =>  $attached_post->post_title);
     
   }else if($fields[$i]->field_type == 'file') {
     
     $attach_id = get_post_meta($id,$fields[$i]->field_name,true);
     $file_url = wp_get_attachment_url($attach_id );
     $array[$fields[$i]->field_name] = array('file' => $file_url);
     
   }else {
    
    $value = get_post_meta($id,$fields[$i]->field_name,true);
    $array[$fields[$i]->field_name] = $value;
    
   }
  }
  return $array;
 }
 
 

 
 function settings_form() {
  global $wpdb;
  
  $field_row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type_fields where id = '".$this->field_id."'");
  ?>
  <a href="<?php print $this->build_url('&act=attr&content_type_id='.$this->content_type_id);?>">Back</a>
  <form method="post">
  <input type="hidden" name="field_id" value="<?php print $this->field_id;?>">
  <input type="hidden" name="content_type_id" value="<?php print $this->content_type_id;?>">
  <input type="hidden" name="setting" value="set_done">
  <div class="field_settings_div">
   <?php
    $showinlist = 0;
  if($field_row->field_type == 'select') {
   $this->select_settings($field_row, $showinlist); 
  }
  
  if($field_row->field_type == 'textfield') {
   $this->textfield_settings($field_row, $showinlist); 
  }
  
  if($field_row->field_type == 'checkbox' || $field_row->field_type == 'radio') {
   $this->checkbox_radio_settings($field_row, $showinlist); 
  }
  
  if($field_row->field_type == 'image') {
   $this->image_settings($field_row, $showinlist); 
  }
  if($field_row->field_type == 'file') {
   $this->file_settings($field_row, $showinlist); 
  }
  if($field_row->field_type == 'textarea') {
   $this->textarea_settings($field_row, $showinlist); 
  }
  
  if($field_row->field_type == 'date') {
   $this->date_settings($field_row, $showinlist); 
  }
  
  if($field_row->field_type == 'taxonomy') {
   $this->taxonomy_settings($field_row, $showinlist); 
  }
  
  if($field_row->field_type == 'user_reference') {
   $this->user_reference_settings($field_row, $showinlist); 
  }
  
  if($field_row->field_type == 'content_reference') {
   $this->content_reference_settings($field_row, $showinlist); 
  }
  $checked = '';
  if($showinlist == 1) {
   $checked = ' checked ';
  }
  ?>
  
  <tr>
     <td><label for="show_in_list">Show In List</label></td>
     <td><input type="checkbox" name="show_in_list" id="show_in_list" value="1" <?php print $checked; ?>></td>
    </tr>  
  </table>
  </div>
   <input type="submit" value="Submit" class="button-primary">
  </form>
  <?php
  
 }
 

 
 function settings_fields($type) {
  $array = array();
  $array['select'] = array(
    'field_multiselect' => 'vc'
    ,'field_options' => 'tx'
    ,'field_first_row_black' => 'vc'
    ,'select_height' => 'vc');
  
  $array['textfield'] = array('textfield_size' => 'vc','textfield_max_length' => 'vc');
  $array['checkbox'] = array('field_options' => 'tx');
  $array['radio'] = array('field_options' => 'tx');
  $array['image'] = array('resize' => 'vc','width' => 'vc','height' => 'vc','location' => 'vc','image_alt' => 'vc','create_all_resized' => 'vc');
  $array['file'] = array('location' => 'vc');
  $array['textarea'] = array('textarea_htmleditor' => 'vc','textarea_row' => 'vc','textarea_col' => 'vc');
  $array['date'] = array('date_format' => 'vc');
  $array['user_reference'] = array('user_reference_roles' => 'tx','user_reference_input_type' => 'vc');
  $array['content_reference'] = array('content_reference_post_type' => 'tx','content_reference_input_type' => 'vc');
  $array['taxonomy'] = array('name' => 'vc'
                            ,'singular_name'       => 'vc'
                            ,'search_items'        => 'vc'
                            ,'all_items'           => 'vc'
                            ,'parent_item'         => 'vc'
                            ,'edit_item'           => 'vc'
                            ,'update_item'         => 'vc'
                            ,'add_new_item'        => 'vc'
                            ,'new_item_name'       => 'vc'
                            ,'menu_name'           => 'vc'
                            ,'hierarchical'       => 'vc'
                            ,'show_ui'            => 'vc'
                            ,'show_admin_column'  => 'vc'
                            ,'query_var'           => 'vc'
                            );
  
  $array[$type]['show_in_list'] = 'vc';
  
  return $array[$type];
 }
 
 
  
 function get_dom($type = 'yn') {
  $dom['yn'] = array('no' => 'No','yes' => 'Yes');
  $dom['tf'] = array('0' => false,1 => true);
  $dom['dateformat'] = array('mm/dd/yy' => 'mm/dd/yy'
                             ,'dd/mm/yy' => 'dd/mm/yy'
                             ,'yy/mm/dd' => 'yy/mm/dd'
                             ,'mm-dd-yy' => 'mm-dd-yy'
                             ,'dd-mm-yy' => 'dd-mm-yy'
                             ,'yy-mm-dd' => 'yy-mm-dd'
                             );
  
  return $dom[$type];
 }
 
 function content_reference_settings($field_row, &$showinlist) {
  global $wpdb;
  
  $fields = $this->settings_fields('content_reference');
  
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  
  $content_reference_post_type_arr = explode(",",$content_reference_post_type->attribute_value);
  
  $sql = "select post_type from ".$wpdb->prefix."posts group by post_type";
  $rows = $wpdb->get_results($sql);
  
  ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
    <tr>
    <td><label for="user_reference_roles">Select Roles:</label></td>
    <td>
   <?php
    
    for($i = 0; $i < count($rows); ++$i) {
     if(in_array($rows[$i]->post_type,$content_reference_post_type_arr)) {
      print '<br /><input checked type="checkbox" name="content_reference_post_type[]" id="'.$rows[$i]->post_type.'" value="'.$rows[$i]->post_type.'">&nbsp;<label for="'.$rows[$i]->post_type.'">'.$rows[$i]->post_type.'</label>'; 
     }else {
      print '<br /><input type="checkbox" name="content_reference_post_type[]" id="'.$rows[$i]->post_type.'" value="'.$rows[$i]->post_type.'">&nbsp;<label for="'.$rows[$i]->post_type.'">'.$rows[$i]->post_type.'</label>'; 
     }
    }
    $autocomplete_checked = '';
    $select_list_checked = '';
    if($content_reference_input_type->attribute_value == 'autocomplete') {
     $autocomplete_checked = 'checked';
    }else {
     $select_list_checked = 'checked';
    }
   ?>
   </td>
   </tr>
    <tr>
     <td><label for="content_reference_input_type1">Autocomplete</label></td>
     <td><input <?php print $autocomplete_checked;?> type="radio" name="content_reference_input_type" id="content_reference_input_type1" value="autocomplete"></td>
    </tr>
    <tr>
     <td><label for="content_reference_input_type2">Select Box</label></td>
     <td><input <?php print $select_list_checked;?> type="radio" name="content_reference_input_type" id="content_reference_input_type2" value="select_list"></td>
    </tr>    
  
  <?php
  
 }
 
 function user_reference_settings($field_row, &$showinlist) {
  global $wpdb,$wp_roles;
  
  $fields = $this->settings_fields('user_reference');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
  }
  $showinlist = $show_in_list->attribute_value;
  $roles = $wp_roles->get_names();
  $user_reference_roles_arr = explode(",",$user_reference_roles->attribute_value);
  ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
    <tr>
    <td><label for="user_reference_roles">Select Roles:</label></td>
    <td>
   <?php
    foreach($roles as $role_key => $role_lbl) {
     if(in_array($role_key,$user_reference_roles_arr)) {
      print '<br /><input checked type="checkbox" name="user_reference_roles[]" id="'.$role_key.'" value="'.$role_key.'">&nbsp;<label for="'.$role_key.'">'.$role_lbl.'</label>'; 
     }else {
      print '<br /><input type="checkbox" name="user_reference_roles[]" id="'.$role_key.'" value="'.$role_key.'">&nbsp;<label for="'.$role_key.'">'.$role_lbl.'</label>'; 
     }
    }
    $autocomplete_checked = '';
    $select_list_checked = '';
    if($user_reference_input_type->attribute_value == 'autocomplete') {
     $autocomplete_checked = 'checked';
    }else {
     $select_list_checked = 'checked';
    }
   ?>
   </td>
   </tr>
    <tr>
     <td><label for="user_reference_input_type1">Autocomplete</label></td>
     <td><input <?php print $autocomplete_checked;?> type="radio" name="user_reference_input_type" id="user_reference_input_type1" value="autocomplete"></td>
    </tr>
    <tr>
     <td><label for="user_reference_input_type2">Select Box</label></td>
     <td><input <?php print $select_list_checked;?> type="radio" name="user_reference_input_type" id="user_reference_input_type2" value="select_list"></td>
    </tr>    
  
  <?php
  
 }
 
 
 function taxonomy_settings($field_row, &$showinlist) {
  global $wpdb;
  
  $fields = $this->settings_fields('taxonomy');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  $labels = array(
    'name'                => 'Name',
    'singular_name'       => 'Singular Name',
    'search_items'        => 'Searh Items',
    'all_items'           => 'All Items',
    'parent_item'         => 'Parent Item',
    'edit_item'           => 'Edit Item', 
    'update_item'         => 'Update Item',
    'add_new_item'        => 'Add New Item',
    'new_item_name'       => 'New Item',
    'menu_name'           => 'Menu Name'
  );
  
  ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
   <?php
   foreach($labels as $k => $lbl) {
    ?>
    <tr>
    <td><?php print $lbl;?>:</td>
    <td><input type="text" name="<?php print $k;?>" value="<?php print $$k->attribute_value;?>"></td>
   </tr>
    <?php
   }
   
   $args = array(
    'hierarchical'        => true,
    'show_ui'             => true,
    'show_admin_column'   => true,
    'query_var'           => true,
    'rewrite'             => array( 'slug' => 'genre' )
  );
   ?>
   <tr>
    <td>Hierarchical:</td>
    <td><select name="hierarchical" id="hierarchical"><?php print $this->build_select($this->get_dom('yn'),$hierarchical->attribute_value);?></select></td>
   </tr>
   <tr>
    <td>Show Ui:</td>
    <td><select name="show_ui" id="show_ui"><?php print $this->build_select($this->get_dom('yn'),$show_ui->attribute_value);?></select></td>
   </tr>
   <tr>
    <td>show_admin_column:</td>
    <td><select name="show_admin_column" id="show_admin_column"><?php print $this->build_select($this->get_dom('yn'),$show_admin_column->attribute_value);?></select></td>
   </tr>
   <tr>
    <td>Query Var:</td>
    <td><select name="query_var" id="query_var"><?php print $this->build_select($this->get_dom('yn'),$query_var->attribute_value);?></select></td>
   </tr>         
  
  <?php
 }
 
 function date_settings($field_row, &$showinlist) {
   global $wpdb;
  
  $fields = $this->settings_fields('date');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
    <tr>
    <td>Date Format:</td>
    <td><select name="date_format" id="date_format"><?php print $this->build_select($this->get_dom('dateformat'),$date_format->attribute_value);?></select></td>
   </tr>
  
  <?php
 }

 function textarea_settings($field_row, &$showinlist) {
  global $wpdb;
  
  $fields = $this->settings_fields('textarea');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
   <tr>
    <td>HTML Editor:</td>
    <td><select name="textarea_htmleditor" id="textarea_htmleditor"><?php print $this->build_select($this->get_dom('yn'),$textarea_htmleditor->attribute_value);?></select></td>
   </tr>
   <tr>
    <td>Rows:</td>
    <td>
     <input type="text" name="textarea_row" id="textarea_row" value="<?php print $textarea_row->attribute_value;?>">
    </td>
   </tr>
   <tr>
    <td>Cols:</td>
    <td>
     <input type="text" name="textarea_col" id="textarea_col" value="<?php print $textarea_col->attribute_value;?>">
    </td>
   </tr>
  
  <?php
 }
 
 function file_settings($field_row, &$showinlist) {
  global $wpdb;
  
  $fields = $this->settings_fields('image');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  
    ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
   <tr>
    <td>Location (type folder name):</td>
    <td>
     <input type="text" name="location" id="location" value="<?php print $location->attribute_value;?>">
     <small>This folder will be append after uploads folder</small>
    </td>
   </tr>
  
  <?php
 }
 
 function image_settings($field_row, &$showinlist) {
  global $wpdb;
  
  $fields = $this->settings_fields('image');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  
    ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0" class="field_settings">
    <tr>
    <td>Textbox for alt tag:</td>
    <td><select name="image_alt" id="image_alt"><?php print $this->build_select($this->get_dom('yn'),$image_alt->attribute_value);?></select></td>
   </tr>
   <tr>
    <td>Resize:</td>
    <td><select name="resize" id="resize"><?php print $this->build_select($this->get_dom('yn'),$resize->attribute_value);?></select></td>
   </tr>
   <tr>
    <td>Width:</td>
    <td>
     <input type="text" name="width" id="width" value="<?php print $width->attribute_value;?>">
    </td>
   </tr>
   <tr>
    <td>Height:</td>
    <td>
     <input type="text" name="height" id="height" value="<?php print $height->attribute_value;?>">
    </td>
   </tr>
   <tr>
    <td>Location (type folder name):</td>
     <td>
     <input type="text" name="location" id="location" value="<?php print $location->attribute_value;?>">
     <small>This folder will be append after uploads folder</small>
    </td>
   </tr>
   <?php
   $checked  = '';
   if($create_all_resized->attribute_value == 1) {
   $checked  = ' checked '; ;
   }
   ?>
   <tr>
    <td>Also Create all resized Copy as defined in media settings :</td>
     <td>
     <input type="checkbox" name="create_all_resized" id="create_all_resized" value="1" <?php print $checked;?>>
    </td>
   </tr>
  
  <?php
 }
 function checkbox_radio_settings($field_row, &$showinlist) {
   global $wpdb;
  
  $fields = $this->settings_fields('select');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  
    ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
   <tr>
    <td>Options:</td>
    <td>
     <textarea name="field_options" id="field_options" rows="5" cols="50"><?php print $field_options->attribute_value; ?></textarea><br>
     <em>Enter pipe(|) seperated key|value pair with new line</em>
    </td>
   </tr>
  
   
  <?php
 }
 
 
 
 function textfield_settings($field_row, &$showinlist) {
  global $wpdb;
  
  $fields = $this->settings_fields('textfield');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
   $showinlist = $show_in_list->attribute_value;
  }
  
    ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
  <tr>
   <td>Size:</td>
   <td><input type="text" name="textfield_size" id="textfield_size" value="<?php print $textfield_size->attribute_value; ?>"></td>
  </tr>
  <tr>
   <td>Max Length:</td>
   <td><input type="text" name="textfield_max_length" id="textfield_max_length" value="<?php print $textfield_max_length->attribute_value; ?>"></td>
  </tr>
  
  <?php
 }
 
 function select_settings($field_row, &$showinlist) {
  global $wpdb;
  
  $fields = $this->settings_fields('select');
  foreach($fields as $field => $type) {
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $$field = $wpdb->get_row("select * from $table where field_id = '$this->field_id' and attribute_name = '$field'");
  }
  $showinlist = $show_in_list->attribute_value;
    ?>
  <h2>Settigns for : <?php print $field_row->field_name;?></h2>
  <input type="hidden" name="type" value="<?php print  $field_row->field_type; ?>">
  <table cellpadding="15" cellspacing="0" border="0">
   <tr>
    <td>Multiselect:</td>
    <td><select name="field_multiselect" id="field_multiselect"><?php print $this->build_select($this->get_dom('yn'),$field_multiselect->attribute_value);?></select></td>
   </tr>
   <tr>
    <td>Multi Select Height:</td>
    <td><input type="text" name="select_height" id="select_height" value="<?php print $select_height->attribute_value; ?>"></td>
   </tr>
   
   <tr>
    <td>Options:</td>
    <td>
     <textarea name="field_options" id="field_options" rows="5" cols="50"><?php print $field_options->attribute_value; ?></textarea><br>
     <em>Enter pipe(|) seperated key|value pair with new line</em>
    </td>
   </tr>
   <tr>
    <td>First Row Blank:</td>
    <td><select name="field_first_row_black" id="field_first_row_black"><?php print $this->build_select($this->get_dom('yn'),$field_first_row_black->attribute_value);?></select></td>
   </tr>
  
   
  <?php
 }
 
 function field_settings_save() {
  global $wpdb;
  
  $type = $_POST['type'];
  $field_id = $_POST['field_id'];
  
  $fields = $this->settings_fields($type);
  
  foreach($fields as $field => $storage_type) {
     $this->update_field_attribute($field_id,$field,$_POST[$field],$type);
  }
    
  /* $data = array();
   $data[] = " field_id = '$field_id'";
   $data[] = "attribute_name=  '$field'";
   $data[] = "attribute_value = '".$_POST[$field]."'";
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   $id = $wpdb->get_var("select id from $table where attribute_name = '$field' and field_id = '$field_id'");
    if($id > 0) {
     $sql = "update ".$table." set ".implode(",",$data)." where id= '$id'" ;
     $wpdb->query($sql);
    }else {
     $sql = "insert into ".$table." set ".implode(",",$data);
     $wpdb->query($sql); 
    }
  }*/
 }
 
 function create_default_field_settings($field_id,$field_type,$field_name,$field_label) {
  global $wpdb;

  if($field_type == 'textfield') {
   $this->update_field_attribute($field_id,'textfield_size',30,$field_type);
  }
  if($field_type == 'select') {
   $this->update_field_attribute($field_id,'field_multiselect','no',$field_type);
   $this->update_field_attribute($field_id,'field_first_row_black','yes',$field_type);
   
  }
  if($field_type == 'checkbox') {
   $this->update_field_attribute($field_id,'field_options',$field_name.'|'.$field_label,$field_type);
  }  
  if($field_type == 'radio') {
   $this->update_field_attribute($field_id,'field_options',$field_name.'|'.$field_label,$field_type);
  }
  if($field_type == 'image') {
   $this->update_field_attribute($field_id,'resize','no',$field_type);
   $this->update_field_attribute($field_id,'image_alt','no',$field_type);
  }
  if($field_type == 'textarea') {
   $this->update_field_attribute($field_id,'textarea_htmleditor','yes',$field_type);
   $this->update_field_attribute($field_id,'textarea_row',5,$field_type);
   $this->update_field_attribute($field_id,'textarea_col',50,$field_type);
  }
  
  if($field_type == 'date') {
    $this->update_field_attribute($field_id,'date_format','mm-dd-yy',$field_type);
  }
  if($field_type == 'taxonomy') {
    $this->update_field_attribute($field_id,'name',$field_name,$field_type);
    $this->update_field_attribute($field_id,'singular_name',$field_name,$field_type);
    $this->update_field_attribute($field_id,'search_items','Search ' . $field_label,$field_type);
    $this->update_field_attribute($field_id,'all_items','All ' . $field_label,$field_type);
    $this->update_field_attribute($field_id,'parent_item','Parent ' . $field_label,$field_type);
    $this->update_field_attribute($field_id,'edit_item','Edit ' . $field_label,$field_type);
    $this->update_field_attribute($field_id,'update_item','Update ' . $field_label,$field_type);
    $this->update_field_attribute($field_id,'add_new_item','Add New ' . $field_label,$field_type);
    $this->update_field_attribute($field_id,'new_item_name','New ' . $field_label,$field_type);
    $this->update_field_attribute($field_id,'menu_name', $field_label,$field_type);
    $this->update_field_attribute($field_id,'hierarchical','yes',$field_type);
    $this->update_field_attribute($field_id,'show_ui','yes',$field_type);
    $this->update_field_attribute($field_id,'show_admin_column','yes',$field_type);
    $this->update_field_attribute($field_id,'query_var','yes',$field_type);
  }  
  
  
  
  
 }
 
 function update_field_attribute($field_id,$attribute_name,$attribute_value,$field_type) {
   global $wpdb;
   
   $fields = $this->settings_fields($field_type);
   
   
   $type = $fields[$attribute_name];
   if($type == 'vc') {
    $table = $wpdb->prefix."scck_content_type_fields_detail";
   }else {
    $table = $wpdb->prefix."scck_content_type_fields_detail_tx";
   }
   
   $id = $wpdb->get_var("select id from $table where attribute_name = '$attribute_name' and field_id = '$field_id'");
   if(is_array($attribute_value)) {
    $attribute_value = implode(",",$attribute_value);
   }
   $data = array();
   $data[] = " field_id = '$field_id'";
   $data[] = "attribute_name=  '$attribute_name'";
   $data[] = "attribute_value = '$attribute_value'";
   
   if($id > 0) {
    $sql = "update ".$table." set ".implode(",",$data)." where id= '$id'" ;
    $wpdb->query($sql);
   }else {
    $sql = "insert into ".$table." set ".implode(",",$data);
    $wpdb->query($sql); 
   }
   
   
 }
 
 
  function install() {
    global $wpdb;
  
    
    $sql = "CREATE TABLE IF NOT EXISTS `".$this->tbl_content_type."` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `enable` int(1) NOT NULL,
    `product_machine_name` varchar(50) NOT NULL,
    `product_label` varchar(50) NOT NULL,
    `add_new_label` varchar(50) NOT NULL,
    `edit_label` varchar(50) NOT NULL,
    `all_product_label` varchar(50) NOT NULL,
    `view_label` varchar(50) NOT NULL,
    `search_label` varchar(50) NOT NULL,
    `menu_label` varchar(50) NOT NULL,
    `product_public` int(1) NOT NULL,
    `has_archive` int(1) NOT NULL,
    `publicly_queryable` int(1) NOT NULL,
    `exclude_from_search` int(1) NOT NULL,
    `show_ui` int(1) NOT NULL,
    `show_in_menu` int(1) NOT NULL,
    `menu_id` int(11) NOT NULL,
    `weight` int(11) NOT NULL,
    `category` int(1) NOT NULL,
    `post_tag` int(1) NOT NULL,
    `supports` text NOT NULL,
    `meta_title` text NOT NULL,
    `meta_desc` text NOT NULL,
    `meta_keyword` text NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    dbDelta($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS `".$this->tbl_fields."` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `content_type_id` int(11) NOT NULL,
    `field_name` varchar(250) NOT NULL,
    `field_type` varchar(50) NOT NULL,
    `field_label` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    dbDelta($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS `".$this->tbl_fields_detail."` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL,
    `attribute_name` varchar(100) NOT NULL,
    `attribute_value` varchar(25) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS `".$this->tbl_fields_detail_tx."` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL,
    `attribute_name` varchar(100) NOT NULL,
    `attribute_value` text NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    dbDelta($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS `".$this->tbl_fields_group."` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `content_type_id` int(11) NOT NULL,
    `group_name` varchar(100) NOT NULL,
    `group_title` varchar(100) NOT NULL,
    `weight` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    dbDelta($sql);    
    
    $sql = "CREATE TABLE IF NOT EXISTS `".$this->tbl_fields_group_rel."` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL,
    `group_id` int(11) NOT NULL,
    `weight` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS `".$this->tbl_menu."` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `menuname` varchar(50) NOT NULL,
    `position` int(11) NOT NULL,
    `weight` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
    dbDelta($sql);    
    
    $sql = "INSERT INTO `".$this->tbl_menu."` (`id`, `menuname`, `position`, `weight`) VALUES (1, 'No Menu', 14, -1);";
    dbDelta($sql);
    
    $sql = "INSERT INTO `".$this->tbl_content_type."`
    (`id`, `enable`, `product_machine_name`, `product_label`, `add_new_label`, `edit_label`, `all_product_label`, `view_label`, `search_label`, `menu_label`, `product_public`, `has_archive`, `publicly_queryable`, `exclude_from_search`, `show_ui`, `show_in_menu`, `menu_id`, `weight`)
    VALUES
    (1, 1, 'post', 'post', 'Add New post', 'Edit post', 'All posts', 'View post', 'Search post', 'post', 1, 1, 1, 1, 1, 1, 1, 0),
    (2, 1, 'page', 'page', 'Add New page', 'Edit page', 'All pages', 'View page', 'Search page', 'page', 1, 1, 1, 1, 1, 1, 1, 0);";
   
   dbDelta($sql);
   
   $sql = "INSERT INTO `".$this->tbl_fields_group."`
   (`id`, `content_type_id`, `group_name`, `group_title`, `weight`) VALUES
   (1, 1, 'default', 'More post Detail', 0),
   (2, 2, 'default', 'More page Detail', 0);";
   
   dbDelta($sql);

  }
  
  function uninstall() {
    global $wpdb;
    //not working for security reasons please delete date yourself
    $sql = "DROP TABLE IF EXISTS ".$this->tbl_content_type;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    //dbDelta($sql);
    
    $sql = "DROP TABLE IF EXISTS ".$this->tbl_fields;
    //dbDelta($sql);
    
    $sql = "DROP TABLE IF EXISTS ".$this->tbl_fields_detail;
    //dbDelta($sql);
    
    $sql = "DROP TABLE IF EXISTS ".$this->tbl_fields_detail_tx;
    //dbDelta($sql); 
    
    $sql = "DROP TABLE IF EXISTS ".$this->tbl_fields_group;
    //dbDelta($sql); 
    
    $sql = "DROP TABLE IF EXISTS ".$this->tbl_fields_group_rel;
    //dbDelta($sql); 
    
    $sql = "DROP TABLE IF EXISTS ".$this->tbl_menu;
    //dbDelta($sql); 
    
  }
}
