<?php



class simple_cck_fields extends simple_cck_fields_settings{
 public $field_id = 0;
 
 function scck_product_fields() {
  global $wpdb;
  $fld = $_REQUEST['fld'];
  $grp = $_REQUEST['grp'];
  $this->field_id = $_REQUEST['field_id'];
  if(isset($_REQUEST['fld'])) {
    switch($fld) {
     case 'new':
     case 'edit':
      $this->add_new_field();
     break;
     case 'save':
      $this->save_field();
     break;
     case 'set':
      $this->field_settings();
     break;
     case 'set_done':
      $this->field_settings_save();
     break;
     case 'delconf':
      $this->field_delete_confirm();
     break;
     case 'del':
      $this->field_delete();
     break;
    
    default:
       //$this->group_list();
     break;
    }
  }elseif(isset($_REQUEST['grp'])) {
   
  }else {
   print '<div id="build_list">';
   $this->build_list();
   print '</div>';
   
   //print '<div id="field_list">';
   //$this->field_list();
   //print '</div>';
    //print '<div id="group_list">';
    //$this->group_list();
   // print '</div>';
  }
 }
 
 function delete_field() {
  global $wpdb;
  $content_type_id = trim($_POST['content_type_id']);
  $field_id = $_POST['field_id'];
  unset($_REQUEST['field_id']);
  
   $row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type_fields where id= '$field_id'");
   $wpdb->query("delete from ".$wpdb->prefix."scck_content_type_fields_group_rel where field_id = '$field_id'");
   $wpdb->query("delete from ".$wpdb->prefix."scck_content_type_fields_detail_tx where field_id = '$field_id'");
   $wpdb->query("delete from ".$wpdb->prefix."scck_content_type_fields_detail where field_id = '$field_id'");
   $wpdb->query("delete from ".$wpdb->prefix."postmeta where meta_key = '$row->field_name'");
   $ct_row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type where id = '$content_type_id'");
   $wpdb->query("delete from ".$wpdb->prefix."scck_content_type_fields where id = '$field_id'");
   $this->build_list();
   die(0);
 }

 function field_delete_confirm() {
   global $wpdb;
   $row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type_fields where id= '$this->field_id'");
   $url = $this->build_url('&act=attr&content_type_id='.$this->content_type_id.'&field_id='.$this->field_id.'&fld=del');
   ?>
    <a href="<?php print $this->build_url('&act=attr&content_type_id='.$this->content_type_id);?>">Back</a>
    <div class="wrap">
    <form method="post" action="<?php print $url;?>">
    <table width="100%" cellpadding="3" cellspacing="0"  border="0">
    <tr>
      <td>Do you want to delete field <b><?php print $row->field_name ?></b> of type <b><?php print $row->field_type ?><b></td>
    </tr>
    <tr>
      <td>All data will be deleted</td>
    </tr>
    </table>
    
    <input type="submit" value="Cofirm Delete" class="button-primary">
    </form>
    </div>
<?php       
 }

 function add_new_field() {
  global $wpdb;
  $field_id = $_GET['field_id'];
  if($field_id > 0) {
   $field_row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type_fields where id = '$field_id'");
   $field_label = $field_row->field_label;
   $field_name = $field_row->field_name;
   $field_type = $field_row->field_type;
   $group_row = $this->get_group($field_id);
   $field_group = $group_row->id;
   
  }
   ?>
    <a href="<?php print $this->build_url('&act=attr&content_type_id='.$this->content_type_id);?>">Back</a>
   <div class="wrap">
    <form method="post">
     <table width="100%" cellpadding="3" cellspacing="0"  border="0">
      <tr>
       <td><label for="field_label">Enter Field Label:</label></td>
       <td><input type="text" name="field_label" id="field_label" value="<?php print $field_label;?>"></td>
       <td><label for="field_name">Enter Field Name:</label></td>
       <td><input type="text" name="field_name" id="field_name" value="<?php print $field_name;?>" <?php if($field_id >0) print 'readonly' ?>></td>
       <td><label for="field_type">Select Field Type:</label></td>
       <td>
        <select name="field_type" id="field_type" <?php if($field_id >0) print 'readonly' ?>><?php print $this->build_select($this->field_type(),$field_type);?></select>
       </td>
       <td><label for="field_type">Select Group:</label></td>
       <td><select name="field_group" id="field_group"><?php print $this->build_select($this->field_group(),$field_group);?></select></td>
      </tr>  
     </table>
     <input type="hidden" value="save" name="fld">
      <input type="hidden" name="field_id" value="<?php print $field_id;?>">
     <input type="submit" value="Submit" class="button-primary">
    </form>
   </div>
   <?php
 }
 
 function save_field() {
  global $wpdb;
  $field_label = trim($_POST['field_label']);
  $field_name = trim($_POST['field_name']);
  $field_group = $_POST['field_group'];
  $field_type = $_POST['field_type'];
  $field_id = $_POST['field_id'];
  
  $error = array();
  
  if($field_label != '' && $field_name != '') {
    if($field_id > 0) {
      $data[] = "field_label = '$field_label'";
      $wpdb->query("update ".$wpdb->prefix."scck_content_type_fields set ".implode(",",$data)." where id = '$field_id'");
      $this->associate_group_field($field_id,$field_group,true);
    }else {
      $total = $wpdb->get_var("select count(*) from ".$wpdb->prefix."scck_content_type_fields where field_name = '$field_name'");
      $total1 = $wpdb->get_var("select count(*) from ".$wpdb->prefix."postmeta where meta_key 	= '$field_name'");
      if($total == 0 && $total1 == 0) {
       $data[] = "content_type_id = '".$this->content_type_id."'";
       $data[] = "field_name = '".$field_name."'";
       $data[] = "field_type = '$field_type'";
       $data[] = "field_label = '$field_label'";
       $wpdb->query("insert into ".$wpdb->prefix."scck_content_type_fields set ".implode(",",$data));
       $field_id = $this->get_field_id($field_name);
       $this->associate_group_field($field_id,$field_group);       
      }else {
       $error['duplicate']  = 'Field name $field_name allready exists, please choose another ';
      }
    }
  }
  $param = array();
  foreach($error as $k => $v) {
   $param[] = $k."=".$v;
  }
  if(count($param) > 0) {
   $param = '&'.implode("&",$param);
  }
  $url = $this->build_url('&act=attr&content_type_id='.$this->content_type_id.$param);
  $this->redirect($url);
  exit;
 }
 function associate_group_field($field_id,$field_group,$edit = false) {
  global $wpdb;
  $data = array();
  $data[] = "field_id = '$field_id'";
  $data[] = "group_id  = '$field_group'";
  if($edit == true) {
   $wpdb->query("update ".$wpdb->prefix."scck_content_type_fields_group_rel set " .implode(",",$data)." where field_id = '$field_id'"); 
  }else {
   $wpdb->query("insert into ".$wpdb->prefix."scck_content_type_fields_group_rel set " .implode(",",$data)); 
  }
  
 }

function get_field_id($field_name) {
 global $wpdb;
 $content_type_id = $_REQUEST['content_type_id'];
 $id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type_fields where content_type_id = '".$content_type_id."' and field_name = '$field_name'");
 return $id;
}

 function field_group() {
  global $wpdb;
  $content_type_id = $_REQUEST['content_type_id'];
  $results = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields_group where content_type_id = '".$content_type_id."'");
  
  $array = array();
  for($i =0; $i < count($results); ++$i) {
   $array[$results[$i]->id] = $results[$i]->group_name;
  }
  
  return $array;
 }

 function field_type() {
   $array = array(
    'textfield' => 'Textfield'
    ,'textarea' => 'TextArea'
    ,'select' => 'Select'
    ,'checkbox' => 'Checkbox'
    ,'radio' => 'Radio'
    ,'image' => 'Image'
    ,'file' => 'File'
    ,'date' => 'Date'
    ,'taxonomy' => 'Taxonomy'
    ,'user_reference' => 'User Reference'
    ,'content_reference' => 'Content Reference'
   );
   return $array;
 }

function sortable_jquery() {
 $content_type_id = $_REQUEST['content_type_id'];
 ?>
 <script>
    jQuery(document).ready(function() {
     
     jQuery( "#sortable" ).sortable({
      start: function (event, ui) {
      
      },
      change:  function (event, ui) {
      
      
      },
     update: function(event, ui) {
       
       var newOrder = jQuery(this).sortable('toArray').toString();
     
        jQuery.post('admin-ajax.php', {order:newOrder,action:'build_order',content_type_id:'<?php print $content_type_id;?>'});
     }
     });
    })
  </script>
  <?php
}
 function build_list($error = array()) {
  global $wpdb;
  $content_type_id = $_REQUEST['content_type_id'];
  $field_id = $_REQUEST['field_id'];
  $group_id = $_REQUEST['group_id'];
  
  $group_rows = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields_group where content_type_id = '$content_type_id' order by weight asc");
  $this->sortable_jquery();
  $this->jquery_ope();
  $this->jquery_field();
  
  print '<div class="wrap" id="simple_cck">';
  print '<a href="'.$this->build_url().'">Back</a> ';
  $content_type_name = $wpdb->get_var("select product_machine_name from ".$wpdb->prefix."scck_content_type where id='$content_type_id'");
  print '<h2>Manage Fields of content type <i>'.$content_type_name.'</i></h2>';
  if(count($error) > 0) {
   print '<div class="error">';
   foreach($error as $k => $v) {
    print $v;
   }
   print '</div>';
  }
  
  print '<table width="100%" cellspacing="0" cellpadding="5" border="0">';
  
  
  for($i =0; $i < count($group_rows); ++$i) {
   
    if($group_id == $group_rows[$i]->id) {
     print '<tr class="sortable_group" id="grp_'.$group_rows[$i]->id.'">';
     print '<td><input type="text" name="group_name" id="group_name" value="'.$group_rows[$i]->group_name.'"></td>';
     print '<td><input type="text" name="group_title" id="group_title" value="'.$group_rows[$i]->group_title.'"></td>';
     
     print '<td><input type="button" value="Save" class="button-primary" id="group_add"></td>';
     print '<td><input type="hidden" name="group_id" id="group_id" value="'.$group_id.'"></td>';
      print '<td colspan="2"></td>';
     print '</tr>';  
    }else {
      print '<tr class="sortable_group" id="grp_'.$group_rows[$i]->id.'">';
      print '<td><b>'.$group_rows[$i]->group_name.'</b></td>';
      print '<td>'.$group_rows[$i]->group_title .'</td>';
      if($group_rows[$i]->group_name == 'default') {
       print '<td></td>';
       print '<td></td>'; 
      }else {
       print '<td><a href="javascript:void(0);" class="group_edit settings2" rel="'.$group_rows[$i]->id.'">Edit</a></td>';
       print '<td><a href="javascript:void(0);" class="group_delete settings2" rel="'.$group_rows[$i]->id.'">Delete</a></td>'; 
      }
      print '<td colspan="2"></td>';
      print '</tr>';
      
      if($group_rows[$i]->group_name == 'default') {
       print '<tbody id="sortable">';
      }
    }
    $sgroup_id = $group_rows[$i]->id;
    
    //get all field of select group
    $sql = "select ctf.* from ".$wpdb->prefix."scck_content_type_fields ctf
    INNER JOIN ".$wpdb->prefix."scck_content_type_fields_group_rel gr
    on gr.field_id = ctf.id
    where gr.group_id = '$sgroup_id' order by gr.weight";
    
    $group_field_rows = $wpdb->get_results($sql);
    
    for($j =0; $j < count($group_field_rows); ++$j) {
     if($field_id == $group_field_rows[$j]->id) {
      $group_row = $this->get_group($field_id);
      $field_group = $group_row->id;
       print '<tr class="sortable_field" id="fld_'.$group_field_rows[$j]->id.'">';
       print '<td><input type="text" name="field_label" id="field_label" value="'.$group_field_rows[$j]->field_label.'"></td>';
       print '<td><input type="text" name="field_name" id="field_name" value="'.$group_field_rows[$j]->field_name.'"></td>';
       print '<td><select name="field_type" id="field_type">'.$this->build_select($this->field_type(),$fields_row[$j]->field_type).'</select></td>';
       print '<td><select name="field_group" id="field_group">'.$this->build_select($this->field_group(),$field_group).'</select></td>';
       print '<td><input type="button" name="field_add" value="Save" class="button-primary add_field"</td>';
       print '<td><input type="hidden" name="field_id" id="field_id" value="'.$field_id.'"></td>';
       print '</tr>';   
     }else {
      print '<tr class="sortable_field" id="fld_'.$group_field_rows[$j]->id.'">';
      print '<td>'.$group_field_rows[$j]->field_label.'</td>';
      print '<td>'.$group_field_rows[$j]->field_name.'</td>';
      print '<td>'.$group_field_rows[$j]->field_type.'</td>';
      print '<td><a href="'.$this->build_url('&act=attr&content_type_id='.$content_type_id.'&field_id='.$group_field_rows[$j]->id.'&fld=set').'" class="fields_link">Settings</a></td>';
      print '<td><a href="javascript:void(0);" rel="'.$group_field_rows[$j]->id.'" class="field_edit">Edit</a></td>';
      print '<td><a href="javascript:void(0);" rel="'.$group_field_rows[$j]->id.'" class="field_delete">Delete</a></td>';
      print '</tr>';              
     }

    }
  }
  print '</tbody>';
  
  if($field_id == 0) {
   print '<tr class="sortable_field">';
   print '<td><input type="text" name="field_label" id="field_label" placeholder="Enter field label"></td>';
   print '<td><input type="text" name="field_name" id="field_name" placeholder="Enter field name"></td>';
   print '<td><select name="field_type" id="field_type">'.$this->build_select($this->field_type(),$field_type).'</select></td>';
   print '<td><select name="field_group" id="field_group">'.$this->build_select($this->field_group(),$field_group).'</select></td>';
   print '<td><input type="button" name="field_add" value="Add Field" class="button-primary add_field"></td>';
   print '<td>&nbsp;</td>';
   print '</tr>';
  }
  if($group_id == 0) {
   print '<tr class="sortable_group">';
   print '<td><input type="text" name="group_name" id="group_name" placeholder="Enter group id/name"></td>';
   print '<td><input type="text" name="group_title" id="group_title" placeholder="Enter group title"></td>';
   print '<td><input type="button" value="Add Group" class="button-primary" id="group_add"></td>';
   print '<td><input type="hidden" name="group_id" id="group_id" value="0"></td>';
   print '<td>&nbsp;</td>';
   print '<td>&nbsp;</td>';
   print '</tr>';
  }
  print '</table>';
  print '</div>';
 }
 
 function build_order() {
  global $wpdb;
  $content_type_id = $_POST['content_type_id'];
  $order = trim($_POST['order']);
  $group_id = $this->get_default_group();
  if($content_type_id > 0 and $order != '') {
   $order_arr = explode(",",$order);
   $field_order = 0;
   $group_order = 0;
   for($i = 0; $i < count($order_arr); ++$i) {
    list($type,$id) = explode("_",$order_arr[$i]);
    if($type == 'fld') {
      ++$field_order;
      $wpdb->query("update ".$wpdb->prefix."scck_content_type_fields_group_rel set weight = '$field_order',group_id = '$group_id' where field_id = '$id'");
    }
    if($type == 'grp') {
      ++$group_order;
      $group_id = $id;
      $wpdb->query("update ".$wpdb->prefix."scck_content_type_fields_group set weight = '$group_order' where id = '$id'");
    }
   }
  }
  $this->build_list();
  die(0);
 }
 
 function field_list() {
  global $wpdb;
  $content_type_id = $_REQUEST['content_type_id'];
  $field_id = $_REQUEST['field_id'];
  $product_attribute_rel_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_product_attribute_rel where content_type_id = '$content_type_id'");
   
  $row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type where id = '$content_type_id'");
  if($_GET['duplicate'] == 1) {
   print '<div class="error">Duplicate field name</div>';
  }
  $this->jquery_field();
  print '<div class="wrap">';
  print '<h2>Manage Fields of contet type <i>'.$row->product_machine_name.'</i></h2>';
  print '<a href="'.$this->build_url().'">Back</a> ';
  
  $fields_row = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields where content_type_id = '$content_type_id'");
  ?>
   <script>
    jQuery(document).ready(function() {
     
     jQuery( "#sortable" ).sortable({
      start: function (event, ui) {
      
      },
      change:  function (event, ui) {
      
      
      },
     update: function(event, ui) {
       var newOrder = jQuery(this).sortable(\'toArray\').toString();
        console.log(newOrder);                                    
       //var category = jQuery("#category").attr("value");
       //jQuery.post(\'admin-ajax.php\', {order:newOrder,category:category,action:\'build_order\'});
     }
     });
    })
  </script>
   <?php
   
  print '<table width="100%" cellpadding="6" cellspacing="0" border="0" ><tr class="row_header" >';
  print '<td>Field label</td>';
  print '<td>Field name</td>';
  print '<td>Field type</td>';
  print '<td></td>';
  print '<td></td>';
  print '<td></td>';
  print '</tr>';
  print '<tbody id="sortable">';
  for($i =0 ; $i < count($fields_row); ++$i) {
   $even_odd = ($even_odd == 'even') ? 'odd' : 'even';
   if($field_id == $fields_row[$i]->id) {
    $group_row = $this->get_group($field_id);
     $field_group = $group_row->id;
     print '<tr class="'.$even_odd.'">';
     print '<td><input type="text" name="field_label" id="field_label" value="'.$fields_row[$i]->field_label.'"></td>';
     print '<td><input type="text" name="field_name" id="field_name" value="'.$fields_row[$i]->field_name.'"></td>';
     print '<td><select name="field_type" id="field_type">'.$this->build_select($this->field_type(),$fields_row[$i]->field_type).'</select></td>';
     print '<td><select name="field_group" id="field_group">'.$this->build_select($this->field_group(),$field_group).'</select></td>';
     print '<td><input type="button" name="field_add" value="Save" class="button-primary add_field"</td>';
     print '<td><input type="hidden" name="field_id" id="field_id" value="'.$field_id.'"></td>';
     print '</tr>';   
   }else {
    print '<tr class="'.$even_odd.'">';
    print '<td>'.$fields_row[$i]->field_label.'</td>';
    print '<td>'.$fields_row[$i]->field_name.'</td>';
    print '<td>'.$fields_row[$i]->field_type.'</td>';
    print '<td><a class="settings" href="'.$this->build_url('&act=attr&content_type_id='.$this->content_type_id.'&field_id='.$fields_row[$i]->id.'&fld=set').'">Settings</a></td>';
    print '<td><a href="javascript:void(0);" rel="'.$fields_row[$i]->id.'" class="field_edit settings">Edit</a></td>';
    print '<td><a href="javascript:void(0);" rel="'.$fields_row[$i]->id.'" class="field_delete settings">Delete</a></td>';
    print '</tr>';   
   }
   
  }
  print '</tbody>';   
 if($field_id == 0) {
  print '<tr class="'.$even_odd.'">';
  print '<td><input type="text" name="field_label" id="field_label" value=""></td>';
  print '<td><input type="text" name="field_name" id="field_name" value=""></td>';
  print '<td><select name="field_type" id="field_type">'.$this->build_select($this->field_type(),$field_type).'</select></td>';
  print '<td><select name="field_group" id="field_group">'.$this->build_select($this->field_group(),$field_group).'</select></td>';
  print '<td><input type="button" name="field_add" value="Add" class="button-primary add_field"></td>';
  print '<td>&nbsp;</td>';
  print '<td>&nbsp;</td>';
  print '</tr>';
 }
  print '</table>';
  print '</div>';
 }
 
 function edit_field() {
  global $wpdb;
  $content_type_id = trim($_POST['content_type_id']);
  $field_id = $_POST['field_id'];
  $this->build_list();
  die(0);
 }
 
 function add_field() {
  global $wpdb;
  $content_type_id = trim($_POST['content_type_id']);
  $field_label = trim($_POST['field_label']);
  $field_name = sanitize_title(trim($_POST['field_name']));
  $field_group = $_POST['field_group'];
  $field_type = $_POST['field_type'];
  
  $field_id = $_POST['field_id'];
  unset($_REQUEST['field_id']);
  $error = array();
  
  
  
  if($field_label != '' && $field_name != '') {
    if($field_id > 0) {
      $data[] = "field_label = '$field_label'";
      $wpdb->query("update ".$wpdb->prefix."scck_content_type_fields set ".implode(",",$data)." where id = '$field_id'");
      $this->associate_group_field($field_id,$field_group,true);
      $field_id = 0;
    }else {
      $total = $wpdb->get_var("select count(*) from ".$wpdb->prefix."scck_content_type_fields where field_name = '$field_name' and content_type_id= '$content_type_id'");
      //$total1 = $wpdb->get_var("select count(*) from ".$wpdb->prefix."postmeta where meta_key 	= '$field_name'");
      if($total == 0) {
       $data[] = "content_type_id = '".$content_type_id."'";
       $data[] = "field_name = '".$field_name."'";
       $data[] = "field_type = '$field_type'";
       $data[] = "field_label = '$field_label'";
       $wpdb->query("insert into ".$wpdb->prefix."scck_content_type_fields set ".implode(",",$data));
       $field_id = $this->get_field_id($field_name);
       $this->associate_group_field($field_id,$field_group);
       $this->create_default_field_settings($field_id,$field_type,$field_name,$field_label);
      }else {
       $error['duplicate']  = "Field name $field_name allready exists in this content type, please choose another.";
      }
    }
  }else {
    $error['required']  = "Please enter field label and field name";
  }
  $this->build_list($error);
  die(0);
 } 
 
 function group_list() {
  global $wpdb;
  $content_type_id = $_REQUEST['content_type_id'];
  $group_id = $_POST['group_id'];
  $product_attribute_rel_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type_fields_group_rel where content_type_id = '$content_type_id'");
  
   
  $row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type where id = '$content_type_id'");
  if($_GET['duplicate'] == 1) {
   print '<div class="error">Duplicate group name</div>';
  }
  $this->jquery_ope();
  print '<div class="wrap">';
  print '<h2>Manage Group of contet type <i>'.$row->product_machine_name.'</i></h2>';
  print '<a href="'.$this->build_url().'">Back</a> ';
  
  $groups_row = $wpdb->get_results("select * from ".$wpdb->prefix."scck_content_type_fields_group where content_type_id = '$content_type_id'");
  
  print '<table width="100%" cellpadding="6" cellspacing="0" border="0"><tr class="row_header">';
  print '<td>Group Name</td>';
  print '<td>Group Title </td>';
  print '<td></td>';
  print '<td></td>'; 
  print '</tr>';
  
  for($i =0 ; $i < count($groups_row); ++$i) {
   $even_odd = ($even_odd == 'even') ? 'odd' : 'even';
   if($group_id == $groups_row[$i]->id) {
     print '<tr class="'.$even_odd.'">';
     print '<td><input type="text" name="group_name" id="group_name" value="'.$groups_row[$i]->group_name.'"></td>';
     print '<td><input type="text" name="group_title" id="group_title" value="'.$groups_row[$i]->group_title.'"></td>';
     
     print '<td><input type="button" value="Save" class="button-primary" id="group_add"></td>';
     print '<td><input type="hidden" name="group_id" id="group_id" value="'.$group_id.'"></td>';
     print '</tr>';  
   }else {
    print '<tr class="'.$even_odd.'">';
    print '<td>'.$groups_row[$i]->group_name.'</td>';
    print '<td>'.$groups_row[$i]->group_title .'</td>';
    if($groups_row[$i]->group_name == 'default') {
    print '<td></td>';
    print '<td></td>'; 
    }else {
     print '<td><a href="javascript:void(0);" class="group_edit settings2" rel="'.$groups_row[$i]->id.'">Edit</a></td>';
     print '<td><a href="javascript:void(0);" class="group_delete settings2" rel="'.$groups_row[$i]->id.'">Delete</a></td>'; 
    }
    
    print '</tr>';    
   }
   
  }
  if($group_id == 0) { 
   print '<tr class="'.$even_odd.'">';
   print '<td><input type="text" name="group_name" id="group_name"></td>';
   print '<td><input type="text" name="group_title" id="group_title"></td>';
   print '<td><input type="button" value="Add" class="button-primary" id="group_add"></td>';
   print '<td><input type="hidden" name="group_id" id="group_id" value="0"></td>';
   print '</tr>';
  }
  print '</table>';
  
  print '</div>';
 }
 function get_default_group() {
  global $wpdb;
  $content_type_id = $_REQUEST['content_type_id'];
  return $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type_fields_group where content_type_id = '$content_type_id' and group_name = 'default'");
 }
 
 function get_group($field_id) {
  global $wpdb;
  $row = $wpdb->get_row("select a.* from ".$wpdb->prefix."scck_content_type_fields_group a
                 INNER JOIN  ".$wpdb->prefix."scck_content_type_fields_group_rel b
                 on a.id = b.group_id and b.field_id = '$field_id'
                 ");
  return $row;
  
 }
 function edit_group() {
  $group_id = $_POST['group_id'];
  print $this->build_list();
  die(0);
 }
 
 function delete_group() {
  global $wpdb;
  $group_id = $_POST['group_id'];
  $default_id = $wpdb->get_var("select id from ".$wpdb->prefix."scck_content_type_fields_group where group_name = 'default'");
  $wpdb->query("update ".$wpdb->prefix."scck_content_type_fields_group_rel set group_id = '$default_id' where group_id = '$group_id'");
  $wpdb->query("delete from  ".$wpdb->prefix."scck_content_type_fields_group where id = '$group_id'");
  unset($_POST['group_id']);
  print $this->build_list();
  die(0);
 }
 
 function new_group_add() {
  global $wpdb;
  $group_name = sanitize_title(trim($_POST['group_name']));
  $group_title = trim($_POST['group_title']);
  $content_type_id = $_POST['content_type_id'];
  $group_id = $_POST['group_id'];
  
  $data = array();
  if($group_name!= '' && $group_title != '') {
   $data[] = "content_type_id = '$content_type_id'";
   $data[] = "group_name = '$group_name'";
   $data[] = "group_title = '$group_title'";
   $total = $wpdb->get_var("select count(*) from ".$wpdb->prefix."scck_content_type_fields_group group_name = '$group_name' and content_type_id = '$content_type_id'");
   
   if($group_id > 0) {
     $wpdb->query("update  ".$wpdb->prefix."scck_content_type_fields_group set ".implode(",",$data). " where id = '$group_id'");
     unset($_REQUEST['group_id']);
   }else {
    if($total == 0) {
     $wpdb->query("insert into ".$wpdb->prefix."scck_content_type_fields_group set ".implode(",",$data));
    } else {
     $error['duplicate'] = 'Group allready exists. please choose another name';
    }
   }
  }else {
     $error['error'] = 'Please enter group name and group title';
  }
  print $this->build_list($error);
  die(0);
 }
 function jquery_field() {
  ?>
   <script>
   jQuery(function() {
    jQuery(".field_delete").click(function(){
     if(confirm('Do you want to delete this field')) {
      var field_id = jQuery(this).attr("rel");
      jQuery.post('admin-ajax.php', {field_id:field_id,action:'delete_field',content_type_id:'<?php print $_REQUEST['content_type_id'];?>'},
       function(response) {
         jQuery("#build_list").html(response);
        }
      );      
     }
    });
    
    jQuery(".field_edit").click(function(){
     var field_id = jQuery(this).attr("rel");
      jQuery.post('admin-ajax.php', {field_id:field_id,action:'edit_field',content_type_id:'<?php print $_REQUEST['content_type_id'];?>'},
       function(response) {
         jQuery("#build_list").html(response);
       }
      );
    })
    
    jQuery(".add_field").click(function() {
      var field_group = jQuery("#field_group").attr("value");
      var field_label = jQuery("#field_label").attr("value");
      var field_name = jQuery("#field_name").attr("value");
      var field_type = jQuery("#field_type").attr("value");
      var field_id = jQuery("#field_id").attr("value");
      
      jQuery.post('admin-ajax.php', {field_id:field_id,field_type:field_type,field_group:field_group,field_label:field_label,field_name:field_name,action:'add_field',content_type_id:'<?php print $_REQUEST['content_type_id'];?>'},
       function(response) {
         jQuery("#build_list").html(response);
        }
      );
    });
        
        
   })
    </script>
  <?php
 }
 
 function jquery_ope() {
  ?>
  <script>
   jQuery(function() {

    jQuery(".group_delete").click(function(){
     if(confirm('Do you want to delete this group')) {
      var group_id = jQuery(this).attr("rel");
      jQuery.post('admin-ajax.php', {group_id:group_id,action:'delete_group',content_type_id:'<?php print $_REQUEST['content_type_id'];?>'},
       function(response) {
         jQuery("#build_list").html(response);
        }
      );      
     }
    });
    
    jQuery(".group_edit").click(function(){
      var group_name = "";
      var group_title = "";
      var group_id = jQuery(this).attr("rel");
      jQuery.post('admin-ajax.php', {group_id:group_id,action:'edit_group',content_type_id:'<?php print $_REQUEST['content_type_id'];?>'},
       function(response) {
         jQuery("#build_list").html(response);
        }
      );
    });
    
    jQuery("#group_add").click(function() {
      var group_name = jQuery("#group_name").attr("value");
      var group_title = jQuery("#group_title").attr("value");
      var group_id = jQuery("#group_id").attr("value");
      
      jQuery.post('admin-ajax.php', {group_id:group_id,group_name:group_name,group_title:group_title,action:'new_group_add',content_type_id:'<?php print $_REQUEST['content_type_id'];?>'},
       function(response) {
         jQuery("#build_list").html(response);
        }
      );
     });
   });
   </script>
    <?php      
 }
}