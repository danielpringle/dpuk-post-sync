<?php
/**
 * Initialize plugin functionality
 *
 * Loads the text domain for translation and
 * instantiates various classes.
 *
 * @package    DPUK_Post_Sync
 * @subpackage Init
 * @category   Core
 * @since      1.0.0
 */

namespace DPUK_Post_Sync;

// Alias namespaces.
use DPUK_Post_Sync\Classes as Classes;
use DPUK_Post_Sync\Admin as Admin;

// Restrict direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Hook initialization functions.
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\admin_init' );


/**
 * Initialization function
 *
 * Loads PHP classes and text domain.
 * Instantiates various classes.
 * Adds settings link in the plugin row.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function init() {

	// Load plugin text domain.
	load_plugin_textdomain(
		'dpuk-post-sync',
		false,
		dirname( DPUKPS_BASENAME ) . '/languages'
	);

	// If this is in the must-use plugins directory.
	load_muplugin_textdomain(
		'dpuk-post-sync',
		dirname( DPUKPS_BASENAME ) . '/languages'
	);

	/**
	 * Class autoloader
	 *
	 * The autoloader registers plugin classes for later use,
	 * such as running new instances below.
	 */
	require_once DPUKPS_PATH . 'includes/autoloader.php';

	// Settings and core methods.
	new Classes\DPUK_Post_Sync;

	//dev
	//new Classes\Woo_Products;
	
}

/**
 * Admin initialization function
 *
 * Instantiates various classes.
 *
 * @since  1.0.0
 * @access public
 * @global $pagenow Get the current admin screen.
 * @return void
 */
function admin_init() {

/** Add admin menu settings procedual **/
	require_once plugin_dir_path( __FILE__ ) . 'admin/admin-settings/admin-menu.php';
	require_once plugin_dir_path( __FILE__ ) . 'admin/admin-settings/settings-page.php';
	require_once plugin_dir_path( __FILE__ ) . 'admin/admin-settings/settings-register.php';
	require_once plugin_dir_path( __FILE__ ) . 'admin/admin-settings/settings-callbacks.php';


}
