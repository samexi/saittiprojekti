<?php
/**
 * Uninstall file as per WP 2.7+
 *
 * @author LMcGuire, WPinHarmony
 * @copyright Copyright 2014
 * @version 1.0

 */

/**
 * Prevent direct access to this file 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( _( 'Sorry, you are not allowed to access this file directly.' ) );
}

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}