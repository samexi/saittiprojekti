<?php
/**
 * @package Simple Advance Content Construction Kit
 * * @author Faaiq Ahmed
 * @version 1.0.3
 */
/*
Plugin Name: Simple Custom post type custom field
Description: Simple Custom post type custom field creator
Author: Faaiq Ahmed, Technial Architect,faaiqsj@gmail.com
Version: 1.0.3
*/

if(!defined('SIMPLE_CCK_PATH')) {
	define( 'SIMPLE_CCK_PATH', plugin_dir_path(__FILE__) );	
}

include_once(SIMPLE_CCK_PATH.'wp-simple-cck-build.php');
include_once(SIMPLE_CCK_PATH.'wp-simple-cck-settings.php');
include_once(SIMPLE_CCK_PATH.'wp-simple-cck-fields.php');
include_once(SIMPLE_CCK_PATH.'wp-simple-cck-base.php');
include_once(SIMPLE_CCK_PATH.'wp-simple-cck-content.php');


class simple_cck extends wp_simple_ckk_content {
  public $tbl_content_type;
  public $tbl_fields;
  public $tbl_fields_detail;
  public $tbl_fields_detail_tx;
  public $tbl_fields_group;
  public $tbl_fields_group_rel;
  public $tbl_menu;
  
  function __construct() {
   global $wpdb;
   $this->tbl_content_type = $wpdb->prefix.'scck_content_type';
   $this->tbl_fields = $wpdb->prefix.'scck_content_type_fields';
   $this->tbl_fields_detail = $wpdb->prefix.'scck_content_type_fields_detail';
   $this->tbl_fields_detail_tx = $wpdb->prefix.'scck_content_type_fields_detail_tx';
   $this->tbl_fields_group = $wpdb->prefix.'scck_content_type_fields_group';
   $this->tbl_fields_group_rel = $wpdb->prefix.'scck_content_type_fields_group_rel';
   $this->tbl_menu = $wpdb->prefix.'scck_menu';
   
   
   add_action('admin_menu', array(&$this,'simple_cck_menu'));
   add_action('admin_head', array($this,'add_style'));
   add_action('wp_ajax_new_group_add', array(&$this,'new_group_add'));
   add_action('wp_ajax_edit_group', array(&$this,'edit_group'));
   add_action('wp_ajax_delete_group', array(&$this,'delete_group'));
   add_action('wp_ajax_add_field', array(&$this,'add_field'));
   add_action('wp_ajax_edit_field', array(&$this,'edit_field'));
   add_action('wp_ajax_delete_field', array(&$this,'delete_field'));
   add_action('wp_ajax_build_order', array(&$this,'build_order'));
   add_action('wp_ajax_add_cck', array(&$this,'add_cck'));
   add_action('wp_ajax_product_menu', array(&$this,'product_menu'));
   add_action('wp_ajax_build_content_order', array(&$this,'build_content_order'));
   add_action('wp_ajax_menu_edit', array(&$this,'menu_edit'));
   add_action('wp_ajax_menu_delete', array(&$this,'menu_delete'));
   
   add_action('wp_ajax_search_user_reference_suggation', array(&$this,'search_user_reference_suggation'));
   add_action('wp_ajax_search_content_reference_suggation', array(&$this,'search_content_reference_suggation'));
   
   add_action('post_edit_form_tag', array(&$this,'scck_post_edit_form_tag'));
   add_action('init', array(&$this,'init'));
   add_action( 'add_meta_boxes', array(&$this,'build_meta_boxes' ));
   add_action( 'save_post', array(&$this,'save_post_field'),1,2);
   register_activation_hook(__FILE__, array(&$this,'install'));
   register_deactivation_hook(__FILE__, array(&$this,'uninstall'));
			add_action('wp_title', array(&$this,'meta_page_title'), 10, 1);
			add_action('wp_head', array(&$this,'meta_data'), 10, 1);
  }
  
  
		function meta_page_title($string)	{
			global $wpdb;
			$post_type = get_query_var('post_type');
			if($post_type  and is_archive()) {
					$meta_row = $wpdb->get_row("select meta_title,meta_desc,meta_keyword from ".$wpdb->prefix."scck_content_type where product_machine_name='$post_type'");
					return $meta_row->meta_title;
			}else {
				echo $string;	
			}
	}  
  
	function meta_data() {
			global $wpdb;
			$post_type = get_query_var('post_type');
			if($post_type and is_archive()) {
					$meta_row = $wpdb->get_row("select meta_title,meta_desc,meta_keyword from ".$wpdb->prefix."scck_content_type where product_machine_name='$post_type'");
					if($meta_row->meta_desc != "") {
					?>
						<meta name="description" content="<?php print $meta_row->meta_desc; ?>" />
						<meta name="keywords" content="<?php print $meta_row->meta_keyword; ?>" />
						<?php
					}
			}
	}
 
 function scck_post_edit_form_tag() {
     echo ' enctype="multipart/form-data"';
 }
  
  function add_style() {
   
   //wp_enqueue_script('jquery');
   wp_enqueue_script('jquery-ui-sortable');
   wp_enqueue_script('jquery-ui-draggable');
   wp_enqueue_script('jquery-ui-droppable');
   wp_enqueue_script('jquery-ui-datepicker');
   wp_enqueue_script('jquery-ui-selectable');
   wp_enqueue_style('jquery-ui-dark',plugins_url('theme/dark-hive/jquery-ui', __FILE__));
   wp_register_style( 'prefix-style', plugins_url('style.css', __FILE__) );
   wp_enqueue_style( 'prefix-style' );
   
   $this->parent_menu_header();
   $this->add_fb_like();
  }
  function add_fb_like() {
   ?>
   <div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));</script>
   <?php
  }
  function parent_menu_header() {
   global $wp_query,$wpdb;
   $post_type = $wp_query->query_vars['post_type'];
  	if(!$post_type) {
  		$post_type = $_REQUEST['post_type'];
  	}
  	if(!$post_type) {
  		$post_type =get_post_type( $post ) 	;
  	}
   
   $row = $wpdb->get_row("select * from ".$wpdb->prefix."scck_content_type where  	product_machine_name = '$post_type'");
  	$lbl = $row->menu_label;
   $row_menu_id = $row->menu_id;
  	$id = "toplevel_page_cck-main-".$row_menu_id;
  	if($post_type) {
				
  ?>
  <script>
  jQuery(document).ready(function() {
     
    jQuery("#<?php print $id;?>").removeClass("wp-has-submenu");
    jQuery("#<?php print $id;?>").removeClass("menu-top menu-icon-generic");
    jQuery("#<?php print $id;?>").removeClass("toplevel_page_icresources");
    jQuery("#<?php print $id;?>").removeClass("wp-current-submenu");
    jQuery("#<?php print $id;?>").removeClass("wp-has-current-submenu");
   
    jQuery("#wp-has-submenu").addClass("wp-has-current-submenu");
    jQuery("#wp-has-current-submenu").addClass("wp-has-current-submenu");
    jQuery("#wp-menu-open menu-top").addClass("wp-has-current-submenu");
    jQuery("#menu-icon-generic").addClass("wp-has-current-submenu");
    jQuery("#<?php print $id;?>").addClass("wp-has-current-submenu");
    jQuery("#wp-current-submenu").addClass("wp-has-current-submenu");
    
    jQuery("#<?php print $id;?> > a").addClass("wp-has-current-submenu");
    jQuery("#<?php print $id;?>s > a").removeClass("wp-not-current-submenu");
  
    jQuery("#<?php print $id;?> > ul > li").each(function( index ) {
     
      if(jQuery(this).text() == '<?php print $lbl;?>') {
       jQuery(this).addClass("current");
      }
    });
   });
  </script>
  <?php
  }
  }
  
  function simple_cck_menu() {
   global $wpdb;
   add_menu_page('Content Cunstruction', 'Content Cunstruction', 'administrator', 'cck-main', array($this,'scck_products_type'),   plugins_url('icon.png',__FILE__));
   $menus = $wpdb->get_results("select * from ".$wpdb->prefix."scck_menu where menuname != 'No Menu' order by weight");
   for($i =0 ; $i < count($menus); ++$i) {
     add_menu_page($menus[$i]->menuname, $menus[$i]->menuname, 'administrator', 'cck-main-'.$menus[$i]->id, array($this,'list_products_submenu'),   plugins_url('icon.png',__FILE__), $menus[$i]->position);
     $sub_menu_rows = $wpdb->get_results("select * from wp_scck_content_type where enable = 1 and menu_id = '".$menus[$i]->id."'");
     for($j = 0; $j < count($sub_menu_rows); ++$j) {
       add_submenu_page( 'cck-main-'.$menus[$i]->id, $sub_menu_rows[$j]->menu_label, $sub_menu_rows[$j]->menu_label,'administrator', $sub_menu_rows[$j]->product_machine_name, array($this,'select_sub_menu') );
     }
     
   }
   //add_submenu_page( 'cck-main', 'Manage Content Type', 'Manage Content Type','administrator', 'cck-main-ct', array($this,'manage_content_type') );
   //add_submenu_page( 'cck-main', 'Manage Content Type', 'Manage Content Type','administrator', 'cck-main-ct', array($this,'manage_content_type') );
  }
  
  function list_products_submenu() {
   global $wpdb;
   $page_arr = explode("-",$_GET['page']);
   $menu_id = $page_arr[2];
   $sub_menu_rows = $wpdb->get_results("select * from wp_scck_content_type where enable = 1 and menu_id = '$menu_id'");
   print '<div id="wrap">';
   print '<ul>';
   for($i = 0; $i < count($sub_menu_rows); ++$i) {
    print '<li><a href="'.admin_url('edit.php?post_type='.$sub_menu_rows[$i]->product_machine_name).'">'.$sub_menu_rows[$i]->menu_label.'</a></li>';
   }
   print '</ul>';
   print '</div>';
  }
  
  function select_sub_menu() {
   $content_type = $_GET['page'];
   $this->redirect('edit.php?post_type='.$content_type);
   exit;
  }
  
  function build_url($query = '') {
   
   return admin_url('admin.php?page=cck-main'.$query);
  }
  
  function redirect($url) {
    ?>
    <script language="javascript">
    location.href= "<?php print $url;?>";
    </script>
    <?php
 }
 
 function build_select($array,$sel = '') {
  $opt = array();
  
  foreach($array as $k => $v) {
   
   if($sel == $k) {
    $opt[] = '<option value="'.$k.'" selected="selected">'.$v.'</option>'; 
   }else {
    $opt[] = '<option value="'.$k.'">'.$v.'</option>'; 
   }
   
  }
  return implode("",$opt);
 }
 
 function init() {
  $this->build_content_type();
  $this->create_taxomony();
 }
 
}


new simple_cck();