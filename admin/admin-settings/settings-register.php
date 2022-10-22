<?php
namespace DPUK_Post_Sync\Admin;


// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}


// register plugin settings
function dpukpostsync_register_settings() {

	/*

	register_setting(
		string   $option_group,
		string   $option_name,
		callable $sanitize_callback = ''
	);

	*/

	register_setting(
		'dpukpostsync_options',
		'dpukpostsync_options',
		'dpukpostsync_callback_validate_options'
	);

	/*

	add_settings_section(
		string   $id,
		string   $title,
		callable $callback,
		string   $page
	);

	*/

	add_settings_section(
		'dpukpostsync_section_login',
		'Synced site:',
		'dpukpostsync_callback_section_login',
		'dpukpostsync'
	);



	/*

	add_settings_field(
    string   $id,
		string   $title,
		callable $callback,
		string   $page,
		string   $section = 'default',
		array    $args = []
	);

	*/

	add_settings_field(
		'dpukpostsync_parent_url',
		'URL',
		'dpukpostsync_callback_field_text',
		'dpukpostsync',
		'dpukpostsync_section_login',
		[ 'id' => 'dpukpostsync_parent_url', 'label' => 'URL for the synced site' ]
	);

	add_settings_field(
		'dpukpostsync_url',
		'URL',
		'dpukpostsync_callback_field_text',
		'dpukpostsync',
		'dpukpostsync_section_login',
		[ 'id' => 'dpukpostsync_url', 'label' => 'URL for the synced site' ]
	);

	add_settings_field(
		'dpukpostsync_username',
		'Username',
		'dpukpostsync_callback_field_text',
		'dpukpostsync',
		'dpukpostsync_section_login',
		[ 'id' => 'dpukpostsync_username', 'label' => 'Username' ]
	);

	add_settings_field(
		'dpukpostsync_password',
		'Password',
		'dpukpostsync_callback_field_text',
		'dpukpostsync',
		'dpukpostsync_section_login',
		[ 'id' => 'dpukpostsync_password', 'label' => 'Password' ]
	);

	add_settings_field(
		'dpukpostsync_checkbox',
		'Is Parent',
		'dpukpostsync_callback_field_checkbox',
		'dpukpostsync', 
		'dpukpostsync_section_login',
		[ 'id' => 'dpukpostsync_checkbox', 'label' => 'Select if parent site' ]
	);

}
add_action( 'admin_init', __NAMESPACE__ . '\dpukpostsync_register_settings' );