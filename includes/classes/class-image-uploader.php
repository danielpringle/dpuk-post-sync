<?php
/**
 * Image Uploader
 *
 *
 * @package    
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

class Image_Uploader {


	public function upload_image($image_url){

		$upload_dir = wp_upload_dir();
							
		$image_data = file_get_contents( $image_url );

		$filename = basename( $image_url );

		// check if attachment already exists
		$attachment_args = array(
			'posts_per_page' => 1,
			'post_type'      => 'attachment',
			'name'           => $filename
		);
		$attachment_check = new \Wp_Query( $attachment_args );

		// if attachment exists, reuse and update data
		if ( $attachment_check->have_posts() ) {

			$image_id = $attachment_check->posts[0]->ID;

			return $image_id;

		} else {

			//Root\write_log('Attachment <strong>'. $filename  .'</strong> not found, downloading...<br>');
			// Create the image  file on the server
			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			}
			else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}
			
			file_put_contents( $file, $image_data );
			
			// Check image file type
			$wp_filetype = wp_check_filetype( $filename, null );
			
			// Set attachment data
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => sanitize_file_name( $filename ),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			// Create the attachment
			$attach_id = wp_insert_attachment( $attachment, $file );
			// Include image.php
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			// Return the image attachment id
			$image_id = $attach_id;

			return $image_id;
		}
	}
}