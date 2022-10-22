<?php
/**
 * Create Posts
 *
 * Methods for creating posts.
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

class Create_Post {

	Public function __construct() {

		add_action( "dpuk_after_save_data", array( $this, "dpuk_grab_content_images" ), 10, 2 );

        add_action( "dpuk_after_save_data", array( $this, "grab_avada_images" ), 10, 2 );
    

	} // end __construct

	public function create_post($author, $dpuk_sync_data) {
		Root\write_log('create_post - started ' . time());


        Root\write_log($dpuk_sync_data);
  

            $return = array();

            $dpuk_sync_data['post_author'] = $author->ID;
            $dpuk_sync_data['post_content'] = stripslashes($dpuk_sync_data['post_content']);

            // Check if a post exists and get the ID
            $post_name = $dpuk_sync_data['post_name'];
            $post_type = $dpuk_sync_data['post_type'];

			// Check if the slug already exists and if yes get the post_id
            $post_id = $this->get_post_id_by_slug($post_name, $post_type);

            // Deal with a parent post if there is one
               if( !empty($dpuk_sync_data['post_parent']) && !empty($dpuk_sync_data['post_parent_slug']) ) {
                $parent_post_arg = array(
                  'name'        => $dpuk_sync_data['post_parent_slug'],
                  'post_type'   => $dpuk_sync_data['post_type']
                );

                $parent_post = get_posts($parent_post_arg);
                if($parent_post) { 
                    $dpuk_sync_data['post_parent'] = $parent_post[0]->ID; 
                }
            }

            // Update the post / Create the Post

            // unhook this function so it doesn't loop infinitely
            //remove_action( "save_post", array( $this, "dpuk_post_data" ), 10 , 3 );

            // Allow some content tags
            add_filter( 'wp_kses_allowed_html', array( $this, 'custom_wpkses_post_tags' ) , 10, 2 );

            if( !empty($post_id) ) {
                Root\write_log('look here');

                $post_action = 'edit';
                $dpuk_sync_data['ID'] = $post_id;
                $post_id = wp_update_post( $dpuk_sync_data );
            } else {

                Root\write_log('look here dan');

                $post_action = 'add';
                $post_id = wp_insert_post( $dpuk_sync_data );
            }

            // Remove some content tags like iframe which are allowed above.
            remove_filter( 'wp_kses_allowed_html', array( $this, 'custom_wpkses_post_tags' ) , 10, 2 );

            // re-hook this function
            //add_action( "save_post", array( $this, "dpuk_post_data" ), 10 , 3 );
           
            // Add Taxonomies
            if( isset($dpuk_sync_data['taxonomies']) && !empty($dpuk_sync_data['taxonomies']) ) {
                foreach ($dpuk_sync_data['taxonomies'] as $taxonomy => $texonomy_data) {
                    if( is_taxonomy_hierarchical( $taxonomy ) ) {
                        // For hierarchical taxonomy - Categories
                        if( isset( $texonomy_data ) && !empty( $texonomy_data ) ) {
                            $post_categories = array();
                            foreach ( $texonomy_data as $category ) {
                                $term = term_exists( $category['name'], $taxonomy );
                                if( $term ) {
                                    $post_categories[] = $term['term_id'];
                                } else {
                                    $tag_temp = wp_insert_term( $category['name'], $taxonomy );
                                    $tag_id = $tag_temp['term_id'];
                                    $post_categories[] = $tag_id;
                                }
                            }
                            wp_set_post_terms( $post_id, $post_categories, $taxonomy, false );
                        } else {
                            wp_set_post_terms( $post_id );
                        }
                    } else {
                        // For non-hierarchical taxonomy - Tags
                        if( isset( $texonomy_data ) && !empty( $texonomy_data ) ) {
                            $post_tags = array();
                            foreach ( $texonomy_data as $tag ) {
                                $post_tags[] = $tag['name'];
                            }
                            wp_set_post_terms( $post_id, $post_tags, $taxonomy, false );
                        } else {
                            wp_set_post_terms( $post_id );
                        }
                    }
                }
            }

            // Insert meta data
            if( isset($dpuk_sync_data['meta']) && !empty($dpuk_sync_data['meta']) ) {
                foreach ($dpuk_sync_data['meta'] as $meta_key => $meta_value) {
                    update_post_meta( $post_id, $meta_key, $meta_value );
                }
            }
       
            // Featured Image 1
            // if( isset($dpuk_sync_data['featured_image']) && !empty($dpuk_sync_data['featured_image']) ) {

            //     $image_url        = 'http://developer.localhost/wp-content/uploads/2022/09/download.jpg'; //$dpuk_sync_data['featured_image'];

            //     $uploadFT = new FT_Image();
            //     $uploadFT_run = $uploadFT->get_ft_img($post_id, $image_url);

            // }

             // Product Image
             if( isset($dpuk_sync_data['gallery_image']) && !empty($dpuk_sync_data['gallery_image']) ) {

                // $this->woo_product_images($post_id);
                $galleryImages = array();
                foreach( $dpuk_sync_data['gallery_image'] as $gallery_image ) {
                    //write_log($gallery_image);

                    // $image_url = $gallery_image;

                    // $upload_dir = wp_upload_dir();
                    
                    // $image_data = file_get_contents( $gallery_image );
                    
                    // $filename = basename( $gallery_image );



                    // // if ( null == ( $thumb_id = $this->does_file_exists( $filename ) ) ) {

                    // //     write_log('hummm....seems like we have never seen this file name before, let us do an upload');
                    
                    // //   } else {
                    // //     write_log('the file already exists ' . $thumb_id);
                    // //   }


                    // // check if attachment already exists
                    // $attachment_args = array(
                    //     'posts_per_page' => 1,
                    //     'post_type'      => 'attachment',
                    //     'name'           => $filename
                    // );
                    // $attachment_check = new Wp_Query( $attachment_args );

                    // // if attachment exists, reuse and update data
                    // if ( $attachment_check->have_posts() ) {

                    //     Root\write_log('Attachment <strong>'. $filename .'</strong> found, omitting download...<br>');

                    //     // do stuff..

                    // // if attachment doesn't exist fetch it from url and save it
                    // } else {

                    //     write_log('Attachment <strong>'. $filename  .'</strong> not found, downloading...<br>');

                    //     if ( wp_mkdir_p( $upload_dir['path'] ) ) {
                    //         $file = $upload_dir['path'] . '/' . $filename;
                    //       }
                    //       else {
                    //         $file = $upload_dir['basedir'] . '/' . $filename;
                    //       }
                          
                    //       file_put_contents( $file, $image_data );
                          
                    //       $wp_filetype = wp_check_filetype( $filename, null );
                          
                    //       $attachment = array(
                    //         'post_mime_type' => $wp_filetype['type'],
                    //         'post_title' => sanitize_file_name( $filename ),
                    //         'post_content' => '',
                    //         'post_status' => 'inherit'
                    //       );
                          
                    //       $attach_id = wp_insert_attachment( $attachment, $file );
                    //       require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    //       $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                    //       wp_update_attachment_metadata( $attach_id, $attach_data );
      
                    //       $galleryImages[$gallery_image] = $attach_id;
                    // }

                    $upload_gallery_image = new Image_Uploader;
                    $upload_gallery_image_id = $upload_gallery_image->upload_image($gallery_image);
                    $galleryImages[$gallery_image] = $upload_gallery_image_id;
                }
                Root\write_log($galleryImages);
                $product_id = $post_id;
                update_post_meta($product_id, '_product_image_gallery', implode(',',$galleryImages)); 
            }
            
           // Featured Image
            if( isset($dpuk_sync_data['featured_image']) && !empty($dpuk_sync_data['featured_image']) ) {
                
                $image_url        = $dpuk_sync_data['featured_image'];
                $upload_featured_image = new Image_Uploader;
                $upload_featured_image_id = $upload_featured_image->upload_image($image_url);
                $data = set_post_thumbnail( $post_id, $upload_featured_image_id );

                // $image_arr        = explode( '/', $dpuk_sync_data['featured_image'] );
                // $image_name       = end($image_arr);
                // $upload_dir       = wp_upload_dir();
                // $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
                // $filename         = basename( $unique_file_name );

                // // Check folder permission and define file location
                // if( wp_mkdir_p( $upload_dir['path'] ) ) {
                //     $file = $upload_dir['path'] . '/' . $filename;
                // } else {
                //     $file = $upload_dir['basedir'] . '/' . $filename;
                // }

                // // Create the image  file on the server
                // $this->grab_image( $image_url, $file);

                // // Check image file type
                // $wp_filetype = wp_check_filetype( $filename, null );

                // // Set attachment data
                // $attachment = array(
                //     'post_mime_type' => $wp_filetype['type'],
                //     'post_title'     => sanitize_file_name( $filename ),
                //     'post_content'   => '',
                //     'post_status'    => 'inherit'
                // );

                // // Create the attachment
                // $attach_id = wp_insert_attachment( $attachment, $file, $post_id );

                // // Include image.php
                // require_once(ABSPATH . 'wp-admin/includes/image.php');

                // // Define attachment metadata
                // $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

                // // Assign metadata to attachment
                // wp_update_attachment_metadata( $attach_id, $attach_data );

                // // And finally assign featured image to post
                // $data = set_post_thumbnail( $post_id, $attach_id );
            }

            do_action( 'dpuk_after_save_data', $post_id, $dpuk_sync_data );

            $return['status'] = __('success', 'dpuk-post-sync');
            $return['msg'] = __('Data proccessed successfully', 'dpuk-post-sync');
            $return['post_id'] = $post_id;
            $return['post_action'] = $post_action;

            return $return;
	}


	public function get_post_id_by_slug($post_name, $post_type) {
		Root\write_log('get_post_id_by_slug');
		
		$args = array(
		  'name'        => $post_name,
		  'post_type'   => $post_type,
		  'numberposts' => 1
		);
		$post_id = get_posts($args);

		if (!empty($post_id[0]->ID)) {
			Root\write_log('get_post_id_by_slug = '.$post_id[0]->ID);
			return $post_id[0]->ID;
		} else {
			Root\write_log('get_post_id_by_slug = 0');
			return $post_id = '';
		}

		return $post_id[0]->ID;            
	}

	function grab_image($url,$saveto){

		Root\write_log($url);

		$data = wp_remote_request($url);

		if( isset( $data['body'] ) && isset( $data['response']['code'] ) && !empty( $data['response']['code'] ) ) {
			$raw = $data['body'];
			if(file_exists($saveto)){
				unlink($saveto);
			}
			$fp = fopen($saveto,'x');
			fwrite($fp, $raw);
			fclose($fp);
		}
	}

	function custom_wpkses_post_tags( $tags, $context ) {
		if ( 'post' === $context ) {
			$tags['iframe'] = array(
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
			);

			$tags['embed'] = array(
				'type'   => true,
				'src'    => true,
				'height' => true,
				'width'  => true,
			);
		}

		return $tags;
	}

	function dpuk_grab_content_images( $post_id, $dpuk_sync_data ) {
		$post_content = stripslashes($dpuk_sync_data['post_content']);

		preg_match_all('/<img[^>]+>/i', $post_content, $images_tag);

		if( isset($images_tag[0]) && !empty($images_tag[0]) ) {
			foreach ($images_tag[0] as $img_tag) {
				preg_match_all('/(alt|title|src)=("[^"]*")/i', $img_tag, $img_data);
				if( isset($img_data[2][0]) && !empty($img_data[2][0]) && isset($img_data[1][0]) && $img_data[1][0] == 'src' ) {
					$image_url = str_replace( '"', '', $img_data[2][0] );

					// check image is exists
					$args = array(
						'post_type' => 'attachment',
						'post_status' => 'inherit',
						'meta_query' => array(
							array(
								'key'       => 'old_site_url',
								'value'     => $image_url,
								'compare'   => '='
							),
						),
					);

					$attachment = new \WP_Query( $args );

					if( empty($attachment->posts) ) {

                        $upload_content_image = new Image_Uploader;
                        $attach_id = $upload_content_image->upload_image($image_url);
                        update_post_meta( $attach_id, 'old_site_url', $image_url );

						// $image_arr        = explode( '/', $image_url );
						// $image_name       = end($image_arr);
						// $upload_dir       = wp_upload_dir();
						// $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
						// $filename         = basename( $unique_file_name );

						// // Check folder permission and define file location
						// if( wp_mkdir_p( $upload_dir['path'] ) ) {
						// 	$file = $upload_dir['path'] . '/' . $filename;
						// } else {
						// 	$file = $upload_dir['basedir'] . '/' . $filename;
						// }

						// // Create the image  file on the server
						// $this->grab_image( $image_url, $file);

						// // Check image file type
						// $wp_filetype = wp_check_filetype( $filename, null );

						// // Set attachment data
						// $attachment = array(
						// 	'post_mime_type' => $wp_filetype['type'],
						// 	'post_title'     => sanitize_file_name( $filename ),
						// 	'post_content'   => $image_url,
						// 	'post_status'    => 'inherit'
						// );
						
						// $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
						// update_post_meta( $attach_id, 'old_site_url', $image_url );
					} else {
						$attachment_posts = $attachment->posts;
						$attach_id = $attachment_posts[0]->ID;
					}

					$new_image_url = wp_get_attachment_url( $attach_id );
					$post_content = str_replace($image_url, $new_image_url, $post_content);
				}
			}
			
			wp_update_post( array( 'ID' => $post_id, 'post_content' => $post_content ) );
		}
	}


    function grab_avada_images( $post_id, $dpuk_sync_data ) {
		$post_content = stripslashes($dpuk_sync_data['post_content']);

        Root\write_log('avada');

        //Root\write_log($post_content);


        $domain = isset($dpuk_sync_data['dpuk_sync']['parent_domain']) ? sanitize_text_field($dpuk_sync_data['dpuk_sync']['parent_domain']) : '';
    
        Root\write_log( $domain);
        
        $pattern = '/http(s)?:\/\/'.$domain.'\/wp-content\/.*?\.[a-zA-Z.]{2,5}/i';
        

        preg_match_all($pattern, $post_content, $avada_urls, PREG_SET_ORDER);

        Root\write_log($avada_urls);

        if( isset($avada_urls[0]) && !empty($avada_urls[0]) ) {
			foreach ($avada_urls as $avada_url) {
                $image_url = $avada_url[0];
                Root\write_log('$image_url');
                Root\write_log($image_url);
                // check image is exists
                $args = array(
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'meta_query' => array(
                        array(
                            'key'       => 'old_site_url',
                            'value'     => $image_url,
                            'compare'   => '='
                        ),
                    ),
                );

                $attachment = new \WP_Query( $args );

                if( empty($attachment->posts) ) {

                    $upload_content_image = new Image_Uploader;
                    $attach_id = $upload_content_image->upload_image($image_url);
                    update_post_meta( $attach_id, 'old_site_url', $image_url );

                    // $image_arr        = explode( '/', $image_url );
                    // $image_name       = end($image_arr);
                    // $upload_dir       = wp_upload_dir();
                    // $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
                    // $filename         = basename( $unique_file_name );

                    // // Check folder permission and define file location
                    // if( wp_mkdir_p( $upload_dir['path'] ) ) {
                    //     $file = $upload_dir['path'] . '/' . $filename;
                    // } else {
                    //     $file = $upload_dir['basedir'] . '/' . $filename;
                    // }

                    // // Create the image  file on the server
                    // $this->grab_image( $image_url, $file);

                    // // Check image file type
                    // $wp_filetype = wp_check_filetype( $filename, null );

                    // // Set attachment data
                    // $attachment = array(
                    //     'post_mime_type' => $wp_filetype['type'],
                    //     'post_title'     => sanitize_file_name( $filename ),
                    //     'post_content'   => $image_url,
                    //     'post_status'    => 'inherit'
                    // );
                    
                    // $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
                    // update_post_meta( $attach_id, 'old_site_url', $image_url );
                } else {
                    $attachment_posts = $attachment->posts;
                    $attach_id = $attachment_posts[0]->ID;
                }

                $new_image_url = wp_get_attachment_url( $attach_id );
                $post_content = str_replace($image_url, $new_image_url, $post_content);
            }
            wp_update_post( array( 'ID' => $post_id, 'post_content' => $post_content ) );
        }

	
	}


}
