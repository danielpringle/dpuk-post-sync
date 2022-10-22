<?php
/**
 * Assets class
 *
 * Methods for enqueueing and printing assets
 * such as JavaScript and CSS files.
 *
 * @package    DPUK_Post_Sync
 * @subpackage Classes
 * @category   Core
 * @since      1.0.0
 */

namespace DPUK_Post_Sync\Classes;

// Restrict direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class EnqueueAssets {

	/**
	 * Plugin version
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string The version number.
	 */
	private $version = DPUKPS_VERSION;

	/**
	 * Plugin version
	 *
	 * @since  1.0.0
	 * @access public
	 * @return string Returns the version number.
	 */
	public function version() {
		return $this->version;
	}

	public function __construct() {

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_plugin_scripts_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_plugin_admin_scripts_styles' ] );
	}

	public function enqueue_plugin_scripts_styles() {

		// Instantiate the Assets class.
		$asset_versioning = new AssetVersioning;
		
		wp_enqueue_script(
			'dpuk-plugin-template-script', 
			DPUKPS_URL . 'assets/js/dpuk-plugin-template' . $asset_versioning->suffix() . '.js',
			[ 'jquery' ],
			$asset_versioning->version_control(),
			true 
		);
	
		wp_enqueue_style( 
			'dpuk-plugin-template-styles',
			DPUKPS_URL . 'assets/css/dpuk-plugin-template' . $asset_versioning->suffix() . '.css',
			array(),
			$asset_versioning->version_control(),
			false 
			);
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_plugin_admin_scripts_styles( $hook ) {
		$screen = get_current_screen();

		// Instantiate the Assets class.
		$asset_versioning = new AssetVersioning;

		if ( 'dashboard' === $screen->id ) {
			
		}

		wp_enqueue_script(
			'dpuk-plugin-template-admin-script', 
			DPUKPS_URL . 'assets/js/dpuk-plugin-template-admin' . $asset_versioning->suffix() . '.js',
			[ 'jquery' ],
			$asset_versioning->version_control(),
			true 
		);
	}

}