<?php

// ==================================================================
// Add Colorpicker
// ==================================================================
function fpf_farbtastic_script() {
  wp_enqueue_style( 'farbtastic' );
  wp_enqueue_script( 'farbtastic' );
}
add_action('init', 'fpf_farbtastic_script');

function fpf_enqueue_color_picker() {
  wp_enqueue_style( 'wp-color-picker' );
  wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'fpf_enqueue_color_picker' );


//==================================================================
// Create the Flexible Post Filter Plugin Menu Page 
//==================================================================

add_action('admin_menu', 'filter_posts_plugin_add_menu');

function filter_posts_plugin_add_menu() {

global $jqs_categories, $FPFpluginname, $FPFshortname, $FPFoptions;

	
  if ( isset ( $_GET['page'] ) && ( $_GET['page'] == basename(__FILE__) ) ) {
    if ( isset ($_REQUEST['action']) && ( 'save' == $_REQUEST['action'] ) ){
      foreach ( $FPFoptions as $value ) {
        if ( array_key_exists('id', $value) ) {
          if ( isset( $_REQUEST[ $value['id'] ] ) ) {
            update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
          } else {
            delete_option( $value['id'] );
          }
        }
      }
  foreach ($_REQUEST['my_name'] as $my_name ) {
   if ( isset( $_REQUEST['my_name'] ) ) { 
  	update_option( 'jqs_select_categories', $_REQUEST['my_name'] );
		} else {
	delete_option( 'jqs_select_categories' );
      }
     
    } 
    header("Location: themes.php?page=".basename(__FILE__)."&saved=true");
      die; 
}
    else if ( isset ($_REQUEST['action']) && ( 'reset' == $_REQUEST['action'] ) ) {
      foreach ($FPFoptions as $value) {
        if ( array_key_exists('id', $value) ) {
          delete_option( $value['id'] );
        }
      }
      header("Location: admin.php?page=".basename(__FILE__)."&reset=true");
      die;
    }

  }
  
add_menu_page( __( 'Flexible Post Filter Settings Page','jqs' ), __( 'Post Filter','jqs' ), 'manage_options', basename( __FILE__ ), 'filterpostsplugin_admin');

}

// ==================================================================
// Create the Menu & the Options
// ==================================================================
	

function filterpostsplugin_admin() {

global $jqs_categories, $FPFpluginname, $FPFshortname, $FPFoptions;

 if( isset($_REQUEST['saved']) ) echo '<div id="message" class="updated fade"><p><strong>'.$FPFpluginname.' settings saved.</strong></p></div>';
  if( isset($_REQUEST['reset']) ) echo '<div id="message" class="updated fade"><p><strong>'.$FPFpluginname.' settings reset.</strong></p></div>';
     
//Populate the Plugin Admin Menu with the Options 
?> 

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/includes/style.css" type="text/css" media="screen" />

<div class="wrap">

<h2>Flexible Post Filter - Plugin Options</h2>

<div id="options-form">

<form method="post">

  <ul class="toplist">
    <li><a class="current" href="#filter"><?php _e( 'General Options','jqs' ); ?></a></li>
    <li><a href="#caption"><?php _e( 'Caption Options','jqs' ); ?></a></li>
         <li class="span">
      <input type="submit" name="action" value="<?php esc_attr_e( 'Save Settings','jqs' ); ?>" class="button-primary" />
      <input type="hidden" name="action" value="save" />
    </li>
  </ul>
 
 <div id="FPFslide">
   
<!-- Text input -->
<?php foreach ($FPFoptions as $value) { if ($value['type'] == 'text') { ?>

  <div class="wrap">
    <label for="<?php echo $value['id']; ?>"><h3><?php echo $value['name']; ?></h3></label>
    <input onfocus="this.select();" type="text" id="<?php echo $value['id']; ?>" name="<?php echo $value['id']; ?>" value="<?php echo get_option( $value['id'] );?>" /><br />
    <div class="note"><?php echo $value['note']; ?></div>
  </div><!-- .wrap -->


<!-- Dropdown select -->
<?php } elseif ($value['type'] == 'select') { ?>
  <div class="wrap">
  <div id=multiselect>
    <label for="<?php echo $value['id']; ?>"><h3><?php echo $value['name']; ?></h3></label>
    <select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" class="<?php echo $value['id']; ?>">
      <?php foreach ($value['options'] as $FPFoption) { ?>
      <option <?php if ( get_option( $value['id'] ) == $FPFoption) { echo ' selected="selected"'; } elseif ($FPFoption == $value['std']) { echo ' selected="selected"'; } ?>><?php echo $FPFoption; ?></option>
      <?php } ?>
    </select><br />
    <div class="note"><?php echo $value['note']; ?></div>
  </div>
  </div><!-- .wrap -->


<!-- Checkbox -->
<?php } elseif ($value['type'] == 'checkbox') { ?>
  <div class="wrap">
    <h3><?php echo $value['name']; ?></h3>
    <div class="note"><?php echo $value['note']; ?></div>
    <?php foreach ($value['options'] as $FPFoption) { ?>
      <label for="<?php echo $FPFoption; ?>">
      <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $FPFoption; ?>" value="<?php echo $FPFoption; ?>" <?php if ( get_option( $value['id'] ) == $FPFoption) { echo ' checked="checked"'; } elseif ($FPFoption == $value['std']) { echo ' checked="checked"'; } ?> /> <?php echo $FPFoption; ?>
      </label>
      
    <?php } ?>
  </div><!-- .wrap -->
  
   <!-- Colour picker -->
<?php } elseif ($value['type'] == 'color') { ?>

  <div class="wrap">
    <label for="<?php echo $value['id']; ?>"><h3><?php echo $value['name']; ?></h3></label>
    <label for="<?php echo $value['id']; ?>"><input type="text" class="<?php echo $value['id']; ?>" name="<?php echo $value['id']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo get_option( $value['id'] ); } else { echo $value['std']; } ?>" /></label>
    <div class="<?php echo $value['note']; ?>"></div>
    
  </div>

  <script type="text/javascript">
  jQuery(document).ready(function($) {
    $('.<?php echo $value['id']; ?>').wpColorPicker();
    
  }); // END
  </script>


<!-- Multi Select -->
<?php } elseif ($value['type'] == 'multiselect') { ?>
  
  <div class="wrap">  
  <div class="multiselect">
    <h3><?php echo $value['name']; ?></h3>
    <?php $categories = get_option("jqs_select_categories"); ?>
    <?php foreach ($value['options'] as $FPFoption) { ?>
      <label for="<?php echo $FPFoption; ?>">
      <input type="checkbox" name="my_name[]" id="<?php echo $FPFoption; ?>" value="<?php echo $FPFoption; ?>" 
      <?php if(in_array($FPFoption, $categories)) { echo ' checked="checked"'; } ?> /> <?php echo $FPFoption; ?>
      </label>
    <?php } ?>
  </div>
  </div><!-- .wrap -->
 

<!-- Class -->
<?php } elseif ($value['type'] == 'class') { echo $value['class']; ?>

<!-- Title -->
<?php } elseif ($value['type'] == 'title') { ?><h2><?php echo $value['heading']; ?></h2><h4><?php echo $value['note']; ?></h4>

<!-- Sub-Title -->
<?php } elseif ($value['type'] == 'sub-title') { ?><h5><?php echo $value['heading']; ?></h5><h4><?php echo $value['note']; ?></h4>

<!-- Info -->
<?php } elseif ($value['type'] == 'info') { ?><p><?php echo $value['note']; ?></p>

<!-- Screenshot -->
<?php } elseif ($value['type'] == 'screenshot') { ?><img src="<?php echo get_template_directory_uri(); ?>/includes/images/<?php echo $value['note']; ?>" class="screenshot" alt="" title="" />

<?php } elseif ($value['type'] == 'header') { } }?>

</div><!-- #FPFslide -->

<script type="text/javascript">
jQuery(document).ready(function($) {

 var divWrapper = $('#FPFslide > div');
  divWrapper.hide().filter(':first').show();
    $('ul.toplist li a').click(function () {
      if (this.className.indexOf('current') == -1){
        divWrapper.hide(); divWrapper.filter(this.hash).fadeIn('slow');
          $('ul.toplist li a').removeClass('current');
          $(this).addClass('current');
      }
    return false;
  });

}); // END
</script>


 
<div class="options-buttons">
  <input type="submit" name="action" value="<?php _e('Save settings','jqs'); ?>" class="button-primary alignright" />
  <input type="hidden" name="action" value="save" />
  </form>
    <form method="post">
      <input type="submit" name="action" value="<?php _e('Reset all settings','jqs'); ?>" class="button alignleft" />
      <input type="hidden" name="action" value="reset" />
    </form>
</div>

</div><!-- #options-form -->


<?php
}

global $FPFoptions;
foreach ($FPFoptions as $value) {
  if (isset($value['id']) && get_option( $value['id'] ) === FALSE && isset($value['std'])) {
    $$value['id'] = $value['std'];
  }
  elseif (isset($value['id'])) { $$value['id'] = get_option( $value['id'] ); }
}
function qs_settings($key) {
  global $settings;
  return $settings[$key];
}


?>