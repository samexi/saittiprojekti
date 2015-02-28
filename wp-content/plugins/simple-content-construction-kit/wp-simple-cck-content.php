<?php


class wp_simple_ckk_content extends simple_cck_fields {
 
 public $content_type_id = 0;
 
 function scck_products_type () {
  global $wpdb;
  $act = $_REQUEST['act'];
  $this->content_type_id = $_REQUEST['content_type_id'];
  
  switch($act) {
   case 'add-type':
    $this->scck_product_type_add();
   break;
   case 'attr':
    $this->scck_product_fields();
   break; 
  default:
   print '<div id="cck_list">';
    $this->scck_products_type_list();
    print '</div>';
  break;
  }
  
 }
 
 
 function scck_product_type_save() {
  global $wpdb;
  $fields = $this->scck_product_field();
  $data = array();
  foreach($fields as $field) {
   if(is_array($_POST[$field])) {
     $$field = implode(",",$_POST[$field]);
   }else {
    $$field = trim($_POST[$field]); 
   }
   
   if(is_array($$field)) {
    $data[] = " $field = '".implode(",",$$field)."'"; 
   }else {
    $data[] = " $field = '".$$field."'"; 
   }
  }
  $content_type_id = $_POST['content_type_id'];
  if($content_type_id > 0) {
   $sql = "update ".$wpdb->prefix."scck_content_type set ".implode(",",$data)." where id = '$content_type_id'";
   $wpdb->query($sql);
  }else {
    $sql = "insert into  ".$wpdb->prefix."scck_content_type set ".implode(",",$data);
    $wpdb->query($sql);
    $this->content_type_id = $this->get_ccid($product_machine_name);
    $this->create_default_group();
  }
 }
 
 function get_ccid($product_machine_name) {
  global $wpdb;
  return $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$product_machine_name'");
 }
 
 
 function create_default_group($group_name = 'default',$group_title = 'More Detail') {
  global $wpdb;
   $data[] = "content_type_id = '".$this->content_type_id."'";
   $data[] = "group_name  = '$group_name'";
   $data[] = "group_title = '$group_title'";
   $sql = "insert into  ".$wpdb->prefix."scck_content_type_fields_group set ".implode(",",$data);
   $wpdb->query($sql);
 }
 
 function build_content_order() {
  global $wpdb;
  $order = $_POST['order'];
  $order_arr = explode(",",$order);
  $menu_id = $this->get_default_menu();
  $default_menu_id = $this->get_default_menu(); // for checking, dont remove
  for($i = 0; $i < count($order_arr); ++$i) {
   list($type,$id) = explode("_",$order_arr[$i]);
   if($type == 'menu') {
    ++$menu_weight;
    $wpdb->query("update ".$wpdb->prefix."scck_menu set weight = '$menu_weight' where id = '$id'");
    $menu_id = $id;
   }
   if($type == 'cck') {
    ++$cck_weight;
    if($default_menu_id != $menu_id) { // then make show show_in_menu to false
      $wpdb->query("update ".$wpdb->prefix."scck_content_type set show_in_menu = 0 , menu_id = '$menu_id', weight = '$cck_weight' where id = '$id'");
    }else {
      $wpdb->query("update ".$wpdb->prefix."scck_content_type set show_in_menu = 1 ,menu_id = '$menu_id', weight = '$cck_weight' where id = '$id'"); 
    }
    
   }
  }
  
  $this->scck_products_type_list($error);
  die(0);
 }
 
 
 function scck_product_type_add() {
  global $wpdb;
  ?>
  <a href="<?php print $this->build_url();?>">Back</a>
  <div class="wrap">
  <h2>Modify Content Type</h2>
  <?php
  $content_type_id = $_REQUEST['content_type_id'];
  if($_POST['form_submit'] == 'form_submit') {
    $fields = $this->scck_product_field();
    foreach($fields as $field) {
     $$field = trim($_POST[$field]);
    }
    if($product_machine_name == "") {
     $err  = 1;
     print '<div class="error">Please Enter product machine Name</div>';
    }
    if($product_label == "") {
     $err  = 1;
     print '<div class="error">Please Enter product label</div>';
    }
    $id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$product_machine_name'");
    if($product_machine_name != "" && $content_type_id == 0) {
     if($id > 0) {
      $err  = 1;
      print '<div class="error">Duplicate machine name</div>'; 
     }
    }
     if($content_type_id > 0) { //check when edit
      if($id != $content_type_id && $content_type_id > 0)  {
       $err  = 1;
       print '<div class="error">Duplicate machine name</div>'; 
      }
     }
    
    if($err == 0) {
      $this->scck_product_type_save();
      $this->redirect($this->build_url());
      exit;
     }
  }
  
  ?>
  
  <?php
  if($content_type_id > 0) {
   
   $sql = "select * from ".$wpdb->prefix."scck_content_type where id = '$content_type_id'";
   $row = $wpdb->get_row($sql);
   
   $arr = array();
   $fields = $this->scck_product_field();
   
   foreach ($fields as $field) {
     $$field = $row->$field;
   }
   
   
  }
  
  
  ?>
  <form method="post" enctype="multipart/form-data">
   <table width="100%" cellpadding="3" cellspacing="0" border="0">
    <tr>
     <td>Enter Machine Name:</td>
     <td><input type="text" name="product_machine_name" value="<?php print $product_machine_name;?>" size="40">
     <br><em>Enter machine name of product like: mobile</em>
     </td>
    </tr>
     <tr>
     <td>Content Label:</td>
     <td><input type="text" name="product_label" value="<?php print $product_label;?>" size="40">
     <br><em>Enter Label of product like: Mobile</em>
     </td>
    </tr>
     <tr>
     <td colspan="2"><u><strong>Enter Variouse Lables For Content</strong></u></td>
    </tr>
    <tr>
     <td>Add New Label:</td>
     <td><input type="text" name="add_new_label" value="<?php print $add_new_label;?>" size="40">
     </td>
    </tr>
    <tr>
     <td>Edit Label:</td>
     <td><input type="text" name="edit_label" value="<?php print $edit_label;?>" size="40">
     </td>
    </tr>
    <tr>
     <td>All Content Label:</td>
     <td><input type="text" name="all_product_label" value="<?php print $all_product_label;?>" size="40">
     </td>
    </tr>
    <tr>
     <td>View Content Label:</td>
     <td><input type="text" name="view_label" value="<?php print $view_label;?>" size="40">
     </td>
    </tr>
    <tr>
     <td>Search Content Label:</td>
     <td><input type="text" name="search_label" value="<?php print $search_label;?>" size="40">
     </td>
    </tr>
    <tr>
     <td>Menu Label:</td>
     <td><input type="text" name="menu_label" value="<?php print $menu_label;?>" size="40">
     </td>
    </tr>
     <tr>
     <td colspan="2"><u><strong>Default Fields</strong></u></td>
    </tr>
     <?php
    $default_fields = array('title' => 'Title'
    ,'editor' => 'Editor'
    ,'author' => 'Author'
    ,'thumbnail' => 'Thumbnail'
    ,'excerpt' => 'Excerpt'
    ,'comments' => 'Comments'
    ,'revisions' => 'Revisions'
    );
    $supports_arr = explode(",",$supports);
    foreach($default_fields  as $dfk => $dff) {
     $checked = ' ';
     if(in_array($dfk,$supports_arr)) {
      $checked = ' checked ';
     }
     ?>
     <tr>
     <td><?php print $dff;?>:</td>
     <td><input type="checkbox" name="supports[]" value="<?php print $dfk;?>" <?php print $checked;?>>
     </td>
    </tr>
     <?php
    }
    $checked = '';
    if($category == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Category:</td>
     <td><input type="checkbox" name="category" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
    <?php
    $checked = '';
    if($post_tag == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Post Tag:</td>
     <td><input type="checkbox" name="post_tag" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
    <tr>
     <td colspan="2"><u><strong>Content Settings</strong></u></td>
    </tr>
    <?php
    $checked = '';
    if($product_public == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Public:</td>
     <td><input type="checkbox" name="product_public" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
    <?php
    $checked = '';
    if($has_archive == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Has Archive:</td>
     <td><input type="checkbox" name="has_archive" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
  <?php
    $checked = '';
    if($publicly_queryable == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Publicly Queryable:</td>
     <td><input type="checkbox" name="publicly_queryable" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
 <?php
    $checked = '';
    if($exclude_from_search == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Exclude From Search:</td>
     <td><input type="checkbox" name="exclude_from_search" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
 <?php
    $checked = '';
    if($show_ui == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Show ui:</td>
     <td><input type="checkbox" name="show_ui" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
 <?php
    $checked = '';
    if($show_in_menu == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
     <td>Show In Menu:</td>
     <td><input type="checkbox" name="show_in_menu" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
     <?php
    $checked = '';
    if($enable == 1) {
     $checked = 'checked'; 
    }
    ?>
    <tr>
      <td>Enable:</td>
     <td><input type="checkbox" name="enable" value="1" size="40" <?php print $checked;?>>
     </td>
    </tr>
     <tr>
     <td colspan="2"><u><strong>Meta Data</strong></u></td>
    </tr>
    
    <tr>
     <td>Meta Title:</td>
     <td><input type="text" name="meta_title" value="<?php print $meta_title; ?>" size="60">
     </td>
    </tr>
    <tr>
     <td>Meta Description:</td>
     <td><textarea name="meta_desc" rows="3" cols="60"><?php print $meta_desc; ?></textarea>
     </td>
    </tr>
        <tr>
     <td>Meta Keyword:</td>
     <td><textarea name="meta_keyword" rows="3" cols="60"><?php print $meta_keyword; ?></textarea>
     </td>
    </tr>
   </table>
 <br />
 <input type="hidden" name="content_type_id" value="<?php print $content_type_id; ?>"> 
 <input type="hidden" value="form_submit" name="form_submit"> 
  <input type="submit" value="Submit" name="submit" class="button button-primary"> 
  </form>
  <?php
 }
 
 function cck_jquery() {
  ?>
   <script>
   jQuery(function() {
    
    jQuery(".create_cc").click(function() {
      var product_machine_name = jQuery("#product_machine_name").attr("value");
      var product_label = jQuery("#product_label").attr("value");
      
      jQuery.post('admin-ajax.php', {product_machine_name:product_machine_name,product_label:product_label,action:'add_cck'},
       function(response) {
         jQuery("#cck_list").html(response);
        }
      );
    });
    
    jQuery(".create_menu").click(function() {
      var product_menu = jQuery("#product_menu").attr("value");
      var menu_position = jQuery("#menu_position").attr("value");
      var menu_id = jQuery("#menu_id").attr("value");
      jQuery.post('admin-ajax.php', {menu_id:menu_id,product_menu:product_menu,menu_position:menu_position,action:'product_menu'},
       function(response) {
         jQuery("#cck_list").html(response);
        }
      );
    });
    
    jQuery(".menu_edit").click(function() {
      var id = jQuery(this).attr("rel");
       jQuery.post('admin-ajax.php', {menu_id:id,action:'menu_edit'},
       function(response) {
         jQuery("#cck_list").html(response);
        }
      );
    })
    jQuery(".menu_delete").click(function() {
      var id = jQuery(this).attr("rel");
      if(confirm('Do you want to delete this menu')) {
        jQuery.post('admin-ajax.php', {menu_id:id,action:'menu_delete'},
         function(response) {
          jQuery("#cck_list").html(response);
         }
        );
      }
    }) 
    
    
     jQuery( "#sortable" ).sortable({
      start: function (event, ui) {
      
      },
      change:  function (event, ui) {
      
      
      },
     update: function(event, ui) {
       var newOrder = jQuery(this).sortable('toArray').toString();
        jQuery.post('admin-ajax.php', {order:newOrder,action:'build_content_order'});
     }
     });
    
    
   })
    </script>
  <?php
 }
 function menu_delete() {
  global $wpdb;
  $menu_id = $_POST['menu_id'];
  $default_menu_id = $this->get_default_menu();
  $wpdb->query("update ".$wpdb->prefix."scck_content_type set menu_id = '$default_menu_id' where menu_id = '$menu_id'");
  $wpdb->query("delete from ".$wpdb->prefix."scck_menu where id = '$menu_id'");
  unset($_REQUEST['menu_id']);
  $this->scck_products_type_list();
  die(0);
  
 }
 
 function menu_edit() {
  $this->scck_products_type_list();
  die(0);
 }
 
 function scck_products_type_list ($error = array()) {
  global $wpdb;
  $this->cck_jquery();
  $request_menu_id = $_REQUEST['menu_id'];
  ?>
  
  <div class="wrap">
  <h2>Manage Content Type</h2>
  <div style="float:right;padding:10px;">Help Us, Like Us	<div class="fb-like" data-href="http://www.facebook.com/pages/Wordpress-Expert/105504792973227" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div></div>
  <?php

  if($error) {
   foreach($error as $k => $v) {
    if($k == 'status') {
     print '<div class="status">';
     print $v;
     print '</div>';    
    }else {
     print '<div class="error">';
     print $v;
     print '</div>';    
    }
   }
  }
  print '<table width="100%" cellspacing="0" cellpadding="8" border="0" >';
  print '<tr style="background:#ccc;"><th  align="left">Sn.</th><th  align="left">Machine Name</th>';
  print '<th align="left">Label</th>';
  print '<th align="left"></th>';
  print '<th align="left"></th>';
  print '<th  align="left"></th>';
  print '</tr>';
  
 
  
  $menu_rows = $wpdb->get_results("select * from ".$wpdb->prefix."scck_menu order by weight");
  
  for($m = 0; $m < count($menu_rows); ++$m) {
   if($menu_rows[$m]->menuname == 'No Menu') {
     print '<tr class="sortable_menu" id="menu_'.$menu_rows[$m]->id.'"><td></td>';
     print '<td>'.$menu_rows[$m]->menuname.'</td>';
     print '<td></td>';
     print '<td></td>';
     print '<td></td>';
     print '<td></td>';
     print '</tr>';
      print '<tbody id="sortable">';
   }else {
    if($request_menu_id == $menu_rows[$m]->id) {
      print '<tr class="sortable_menu"><td></td>';
      print '<td><input type="text" name="product_menu" id="product_menu" size="30" value="'.$menu_rows[$m]->menuname.'"></td>';
      print '<td><select name="menu_position" id="menu_position">';
      print '<option value="">Menu Position</option>';
      for($p = 1; $p < 100;++$p) {
       if($menu_rows[$m]->position == $p) {
        print '<option value="'.$p.'" selected>'.$p.'</option>';  
       }else {
        print '<option value="'.$p.'">'.$p.'</option>';  
       }
       
      }
      print '</select></td>';
      print '<td><input type="button" value="Update Menu" class="button-primary create_menu" ></td>';
      print '<td><input type="hidden" value="'.$request_menu_id.'" id="menu_id" ></td>';
      print '<td>&nbsp;</td>';
      print '<td>&nbsp;</td>';
      print '</tr>'; 
    }else {
     print '<tr class="sortable_menu" id="menu_'.$menu_rows[$m]->id.'"><td>+</td>';
     print '<td>'.$menu_rows[$m]->menuname.'</td>';
     print '<td><a href="javascript:void(0);" rel="'.$menu_rows[$m]->id.'" class="menu_edit">Edit</a></td>';
     print '<td><a href="javascript:void(0);" rel="'.$menu_rows[$m]->id.'" class="menu_delete">Delete</a></td>';
     print '<td></td>';
     print '<td></td>';
     print '</tr>';      
    }

   }

   
   $menu_id = $menu_rows[$m]->id;
   $sql = "select * from ".$wpdb->prefix."scck_content_type where menu_id = '$menu_id' order by weight";
   $results = $wpdb->get_results($sql);
  for($i =0 ; $i < count($results); ++$i) {
    ++$sn ;
    print '<tr class="sortable_group" id="cck_'.$results[$i]->id.'"><td>+</td>';
    print '<td>'.$results[$i]->product_machine_name.'</td>';
    print '<td>'.$results[$i]->product_label.'</td>';
    if($results[$i]->product_machine_name == 'post' || $results[$i]->product_machine_name == 'page') {
     print '<td>&nbsp;</td>';
     print '<td>&nbsp;</td>';
     print '<td><a href="'.$this->build_url('&act=attr&content_type_id='.$results[$i]->id).'">Manage Fields</a></td>'; 
    }else {
     print '<td><a class="settings" href="'.$this->build_url('&act=add-type&content_type_id='.$results[$i]->id).'">Settings</a></td>';
     print '<td><!--<a href="'.$this->build_url('&act=add-type&content_type_id='.$results[$i]->id).'">Delete</a>--></td>';
     print '<td><a class="settings" href="'.$this->build_url('&act=attr&content_type_id='.$results[$i]->id).'">Manage Fields</a></td>';
    }
    print '</tr>';  
   }
  }
  
  $sql = "select * from ".$wpdb->prefix."scck_content_type where menu_id = '0' order by weight";
  $results = $wpdb->get_results($sql);
  for($i =0 ; $i < count($results); ++$i) {
    ++$sn ;
    print '<tr class="sortable_group" id="cck_'.$results[$i]->id.'"><td>+</td>';
    print '<td>'.$results[$i]->product_machine_name.'</td>';
    print '<td>'.$results[$i]->product_label.'</td>';
    if($results[$i]->product_machine_name == 'post' || $results[$i]->product_machine_name == 'page') {
     print '<td>&nbsp;</td>';
     print '<td>&nbsp;</td>';
     print '<td>&nbsp;</td>'; 
    }else {
     print '<td><a href="'.$this->build_url('&act=add-type&content_type_id='.$results[$i]->id).'">Edit</a></td>';
     print '<td><!--<a href="'.$this->build_url('&act=add-type&content_type_id='.$results[$i]->id).'">Delete</a>--></td>';
     print '<td><a href="'.$this->build_url('&act=attr&content_type_id='.$results[$i]->id).'">Manage Fields</a></td>';     
    }
    
    print '</tr>';  
   }
   
   print '</tbody>';
 
   print '<tr class="sortable_group"><td></td>';
   print '<td><input type="text" name="product_machine_name" id="product_machine_name" size="30" placeholder="Enter content type machine name"></td>';
   print '<td><input type="text" name="product_label" id="product_label" placeholder="Enter content type label" size="30"></td>';
   print '<td><input type="button" value="Create Content Type" class="button-primary create_cc" ></td>';
   print '<td>&nbsp;</td>';
   print '<td>&nbsp;</td>';
   print '</tr>';
   if($request_menu_id == 0) {
    print '<tr class="sortable_group"><td></td>';
    print '<td><input type="text" name="product_menu" id="product_menu" size="30" placeholder="Enter parent menu label"></td>';
    print '<td><select name="menu_position" id="menu_position">';
    print '<option value="">Menu Position</option>';
    for($p = 1; $p < 100;++$p) {
     print '<option value="'.$p.'">'.$p.'</option>'; 
    }
    print '</select></td>';
    print '<td><input type="button" value="Create Parent Menu" class="button-primary create_menu" ></td>';
    print '<td>&nbsp;</td>';
    print '<td>&nbsp;</td>';
    print '<td>&nbsp;</td>';
    print '</tr>';     
   }

  print '</table>';
 }
 
 function product_menu() {
  global $wpdb;
  $product_menu	= trim($_POST['product_menu']);
  $menu_position	= trim($_POST['menu_position']);
  $menu_id	= trim($_POST['menu_id']);
  if($product_menu != '') {
   $data = array();
   $data[] = " menuname = '$product_menu'";
   $data[] = " position = '$menu_position'";
   if($menu_id > 0) {
    $wpdb->query("update ".$wpdb->prefix."scck_menu set ".implode(",",$data)." where id = '$menu_id'");
    unset($_REQUEST['menu_id']);
   }else {
    $wpdb->query("insert into ".$wpdb->prefix."scck_menu set ".implode(",",$data)); 
   }
   
  }else {
   $error['error'] = 'Cannot create menu with black string'; 
  }
  
  $this->scck_products_type_list($error);
  die(0);
 }
 function add_cck() {
  global $wpdb;
  $product_label	= trim($_POST['product_label']);
  $product_machine_name	= sanitize_title(trim($_POST['product_machine_name']));
  $total = $wpdb->get_var("select count(*) from ".$wpdb->prefix."scck_content_type where product_machine_name = '$product_machine_name'");
  if($total > 0) {
   $error['duplicate'] = 'Content type allready exists, please choose another.';
  }
  if($product_label != '' && $product_machine_name != '' && $total == 0) {
   $data[] = "enable = 1";
   $data[] = "product_machine_name = '$product_machine_name'";
   $data[] = "product_label = '$product_label'";
   $data[] = "add_new_label = 'Add New ".$product_label."'";
   $data[] = "edit_label = 'Edit ".$product_label."'";
   $data[] = "all_product_label = 'All ".$product_label."s'";
   $data[] = "view_label	 = 'View ".$product_label."'";
   $data[] = "search_label	 = 'Search ".$product_label."'";
   $data[] = "menu_label	 = '$product_label'";
   $data[] = "product_public	 = '1'";
   $data[] = "has_archive	 = '1'";
   $data[] = "publicly_queryable	 = '1'";
   $data[] = "exclude_from_search	 = '1'";
   $data[] = "show_ui	 = '1'";
   $data[] = "show_in_menu	 = '1'";
   $data[] = "menu_id	 = '1'";
   $data[] = "category	 = '1'";
   $data[] = "post_tag	 = '1'";
   $value = addslashes("title,editor,author,thumbnail,excerpt,comments,revisions");
   $data[] = "supports  = '$value'";
   
   $wpdb->query("insert into ".$wpdb->prefix."scck_content_type set ".implode(",",$data));
   $this->content_type_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type where product_machine_name = '$product_machine_name'");
   $this->create_default_group('default','More '.$product_label.' Detail');
    $error['status'] = '<strong>Congratulation! new content type has been created. Refresh the page to see the new content type.</strong>'; 
  }else {
   if($total == 0) {
     $error['error'] = 'Please enter name and label of new content type.'; 
   }
   
  }
  
  $this->scck_products_type_list($error);
  
  die(0);
 }
 
 function get_default_menu() {
  global $wpdb;
  return $wpdb->get_var("select id from ".$wpdb->prefix."scck_menu where menuname = 'No Menu'");
 }
 
 
 function scck_product_field() {
  $fields = array('product_machine_name'
 ,'product_label'
 ,'add_new_label'
 ,'edit_label'
 ,'all_product_label'
 ,'view_label'
 ,'search_label'
 ,'menu_label'
 ,'product_public'
 ,'has_archive'
 ,'publicly_queryable'
 ,'exclude_from_search'
 ,'show_ui'
 ,'show_in_menu'
 ,'enable'
 ,'category'
 ,'post_tag'
 ,'supports'
 ,'meta_title'
 ,'meta_desc'
 ,'meta_keyword'
 );
  return $fields;
 }
 
 


}