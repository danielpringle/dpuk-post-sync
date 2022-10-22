<?php
namespace DPUK_Post_Sync\Admin;
use DPUK_Post_Sync\Admin as Admin;

// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	
	exit;
	
}

// add top-level administrative menu
function dpukpostsync_add_toplevel_menu() {
	
	add_menu_page(
		'Post Sync Settings',
		'DPUK Post Sync',
		'manage_options',
		'dpukpostsync',
		'DPUK_Post_Sync\Admin\dpukpostsync_display_settings_page',
		'dashicons-admin-generic',
		null
	);
	
}
add_action( 'admin_menu', __NAMESPACE__ . '\dpukpostsync_add_toplevel_menu' );


