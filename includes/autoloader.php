<?php
/**
 * Register plugin classes
 *
 * The autoloader registers plugin classes for later use.
 *
 * @package    Dashboard_Summary
 * @subpackage Includes
 * @category   Classes
 * @since      1.0.0
 */

namespace DPUK_Post_Sync;

// Restrict direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class files
 *
 * Defines the class directory and file prefix.
 *
 * @since 1.0.0
 * @var   string Defines the class file path.
 */
define( 'DPUKPS_CLASS', DPUKPS_PATH . 'includes/classes/class-' );

/**
 * Array of classes to register
 *
 * @since 1.0.0
 * @var   array Defines an array of class files to register.
 */
define( 'DPUKPS_CLASSES', [
	__NAMESPACE__ . '\Classes\AssetVersioning'          => DPUKPS_CLASS . 'asset-versioning.php',
    __NAMESPACE__ . '\Classes\EnqueueAssets'            => DPUKPS_CLASS . 'enqueue-assets.php',
	__NAMESPACE__ . '\Classes\DPUK_Post_Sync'          => DPUKPS_CLASS . 'dpuk-post-sync.php',
	__NAMESPACE__ . '\Classes\Build_Post_Data'       => DPUKPS_CLASS . 'build-post-data.php',
	__NAMESPACE__ . '\Classes\Build_Woo_Post_Data'           => DPUKPS_CLASS . 'build-woo-post-data.php',
	__NAMESPACE__ . '\Classes\Create_Post'             => DPUKPS_CLASS . 'create-post.php',
	__NAMESPACE__ . '\Classes\Create_Product'             => DPUKPS_CLASS . 'create-product.php',
	__NAMESPACE__ . '\Classes\Image_Uploader'             => DPUKPS_CLASS . 'image-uploader.php',
	// Dev
	__NAMESPACE__ . '\Classes\Woo_Products'             => DPUKPS_CLASS . 'woo-products.php',

] );

/**
 * Autoload class files
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
spl_autoload_register(
	function ( string $class ) {
		if ( isset( DPUKPS_CLASSES[ $class ] ) ) {
			require DPUKPS_CLASSES[ $class ];
		}
	}
);