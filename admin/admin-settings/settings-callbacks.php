<?php // MyPlugin - Settings Callbacks



// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}


// callback: login section
function dpukpostsync_callback_section_login() {

	echo '<p>Add the details of the site you are syncing with. Requires valid user credentials. This plugin uses a JSON Web Token for authentication.</p>';

}


// callback: text field
function dpukpostsync_callback_field_text( $args ) {

	$options = get_option( 'dpukpostsync_options' );

	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';

	$value = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : '';

	echo '<input id="dpukpostsync_options_'. $id .'" name="dpukpostsync_options['. $id .']" type="text" size="40" value="'. $value .'"><br />';
	echo '<label for="dpukpostsync_options_'. $id .'">'. $label .'</label>';

}

function dpukpostsync_callback_field_checkbox( $args ) {
	
	$options = get_option( 'dpukpostsync_options');
	
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	
	$checked = isset( $options[$id] ) ? checked( $options[$id], 1, false ) : '';
	
	echo '<input id="dpukpostsync_options_'. $id .'" name="dpukpostsync_options['. $id .']" type="checkbox" value="1"'. $checked .'> ';
	echo '<label for="dpukpostsync_options_'. $id .'">'. $label .'</label>';
	
}



