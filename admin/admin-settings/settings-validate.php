<?php // MyPlugin - Validate Settings
namespace DPUK_Post_Sync\Admin;


// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	
	exit;
	
}

// callback: validate options
function dpukpostsync_callback_validate_options( $input ) {
	
	// dpukpostsync_url
	if ( isset( $input['dpukpostsync_url'] ) ) {
		
		$input['dpukpostsync_url'] = esc_url( $input['dpukpostsync_url'] );
		
	}
	
	// dpukpostsync_username
	if ( isset( $input['dpukpostsync_username'] ) ) {
		
		$input['dpukpostsync_username'] = sanitize_text_field( $input['dpukpostsync_username'] );


		
	}

	// dpukpostsync_password
	if ( isset( $input['dpukpostsync_password'] ) ) {
		
		$input['dpukpostsync_password'] = sanitize_text_field( $input['dpukpostsync_password'] );
		
	}


		// Checkbox
		if ( ! isset( $input['dpukpostsync_checkbox'] ) ) {
		
			$input['dpukpostsync_checkbox'] = null;
			
		}
		
		$input['dpukpostsync_checkbox'] = ($input['dpukpostsync_checkbox'] == 1 ? 1 : 0);


	return $input;
	
}

