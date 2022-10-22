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
// Alias namespaces.
use DPUK_Post_Sync as Root;

// Restrict direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class DPUK_Post_Sync {
	
	/**
	 * 
	 */
    public function __construct() {

		add_action( "save_post", array( $this, "get_post_data" ), 10 , 3 );

		add_action( "init", array( $this, "get_sync_post_request" ) );
     
    }

	/**
	 * 
	 */
	public function get_post_data($post_ID, $post) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

		$options = get_option( 'dpukpostsync_options');

		if ( isset( $options['dpukpostsync_checkbox'] ) && ! empty( $options['dpukpostsync_checkbox'] ) ) {

			$is_parent_site = true;

		} else {

			$is_parent_site = false;
		}
		$status_not = array('auto-draft', 'trash', 'inherit', 'draft');
		if( !in_array($post->post_status, $status_not) && $is_parent_site == true ) {

			$current_post_type = get_post_type( $post_ID);

			Root\write_log($current_post_type);

			if( $current_post_type == 'product' ) {

				$post_args = new Build_Woo_Post_Data;
				$post_args = $post_args->collect_the_post_data($post_ID, $post, $product);

			} else {
				$post_args = new Build_Post_Data;
				$post_args = $post_args->collect_the_post_data($post_ID, $post);
			}

			return $this->get_admin_settings($post_args);

		} else {
			return;
		}
	}

	/**
	 * Build the settings data to send
	 */
	function get_admin_settings($post_args = array()) {
		Root\write_log('dpuk_post_data - started');

		$options = get_option( 'dpukpostsync_options');

		if ( isset( $options['dpukpostsync_parent_url'] ) && ! empty( $options['dpukpostsync_parent_url'] ) ) {
			$post_args['dpuk_sync']['parent_domain'] = $options['dpukpostsync_parent_url'];
		}

		if ( isset( $options['dpukpostsync_url'] ) && ! empty( $options['dpukpostsync_url'] ) ) {
			$post_args['dpuk_sync']['synced_site_domain'] = esc_url( $options['dpukpostsync_url'] );
		}

		if ( isset( $options['dpukpostsync_password'] ) && ! empty( $options['dpukpostsync_password'] ) ) {
			$post_args['dpuk_sync']['content_password'] = $options['dpukpostsync_password'];
		}

		if ( isset( $options['dpukpostsync_username'] ) && ! empty( $options['dpukpostsync_username'] ) ) {
			$post_args['dpuk_sync']['content_username']  = $options['dpukpostsync_username'];
		}
		
		return $this->run_remote_post($post_args);
	}

	/**
	 * Send the POST request with the data
	 */
	public function run_remote_post($post_args = array()) {
		Root\write_log('dpuk_remote_post - started');

		$parent_data = json_decode(json_encode($post_args), true);
		
		Root\write_log($parent_data);

		$dpuk_action = 'create_update_post';
		
		$domain = $parent_data['dpuk_sync']['synced_site_domain'];

		$url = $domain.'/?dp_action='.$dpuk_action;

		Root\write_log($url);

		$response =  wp_remote_post( $url, array( 'body' => $parent_data ));

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();

			Root\write_log('dpuk_remote_post - bad response: ' . $error_message);

		} else {

		$apiBody = json_decode( wp_remote_retrieve_body( $response ) );

		Root\write_log('dpuk_remote_post - response is good');

		}

		return $response;
	}

	/**
	 * Get the post request from the parent site
	 */
	public function get_sync_post_request() {
		Root\write_log('dpuk_get_request');
		if( isset($_REQUEST['dp_action']) && !empty($_REQUEST['dp_action']) ) {

			$dpuk_sync_data = $_REQUEST;

			$dpuk_content_username = isset($dpuk_sync_data['dpuk_sync']['content_username']) ? sanitize_text_field($dpuk_sync_data['dpuk_sync']['content_username']) : '';
			$dpuk_content_password = isset($dpuk_sync_data['dpuk_sync']['content_password']) ? sanitize_text_field($dpuk_sync_data['dpuk_sync']['content_password']) : '';
			$dpuk_action           = isset($dpuk_sync_data['dp_action']) ? 'dpuk_'.sanitize_text_field($dpuk_sync_data['dp_action']) : '';
			

			Root\write_log('dpuk_get_request - passed with action');

			$return = array();
			if( !empty($dpuk_content_username) && !empty($dpuk_content_password) ) {

				$author = wp_authenticate( $dpuk_content_username, $dpuk_content_password );

				if( isset($author->ID) && !empty($author->ID) ) {
					if( isset($dpuk_sync_data['ID']) ) {
						unset($dpuk_sync_data['ID']);
					}
					if( isset($dpuk_sync_data['guid']) ) {
						unset($dpuk_sync_data['guid']);
					}
					$return['status'] = __('success', 'dpuk-post-sync');
					$return['msg'] = __('Authenitcate successfully.', 'dpuk-post-sync');
				}
			} else {
				$return['status'] = __('failed', 'dpuk-post-sync');
				$return['msg'] = __('Username or Password is null.', 'dpuk-post-sync');
			}

			call_user_func( array( $this, $dpuk_action  ), $author, $dpuk_sync_data );
			Root\write_log($return);
			// echo json_encode( $return );
			exit;
		} else {
			return;
		}
	}

	/**
	 * Get the post request from the parent site
	 */
    public function dpuk_create_update_post($author, $dpuk_sync_data) {

		$options = get_option( 'dpukpostsync_options');

		if ( isset( $options['dpukpostsync_checkbox'] ) && ! empty( $options['dpukpostsync_checkbox'] ) ) {

			$is_parent_site = true;

		} else {

			$is_parent_site = false;
		}

		if( $is_parent_site == true ) {

			return;

		} else {

			if($dpuk_sync_data['post_type'] == 'product'){
				$build_post = new Create_Product;
				$build_post = $build_post->create_post($author, $dpuk_sync_data);
				Root\write_log($build_post);
			} else{
				$build_post = new Create_Post;
				$build_post = $build_post->create_post($author, $dpuk_sync_data);
			}

		}
	}

}
