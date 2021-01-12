<?php
/**
 * Plugin Name: Contact Form 7 Summary and Print
 * Version: 1.2.1
 * Description: This plugin helps you to view summary of contact form 7 form with a print summary button. Users can view their form's summary of all the fields which they have entered during form submission with the Print button at the bottom so they can easily print out their form summary.
 * Author: Muhammad Rehman
 * Author URI: https://muhammadrehman.com
 * Text Domain: CF7_SP
 *
 * @package cf7-summary-and-print
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CF7_SP_VERSION', '1.2.1' );
define( 'CF7_SP_SLUG', 'CF7_SP' );
define( 'CF7_SP_TEXTDOMAIN', 'CF7_SP' );

define( 'CF7_SP_THIS', __FILE__ );
define( 'CF7_SP_ROOT_DIR', plugin_dir_path( CF7_SP_THIS ) );
define( 'CF7_SP_DIR', CF7_SP_ROOT_DIR . 'assets/' );
define( 'CF7_SP_INCLUDES_DIR', CF7_SP_ROOT_DIR . 'includes/' );

/**
 * Load plugin files
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_load() {

	require_once CF7_SP_INCLUDES_DIR . 'admin/class-cf7-sp-settings.php';
	require_once CF7_SP_INCLUDES_DIR . 'class-cf7-sp-summary-print.php';
}

cf7_sp_load();
