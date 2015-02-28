<?php


class wp_simple_cck_build {

 function build_content_type() {
   global $wpdb;
   $results = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type where 	enable = 1 and product_machine_name not in('post','page')");
   
   $yesnodom = $this->get_dom('tf');
   for($i =0 ; $i < count($results); ++$i) {
   
     $options_arr = array();
     $options_arr['public'] = $yesnodom[$results[$i]->product_public];
     $options_arr['has_archive'] = $yesnodom[$results[$i]->has_archive];
     $options_arr['rewrite'] = array('slug' => $results[$i]->product_machine_name,'with_front' => true);
     $options_arr['publicly_queryable'] = $yesnodom[$results[$i]->publicly_queryable];
     $options_arr['exclude_from_search'] = $yesnodom[$results[$i]->exclude_from_search];
     $options_arr['show_ui'] = $yesnodom[$results[$i]->show_ui];
     $options_arr['show_in_menu'] = $yesnodom[$results[$i]->show_in_menu];
     $options_arr['menu_position'] = null;
     $options_arr['query_var'] = true;
     $options_arr['capability_type'] =  'post';
     $options_arr['hierarchical'] = true;
     $options_arr['supports'] = explode(",",$results[$i]->supports); 
     if($results[$i]->category == 1) {
       $options_arr['taxonomies'][] =  'category';
     }
     if($results[$i]->post_tag == 1) {
       $options_arr['taxonomies'][] =  'post_tag';
     }
     //$options_arr['register_meta_box_cb'] = 'scck_add_morefield_metaboxes_'.$i;
     $options_arr['labels']['name'] = __($results[$i]->product_label);
     $options_arr['labels']['singular_name'] = __($results[$i]->product_label);
     $options_arr['labels']['add_new'] = __($results[$i]->add_new_label);
     $options_arr['labels']['add_new_item'] = __($results[$i]->add_new_label);
     $options_arr['labels']['all_items'] = __($results[$i]->all_product_label);
     $options_arr['labels']['view_item'] = __($results[$i]->view_label);
     $options_arr['labels']['search_items'] = __($results[$i]->search_label);
     $options_arr['labels']['not_found'] = __('No found');
     $options_arr['labels']['not_found_in_trash'] = __('No found in Trash');
     $options_arr['labels']['parent_item_colon'] = '';
     $options_arr['labels']['menu_name'] = __($results[$i]->product_label);
     
     register_post_type($results[$i]->product_machine_name,$options_arr);
     
     add_filter('manage_'.$results[$i]->product_machine_name.'_posts_columns', array($this,'cck_columns_head'));  
     add_action('manage_'.$results[$i]->product_machine_name.'_posts_custom_column', array($this,'cck_columns_content'), 10, 2);
   }
 }
 
 function cck_columns_head($defaults) {
  global $wpdb;
  $post_type = $_REQUEST['post_type'];
  $content_type_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$post_type'");
  
  $sql = "select sctf.* from ".$wpdb->prefix."scck_content_type_fields sctf
  inner join ".$wpdb->prefix."scck_content_type_fields_detail sctfd on sctf.id = sctfd.field_id
  where sctf.content_type_id = '$content_type_id' and sctfd.attribute_name = 'show_in_list' and sctfd.	attribute_value = 1 
  ";
  $rows = $wpdb->get_results($sql);
  for($i = 0; $i < count($rows); ++$i) {
   $defaults[$rows[$i]->field_name] = $rows[$i]->field_label;
  }
  
  return $defaults;  
  
 }
 function cck_columns_content($column_name, $post_ID) {
  global $wpdb;
  $post_type = $_REQUEST['post_type'];
  $content_type_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$post_type'");
  
  $sql = "select sctf.* from ".$wpdb->prefix."scck_content_type_fields sctf
  inner join ".$wpdb->prefix."scck_content_type_fields_detail sctfd on sctf.id = sctfd.field_id
  where sctf.content_type_id = '$content_type_id' and sctfd.attribute_name = 'show_in_list' and sctfd.	attribute_value = 1 
  ";
  $rows = $wpdb->get_results($sql);
  for($i = 0; $i < count($rows); ++$i) {
   if($column_name == $rows[$i]->field_name) {
     $val = get_post_meta($post_ID,$rows[$i]->field_name,true);
     if($rows[$i]->field_type == 'image') {
      $images_arr = wp_get_attachment_image_src($val,'thumbnail');
       $image_src = $images_arr[0];
       print '<img src="'.$image_src.'" border="0">';
     }else {
      print $val;
     }
     
   }
  }
 }
 
 function build_meta_boxes($post) {
  global $wpdb,$post;
  $product_machine_name = $post->post_type;
  $sql = "select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$product_machine_name'";
  $content_type_id = $wpdb->get_var($sql);
  
  $group_rows = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields_group where content_type_id = '$content_type_id' order by weight asc");
  for($i =0 ; $i < count($group_rows); ++$i) {
     $group_id = $group_rows[$i]->id;
     $sql = " select count(*) from ".$wpdb->prefix."scck_content_type_fields ctf
     INNER JOIN  ".$wpdb->prefix."scck_content_type_fields_group_rel ctfgr
     ON ctfgr.field_id = ctf.id
     WHERE ctfgr.group_id = '$group_id '" ;
    $total_fields = $wpdb->get_var($sql);
   
     if($total_fields > 0) {
      $params['group_id'] = $group_rows[$i]->id;
      add_meta_box($group_rows[$i]->group_name.$group_rows[$i]->id,  __( $group_rows[$i]->group_title, 'scck_textdomain' ), array($this,'build_cck_metabox_detail'), $product_machine_name,'advanced','default',$params); 
     }
  }
 }
 
 function build_cck_metabox_detail($post,$params) {
   global $wpdb;
   print '<table  cellspacing="0" cellpadding="4" border="0">';
   $group_id = $params['args']['group_id'];
   
   $sql = " select ctf.* from ".$wpdb->prefix."scck_content_type_fields ctf
   INNER JOIN  ".$wpdb->prefix."scck_content_type_fields_group_rel ctfgr
   ON ctfgr.field_id = ctf.id
   WHERE ctfgr.group_id = '$group_id '  order by ctfgr.weight" ;
   
   $fields_row = $wpdb->get_results($sql);
   
   for($j = 0; $j < count($fields_row); ++$j) {
    
    if($fields_row[$j]->field_type == 'textfield') {
      print $this->build_text_field($fields_row[$j],$post);
    }
    
    if($fields_row[$j]->field_type == 'select') {
      print $this->build_select_field($fields_row[$j],$post);
    }
    
    if($fields_row[$j]->field_type == 'checkbox') {
      print $this->build_checkbox_field($fields_row[$j],$post);
    }
    
    
    if($fields_row[$j]->field_type == 'radio') {
      print $this->build_radio_field($fields_row[$j],$post);
    }
    if($fields_row[$j]->field_type == 'textarea') {
      print $this->build_textarea_field($fields_row[$j],$post);
    }
    
    if($fields_row[$j]->field_type == 'date') {
      print $this->build_date_field($fields_row[$j],$post);
    }
    
    if($fields_row[$j]->field_type == 'image') {
      print $this->build_image_field($fields_row[$j],$post);
    }    
    if($fields_row[$j]->field_type == 'file') {
      print $this->build_file_field($fields_row[$j],$post);
    }
    if($fields_row[$j]->field_type == 'user_reference') {
      print $this->build_user_reference_field($fields_row[$j],$post);
    }
    if($fields_row[$j]->field_type == 'content_reference') {
      print $this->build_content_reference_field($fields_row[$j],$post);
    }    
   }
   
   print '</table>';
 }
 
 function get_field_roles_users($roles_arr,$txt = '') {
  global $wpdb;
   $user_reference_roles = $this->get_field_setting_value('user_reference_roles',$field_id,'tx');
   $user_reference_roles_arr = explode(",",$user_reference_roles);
   
   $role_sql = array();
   foreach($user_reference_roles_arr as $r) {
    $role_sql[] = " (select * from ".$wpdb->prefix."users u
    inner join ".$wpdb->prefix."usermeta um   on u.ID = um.user_id
    where (meta_key = 'wp_capabilities' and meta_value like '%$r%') and u.user_login like  '%$field_value%') ";
   }
   
   $sql = implode(" union ",$role_sql);
   $rows = $wpdb->get_results($sql);
  
 }
 
 function search_content_reference_suggation() {
   global $wpdb;
   $field_id = $_POST['field_id'];
   $field_value = $_POST['field_value'];
   $field_name = $_POST['field_name'];
   
   $content_reference_post_type = $this->get_field_setting_value('content_reference_post_type',$field_id,'tx');
   $content_reference_post_type_arr = explode(",",$content_reference_post_type);
   
   $sql = "select ID,post_title from ".$wpdb->prefix."posts where post_title like '%$field_value%' and  post_type in ('".implode("','",$content_reference_post_type_arr)."') and post_status = 'publish'";
   $rows = $wpdb->get_results($sql);
   $rows = $wpdb->get_results($sql);
    
    ?>
     <style>
    #feedback { font-size: 1.4em; }
    #selectable-<?php print $field_id; ?> .ui-selecting { background: #FECA40; }
    #selectable-<?php print $field_id; ?> .ui-selected { background: #F39814; color: white; }
    #selectable-<?php print $field_id; ?> { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #selectable-<?php print $field_id; ?> li { margin: 3px; padding: 0.4em; font-size: 1.4em; height: 18px; }
</style>
    <script>
     jQuery(function() {
     jQuery( "#selectable-<?php print $field_id; ?>" ).selectable({
        selected: function( event, ui ) {
         var id = ui.selected.id;
         jQuery("#<?php print $field_name; ?>-<?php print $field_id; ?>").attr("value",ui.selected.innerHTML);
         jQuery("#<?php print $field_name; ?>-<?php print $field_id; ?>-id").attr("value",ui.selected.id);
         jQuery( "#selectable-<?php print $field_id; ?>" ).hide();
        }
     });
     });
</script>
    <?php
    print '<ol id="selectable-'.$field_id.'">';
    
    for($i = 0; $i < count($rows); ++$i ) {
     print '<li class="ui-widget-content" id="'.$rows[$i]->ID.'">'.$rows[$i]->post_title.'</li>';
     	
    }
    print '</ol>';
   
   die(0);
 }
 
 function search_user_reference_suggation() {
   global $wpdb;
   $field_id = $_POST['field_id'];
   $field_value = $_POST['field_value'];
   $field_name = $_POST['field_name'];
   
   $user_reference_roles = $this->get_field_setting_value('user_reference_roles',$field_id,'tx');
   $user_reference_roles_arr = explode(",",$user_reference_roles);

    $role_sql = array();
    foreach($user_reference_roles_arr as $r) {
     $role_sql[] = " (select * from ".$wpdb->prefix."users u
     inner join ".$wpdb->prefix."usermeta um   on u.ID = um.user_id
      where (meta_key = 'wp_capabilities' and meta_value like '%$r%') and u.user_login like  '%$field_value%') ";
    }
    $sql = implode(" union ",$role_sql);
    $rows = $wpdb->get_results($sql);
    ?>
     <style>
    #feedback { font-size: 1.4em; }
    #selectable-<?php print $field_id; ?> .ui-selecting { background: #FECA40; }
    #selectable-<?php print $field_id; ?> .ui-selected { background: #F39814; color: white; }
    #selectable-<?php print $field_id; ?> { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #selectable-<?php print $field_id; ?> li { margin: 3px; padding: 0.4em; font-size: 1.4em; height: 18px; }
</style>
    <script>
     jQuery(function() {
     jQuery( "#selectable-<?php print $field_id; ?>" ).selectable({
        selected: function( event, ui ) {
         var id = ui.selected.id;
         jQuery("#<?php print $field_name; ?>-<?php print $field_id; ?>").attr("value",ui.selected.innerHTML);
         jQuery("#<?php print $field_name; ?>-<?php print $field_id; ?>-id").attr("value",ui.selected.id);
         jQuery( "#selectable-<?php print $field_id; ?>" ).hide();
        }
     });
     });
</script>
    <?php
    print '<ol id="selectable-'.$field_id.'">';
    
    for($i = 0; $i < count($rows); ++$i ) {
     print '<li class="ui-widget-content" id="'.$rows[$i]->ID.'">'.$rows[$i]->user_login.'</li>';
     	
    }
    print '</ol>';
   
   die(0);
 }

 function build_content_reference_field($row,$post) {
  global $wpdb;
  $field_info = $this->settings_fields($row->field_type);
  $field_id = $row->id;
  $content_reference_input_type = $this->get_field_setting_value('content_reference_input_type',$field_id,'vc');
  
  ?>
   <script>
   jQuery(function() {
    jQuery( ".content_refernce_ac-<?php print $field_id;?>" ).keypress(function() {
     var field_id  = jQuery(this).attr("rel");
     var field_value  = jQuery(this).attr("value");
     if(field_value.length <  4) {
										return;
					}
     jQuery.post('admin-ajax.php', {field_name:'<?php print $row->field_name;?>',field_id:field_id,field_value:field_value,action:'search_content_reference_suggation'},
       function(response) {
         jQuery("#content_ref_search_suggation_<?php print $field_id;?>").html(response);
        }
     );  
     
    });
   });
  </script>
   <?php
  $post_id = get_post_meta($post->ID,$row->field_name,true);
  $related_post = get_post($post_id);
  
  if($content_reference_input_type == 'autocomplete') {
   $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
   $html .= '<td><input value="'.$related_post->post_title.'" type="text" id="'.$row->field_name.'-'.$field_id.'" class="widefat content_refernce_ac-'.$field_id.'" rel="'.$field_id.'" size="50" placeholder="Type at least five charecter to initiate search" title="Type at least five charecter to search"/><div class="widget_tp" id="content_ref_search_suggation_'.$field_id.'"></div></td></tr>';
   $html .= '<input type="hidden" value="'.$post_id.'" name="'.$row->field_name.'" id="'.$row->field_name.'-'.$field_id.'-id" />';
  }
  
  if($content_reference_input_type == 'select_list') {
   
   $content_reference_post_type = $this->get_field_setting_value('content_reference_post_type',$field_id,'tx');
   $content_reference_post_type_arr = explode(",",$content_reference_post_type);
   
    $sql = "select ID, post_title from ".$wpdb->prefix."posts where post_type in ('".implode("','",$content_reference_post_type_arr)."') and post_status = 'publish'";
   $rows = $wpdb->get_results($sql);
   for($k = 0; $k < count($rows); ++$k) {
    if($post_id == $rows[$k]->ID) {
     $opts .= '<option value="'.$rows[$k]->ID.'" selected>'.$rows[$k]->post_title.'</option>'; 
    }else {
     $opts .= '<option value="'.$rows[$k]->ID.'">'.$rows[$k]->post_title.'</option>'; 
    }
    
   }
  
   $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
   $html .= '<td><select  id="'.$row->field_name.'-'.$field_id.'" name="'.$row->field_name.'"/>';
   $html .=  $opts;
   $html .= '</select></td></tr>';
  }
  
  return $html;
 }
 
 function build_user_reference_field($row,$post) {
  global $wpdb;
  $field_info = $this->settings_fields($row->field_type);
  $field_id = $row->id;
  $user_reference_input_type = $this->get_field_setting_value('user_reference_input_type',$field_id,'vc');
  
  ?>
   <script>
   jQuery(function() {
    jQuery( ".user_refernce_ac-<?php print $field_id;?>" ).keypress(function() {
     var field_id  = jQuery(this).attr("rel");
     var field_value  = jQuery(this).attr("value");
     if(field_value.length < 4) {
										return;
					}
     jQuery.post('admin-ajax.php', {field_name:'<?php print $row->field_name;?>',field_id:field_id,field_value:field_value,action:'search_user_reference_suggation'},
       function(response) {
         jQuery("#user_ref_search_suggation_<?php print $field_id;?>").html(response);
        }
     );  
     
    });
   });
  </script>
   <?php
  $user_id = get_post_meta($post->ID,$row->field_name,true);
  $user = get_userdata( $user_id );
  
  if($user_reference_input_type == 'autocomplete') {
   $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
   $html .= '<td><input value="'.$user->data->user_login.'" type="text" id="'.$row->field_name.'-'.$field_id.'" class="widefat user_refernce_ac-'.$field_id.'" rel="'.$field_id.'" size="50" placeholder="Type at least five charecter to initiate search" title="Type at least five charecter to search"/><div class="widget_tp" id="user_ref_search_suggation_'.$field_id.'"></div></td></tr>';
   $html .= '<input type="hidden" value="'.$user->data->ID.'" name="'.$row->field_name.'" id="'.$row->field_name.'-'.$field_id.'-id" />';
  }
  
  if($user_reference_input_type == 'select_list') {
   
   $user_reference_roles = $this->get_field_setting_value('user_reference_roles',$field_id,'tx');
   $user_reference_roles_arr = explode(",",$user_reference_roles);
   
   $role_sql = array();
   foreach($user_reference_roles_arr as $r) {
   $role_sql[] = " (select * from ".$wpdb->prefix."users u
   inner join ".$wpdb->prefix."usermeta um   on u.ID = um.user_id
   where (meta_key = 'wp_capabilities' and meta_value like '%$r%')) ";
   }
   
   $sql = implode(" union ",$role_sql);
   $rows = $wpdb->get_results($sql);
   for($k = 0; $k < count($rows); ++$k) {
    if($user_id == $rows[$k]->ID) {
     $opts .= '<option value="'.$rows[$k]->ID.'" selected>'.$rows[$k]->user_login.'</option>'; 
    }else {
     $opts .= '<option value="'.$rows[$k]->ID.'">'.$rows[$k]->user_login.'</option>'; 
    }
    
   }
  
   $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
   $html .= '<td><select  id="'.$row->field_name.'-'.$field_id.'" name="'.$row->field_name.'"/>';
   $html .=  $opts;
   $html .= '</select></td></tr>';
  }
  
  return $html;
 }
 
 function build_file_field($row,$post) {
  global $wpdb;
  $field_info = $this->settings_fields($row->field_type);
  $field_id = $row->id;
  $attach_id = get_post_meta($post->ID,$row->field_name,true);
  $file_url = wp_get_attachment_url($attach_id );
  $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
  $html .= '<td><input type="file" name="'.$row->field_name.'" id="'.$row->field_name.'-'.$field_id.'" value="" size="50" ></td></tr>';
  $html .= '<tr><td></td><td><a href="'.$file_url.'" target="new">'.$file_url.'</a></td></tr>';
  
  return $html;
 }
 
 function build_image_field($row,$post) {
  global $wpdb;
  $field_id = $row->id;
  $field_info = $this->settings_fields($row->field_type);
  $attach_id = get_post_meta($post->ID,$row->field_name,true);
  $attached_post = get_post($attach_id);

  $images_arr = wp_get_attachment_image_src($attach_id,'large');
  
  $image_alt = $this->get_field_setting_value('image_alt',$field_id,'vc');
  $image = $images_arr[0];
  
  
  $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
  $html .= '<td><input type="file" name="'.$row->field_name.'" id="'.$row->field_name.'-'.$field_id.'" value="" size="50" ></td></tr>';
  if($image_alt == 'yes') {
   $html .= '<tr><td><label for="'.$row->field_name.'-'.$field_id.'-alt">'.$row->field_label.' Alt Tag</label></td>';
   $html .= '<td><input type="text" name="'.$row->field_name.'_alt" id="'.$row->field_name.'-'.$field_id.'-alt" value="'.$attached_post->post_title.'" size="50" ></td></tr>';
  }
  $html .= '<tr><td></td><td><img src="'.$image.'"></td></tr>';
  
  return $html;
 }
 
 function build_date_field($row,$post) {
  global $wpdb;
  $field_id = $row->id;
  $value = get_post_meta($post->ID,$row->field_name,true);
  $field_info = $this->settings_fields($row->field_type);
  $date_format = $this->get_field_setting_value('date_format',$field_id,'vc');
  ?>
   <script>
   jQuery(function() {
    jQuery( "#<?php print $row->field_name.'-'.$field_id;?>" ).datepicker({ dateFormat: "<?php print $date_format;?>" });
   });
  </script>
  <?php
  
  $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
  $html .= '<td><input type="text" name="'.$row->field_name.'" id="'.$row->field_name.'-'.$field_id.'" value="'.$value.'" ></td></tr>';
  return $html;
  
  
 }
 
 function get_field_setting_value($name,$field_id,$type = 'vc') {
  global $wpdb;
  if($type == 'vc') {
   $value =  $wpdb->get_var("select attribute_value from ".$wpdb->prefix."scck_content_type_fields_detail where attribute_name = '$name' and field_id 	= '$field_id'");
  }else {
   $value =  $wpdb->get_var("select attribute_value from ".$wpdb->prefix."scck_content_type_fields_detail_tx where attribute_name = '$name' and field_id 	= '$field_id'");
  }
  return $value;
 }
 
 
 function build_textarea_field($row,$post) {
  global $wpdb;
  $field_id = $row->id;
  $field_info = $this->settings_fields($row->field_type);
  $textarea_htmleditor = $this->get_field_setting_value('textarea_htmleditor',$field_id,'vc');
  
  $textarea_row = $this->get_field_setting_value('textarea_row',$field_id,'vc');
  $textarea_col = $this->get_field_setting_value('textarea_col',$field_id,'vc');
  $html = '<tr><td><label for="'.$row->field_name.'">'.$row->field_label.'</label></td>';
  $html .= '<td>';
  $content = get_post_meta($post->ID,$row->field_name,true);
  if($textarea_htmleditor == 'no') {
  
   $html .= '<textarea  class="wp-editor-area" name="'.$row->field_name.'" id="'.$row->field_name.'-'.$field_id.'" rows="'.$textarea_row.'" cols="'.$textarea_col.'" >'.$content.'</textarea>'; 
  }else {
   $settings['wpautop'] = false;
   $settings['media_buttons'] = false;
   $settings['textarea_rows'] = $textarea_row;
   ob_start();
   wp_editor( $content, $row->field_name, $settings );
   $theid = ob_get_clean();
   ob_end_flush();
   $html .= $theid;
  }
  $html .= '</td></tr>';
  return $html;
 }
 
  function build_select_opt($array,$sel = '',$blank = 0) {
  $opt = array();
  if($blank == 1) {
   $opt[] = '<option value="">Select</option>'; 
  }
  
  foreach($array as $k => $v) {
   if($sel == $k) {
    $opt[] = '<option value="'.$k.'" selected="selected">'.$v.'</option>'; 
   }else {
    $opt[] = '<option value="'.$k.'">'.$v.'</option>'; 
   }
   
  }
  return implode("",$opt);
 }
 
 
 function build_checkbox_field($row,$post) {
   global $wpdb;
   $field_info = $this->settings_fields($row->field_type);
   $field_id = $row->id;
   $value = get_post_meta($post->ID,$row->field_name,true);
   $value_arr = explode(",",$value);
   
      
   $field_options = $wpdb->get_var("select attribute_value from ".$wpdb->prefix."scck_content_type_fields_detail_tx where field_id = '$field_id' and attribute_name = 'field_options'");
   $opt_arr = $this->pipe_opt_array($field_options);
   
   $html = '<tr><td>'.$row->field_label.'</td><td>';
   foreach($opt_arr as $k => $v) {
    $fieldname = $row->field_name;
    if(in_array($k,$value_arr)) {
     $html .= '<input type="checkbox" name="'.$fieldname.'[]" value="'.$k.'" id="'.$fieldname.$k.'" checked><label for="'.$fieldname.$k.'">&nbsp;&nbsp;'.$v.'</label><br/>';     
    }else {
     $html .= '<input type="checkbox" name="'.$fieldname.'[]" value="'.$k.'" id="'.$fieldname.$k.'"><label for="'.$fieldname.$k.'">&nbsp;&nbsp;'.$v.'</label><br/>';     
    }
    
   }
   $html .= '</td></tr>';
   
   return $html;
 }
 
  function build_radio_field($row,$post) {
   global $wpdb;
   $field_info = $this->settings_fields($row->field_type);
   $field_id = $row->id;
   
   $value = get_post_meta($post->ID,$row->field_name,true);
   
   $field_options = $wpdb->get_var("select attribute_value from ".$wpdb->prefix."scck_content_type_fields_detail_tx where field_id = '$field_id' and attribute_name = 'field_options'");
   $opt_arr = $this->pipe_opt_array($field_options);
   
   $html = '<tr><td>'.$row->field_label.'</td><td>';
   foreach($opt_arr as $k => $v) {
    $fieldname = $row->field_name;
    if($value === $k) {
     $html .= '<input type="radio" name="'.$fieldname.'" value="'.$k.'" id="'.$fieldname.$k.'" checked><label for="'.$fieldname.$k.'">&nbsp;&nbsp;'.$v.'</label><br/>';     
    }else {
     $html .= '<input type="radio" name="'.$fieldname.'" value="'.$k.'" id="'.$fieldname.$k.'"><label for="'.$fieldname.$k.'">&nbsp;&nbsp;'.$v.'</label><br/>';     
    }
    
   }
   $html .= '</td></tr>';
   
   return $html;
 }
 
 function build_select_field($row,$post) {
  global $wpdb;
  
  
  
  $field_info = $this->settings_fields($row->field_type);
  $field_id = $row->id;
  
  $select_height = $this->get_field_setting_value('select_height',$field_id,'vc');
  
  $value = get_post_meta($post->ID,$row->field_name,true);
  $elem  = '<select ';
  $elem  .= ' name="'.$row->field_name.'" ';
  $elem  .= ' id="'.$row->field_name.'-'.$field_id.'" ';
  
  $field_multiselect = $wpdb->get_var("select attribute_value from ".$wpdb->prefix."scck_content_type_fields_detail where field_id = '$field_id' and attribute_name = 'field_multiselect'");
  
  $field_first_row_black = $wpdb->get_var("select attribute_value from ".$wpdb->prefix."scck_content_type_fields_detail where field_id = '$field_id' and attribute_name = 'field_first_row_black'");
  
  $field_options = $wpdb->get_var("select attribute_value from ".$wpdb->prefix."scck_content_type_fields_detail_tx where field_id = '$field_id' and attribute_name = 'field_options'");
  
  if($field_multiselect == 'yes') {
   $elem .= ' multiselect="multiselect" ';
   if($select_height > 0) {
    $elem .= ' height="".$select_height."" ';
   }
  }
  $elem .= ' > ';
  
  $html = '<tr><td><label for="'.$row->field_name.'">'.$row->field_label.'</label></td>';
  $html .= '<td>'.$elem.$this->build_select_opt($this->pipe_opt_array($field_options),$value,1);
  $html .= '</select></td></tr>';
  return $html;
 }
 
 function pipe_opt_array($options) {
   $options_arr = explode(chr(13),$options);
   $array = array();
   for($i = 0; $i < count($options_arr); ++$i) {
    list($k,$v) = explode("|",$options_arr[$i]);
    $array[trim($k)] = trim($v);
   }
   return $array;
 }
 
 function build_text_field($row,$post) {
  global $wpdb;
  $field_info = $this->settings_fields($row->field_type);
  $field_id = $row->id;
  $attr = '';
  $textfield_size = $this->get_field_setting_value('textfield_size',$field_id,'vc');
  if($textfield_size > 0) {
   $attr .= ' size = "'.$textfield_size.'" ';
  }else {
   $attr .= ' size = 50 ';
  }
  $textfield_max_length = $this->get_field_setting_value('textfield_max_length',$field_id,'vc');
  if($textfield_max_length > 0) {
   $attr .= ' maxlength = "'.$textfield_max_length.'" ';
  }
  
  $value = get_post_meta($post->ID,$row->field_name,true);
  $html = '<tr><td><label for="'.$row->field_name.'-'.$field_id.'">'.$row->field_label.'</label></td>';
  $html .= '<td><input type="text" name="'.$row->field_name.'" id="'.$row->field_name.'-'.$field_id.'" value="'.$value.'" '.$attr.'></td></tr>';
  return $html;
 }
 
 function create_taxomony() {
  global $wpdb;
  
  $taxonomies_arr = $wpdb->get_results("select ctf.*,sct.product_machine_name from ".$wpdb->prefix."scck_content_type_fields ctf
                                       INNER JOIN ".$wpdb->prefix."scck_content_type  sct
                                       on sct.id = ctf.content_type_id 
                                       where field_type = 'taxonomy' and sct.enable = 1 " );
  
  
  for($i = 0; $i < count($taxonomies_arr); ++$i ) {
   $field_id = $taxonomies_arr[$i]->id;
   $field_name = $taxonomies_arr[$i]->field_name;
   $product_machine_name = $taxonomies_arr[$i]->product_machine_name;
   $field_detail = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields_detail where field_id = '$field_id'");
   for($j = 0; $j < count($field_detail); ++$j) {
    $attribute_name = $field_detail[$j]->attribute_name;
    $$attribute_name = $field_detail[$j]->attribute_value;
   }
    
    $labels = array(
     'name'                => _x( $name, 'taxonomy general name' ),
     'singular_name'       => _x( $singular_name, 'taxonomy singular name' ),
     'search_items'        => __( $search_items),
     'all_items'           => __( $all_items ),
     'parent_item'         => __( $parent_item ),
     'parent_item_colon'   => __( $parent_item_colon ),
     'edit_item'           => __( $edit_item), 
     'update_item'         => __( $update_item ),
     'add_new_item'        => __( $add_new_item),
     'new_item_name'       => __( $new_item_name ),
     'menu_name'           => __( $menu_name )
   ); 	
 
   $args = array(
     'hierarchical'        => $hierarchical,
     'labels'              => $labels,
     'show_ui'             => $show_ui,
     'show_admin_column'   => $show_admin_column,
     'query_var'           => $query_var,
     'rewrite'             => array( 'slug' => $field_name )
   );
   
   register_taxonomy( $field_name, array( $product_machine_name ), $args );                             
  }
  
 }
 
 function save_post_field($post_id,$post) {
  global $wpdb;
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )   return;
  if ( !current_user_can( 'edit_post', $post_id ) )      return;
  //if ( !wp_verify_nonce( $_POST['prettyurl_noncename'], plugin_basename( __FILE__ ) ) )    return;
  if ( wp_is_post_revision( $post_id ) ) return;
  
  $content_type_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$post->post_type'");
  
  $fields_row = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields where content_type_id = '$content_type_id' ");
  
  for($i = 0; $i < count($fields_row); ++$i) {
    if($fields_row[$i]->field_type != 'image' and $fields_row[$i]->field_type != 'file' && $fields_row[$i]->field_type != 'taxonomy') {
     if(is_array($_POST[$fields_row[$i]->field_name])) {
      $value = implode(",",$_POST[$fields_row[$i]->field_name]);
     }else {
      $value = trim($_POST[$fields_row[$i]->field_name]);
     }
     update_post_meta($post_id,$fields_row[$i]->field_name,$value);
    }
    
    if($fields_row[$i]->field_type == 'image') {
     $this->upload_image($fields_row[$i],$attach_id,$post_id);
    } 
    if($fields_row[$i]->field_type == 'file') {
     $this->upload_file($fields_row[$i],$attach_id,$post_id);
    }
    
  }
 }
 
 function change_upload_path($array) {
  global $post,$field_id;
  
  $location = trim($this->get_field_setting_value('location',$field_id,'vc'));
  if($location != '') {
   $basedir = $array['basedir'];
   $baseurl = $array['baseurl'];
   $array['path'] = $basedir."/".$location;
   $array['url'] = $baseurl."/".$location;
   $array['subdir'] = "/".$location;
  }
  return $array;
 }
 
  function upload_file($row,$filename_id = 0,$post_id = 0) {
  global $wpdb,$field_id;
  $field_id = $row->id;
  $location = trim($this->get_field_setting_value('location',$field_id,'vc'));
  $filename = $row->field_name;
   
  if(!empty($_FILES[$filename])) {
				$file   = $_FILES[$filename];
    
				if($location != '') {
      add_filter('upload_dir',array(&$this,'change_upload_path'),1);
    }
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    remove_filter('upload_dir',array(&$this,'change_upload_path'),1,1);
    
				if(!isset($upload['error']) && isset($upload['file'])) {
								if($_POST[$filename_id] > 0) {
									 wp_delete_attachment( $_POST[$filename_id], true);
								}
								$wp_filetype = wp_check_filetype(basename($upload['file']), null );
								$wp_upload_dir = wp_upload_dir();
									$main_image_alt = $_POST['main_image_alt'];
								 if($main_image_alt) {
									 $title = $main_image_alt;
								 }else {
									 $title = preg_replace('/\.[^.]+$/', '', basename($upload['file']));
								 }
									
								$attachment = array(
											'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $upload['file'] ), 
											'post_mime_type' => $wp_filetype['type'],
											'post_title' => $title,
											'post_content' => $title,
											'post_status' => 'inherit'
								);
								$attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
        update_post_meta($post_id,$row->field_name,$attach_id);
								require_once(ABSPATH . 'wp-admin/includes/image.php');
				}
	 }
 }
 
 function upload_image($row,$filename_id = 0,$post_id = 0) {
  global $wpdb,$field_id;
  $field_id = $row->id;
  $resize = $this->get_field_setting_value('resize',$field_id,'vc');
  $image_width = $this->get_field_setting_value('width',$field_id,'vc');
  $image_height = $this->get_field_setting_value('height',$field_id,'vc');
  $location = trim($this->get_field_setting_value('location',$field_id,'vc'));
  $create_all_resized = trim($this->get_field_setting_value('create_all_resized',$field_id,'vc'));
  $filename = $row->field_name;
  $alt_tag = $row->field_name.'_alt';
  $image_alttag = trim($_POST[$alt_tag]);
   
  $prev_attach_id = get_post_meta($post_id,$row->field_name,true);
  if($prev_attach_id && $image_alttag != '') {
   $wpdb->query("update ".$wpdb->prefix."posts set post_title = '$image_alttag', post_content = '$image_alttag' where ID = '$prev_attach_id'"); 
  }
  
  
  if(!empty($_FILES[$filename])) {
				$file   = $_FILES[$filename];
    
				if($location != '') {
      add_filter('upload_dir',array(&$this,'change_upload_path'),1);
    }
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    remove_filter('upload_dir',array(&$this,'change_upload_path'),1,1);
    
				if(!isset($upload['error']) && isset($upload['file'])) {
        
								if($prev_attach_id > 0) {
									 wp_delete_attachment( $prev_attach_id, true);
								}
        
								$wp_filetype = wp_check_filetype(basename($upload['file']), null );
								$wp_upload_dir = wp_upload_dir();
							 $title = preg_replace('/\.[^.]+$/', '', basename($upload['file']));
         
								$alt_tag = trim($_POST[$alt_tag]);
        if($alt_tag) {
         $title = $alt_tag;
        }
								$attachment = array(
											'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $upload['file'] ), 
											'post_mime_type' => $wp_filetype['type'],
											'post_title' => $title,
											'post_content' => $title,
											'post_status' => 'inherit'
								);
								$attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
        if($attach_id > 0) {
         update_post_meta($post_id,$row->field_name,$attach_id);

        }
        
								require_once(ABSPATH . 'wp-admin/includes/image.php');
        if($resize == 'yes') {
         $image = wp_get_image_editor( $upload['file']);
         if ( ! is_wp_error( $image ) ) {
            $image->resize( $image_width, $image_height, true );
            $image->save( $upload['file'] );
         }
        }
        if($create_all_resized == 1 && $attach_id > 0) {
          $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
          wp_update_attachment_metadata( $attach_id, $attach_data );				
        }
        //if($option_image_attach_id > 0) {
          //wp_delete_attachment( $option_image_attach_id, true); 
        //}
        
				}
	 }
 }
 
}
