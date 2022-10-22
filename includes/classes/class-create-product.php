<?php
/**
 * Methods for creating Products
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
use WC_Product_variation;

// Restrict direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Create_Product {

	Public function __construct() {

		add_action( "dpuk_after_save_data", array( $this, "dpuk_grab_content_images" ), 10, 2 );

	} // end __construct

	public function create_post($author, $dpuk_sync_data) {
		Root\write_log('create_product - started');

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

            // Allow some content tags
            add_filter( 'wp_kses_allowed_html', array( $this, 'custom_wpkses_post_tags' ) , 10, 2 );

            if( !empty($post_id) ) {
                $post_action = 'edit';
                $dpuk_sync_data['ID'] = $post_id;
                $post_id = wp_update_post( $dpuk_sync_data );
            } else {
                

                $post_action = 'add';
                $post_id = wp_insert_post( $dpuk_sync_data );

                Root\write_log('insert ' . $post_id);
            }

            // Remove some content tags like iframe which are allowed above.
            remove_filter( 'wp_kses_allowed_html', array( $this, 'custom_wpkses_post_tags' ) , 10, 2 );

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
             // Variable products
            if( isset($dpuk_sync_data['woo_product_type']) && !empty($dpuk_sync_data['woo_product_type']) ) {

                if($dpuk_sync_data['woo_product_type'] = 'variable-product'){
                    
                    // Add product variations
                    $variations = $dpuk_sync_data['variations'];

                    foreach ( $variations as $variation ) {

                        // Check if a variation exists by slug
                        $the_child_id = wc_get_product_id_by_sku($variation[0]['sku']);
                        if(!$the_child_id == '0'){

                            //update variation
                            Root\write_log('existing sku: ' . $variation[0]['sku'] . ' and id ' . $the_child_id);

                            // We need to turn the array into an object to update the variation
                            $object = json_decode(json_encode($variation[0]));
     
                            $variation = wc_get_product_object( 'variation', $the_child_id );
      
                            $variation->set_props(
                                array(
                                    'regular_price' => $object->display_regular_price,
                                    'sale_price' => $object->display_price,
                                    'description' => $object->variation_description,
                                    'weight' => $object->variation_description,
                                    'width' => $object->variation_description,
                                    'height' => $object->variation_description,
                                    'length' => $object->variation_description,
                                    'shipping_class_id' => $object->variation_description,
                                    'stock_status' => $object->variation_description,
                                    )
                                );
                            $variation->save();

                        } else {
                            $product_variation = new WC_Product_Variation();
                            $product_variation->set_parent_id( $post_id );
                            // Iterating through the variations attributes
                            foreach ($variation[0]['attributes'] as $attribute => $term_name ){

                                $product_variation->set_attributes( array( $attribute => $term_name ) );
                            }

                            // SKU
                            if( ! empty( $variation[0]['sku'] ) ){
                                $product_variation->set_sku( $variation[0]['sku'] );
                            }

                            // Description
                            if( ! empty( $variation[0]['variation_description'] ) ){

                                $variation_description = sanitize_textarea_field( $variation[0]['variation_description']);
                                $product_variation->set_description($variation_description);
                            }

                            // Variation Image
                            if( ! empty( $variation[0]['image']['url'] ) ){
                                $variation_img_url = $variation[0]['image']['url'];
                                $grab_variation_image = new Image_Uploader;
                                $variation_img_id = $grab_variation_image->upload_image($variation_img_url);
                                $product_variation->set_image_id($variation_img_id);
                            }

                            // Prices
                            if( empty( $variation[0]['display_price'] ) ){
                                $product_variation->set_price( $variation[0]['display_regular_price'] );
                            } else {
                                $product_variation->set_price( $variation[0]['display_price'] );
                                $product_variation->set_sale_price( $variation[0]['display_price'] );
                            }
                            $product_variation->set_regular_price( $variation[0]['display_regular_price']); 

                            // Description
                            if( ! empty( $variation[0]['variation_description'] ) ){
                                $product_variation->set_description($variation[0]['variation_description']);
                            }

                            $product_variation->save();

                        }
                    }
                }
            }
       
             // Upload and set gallery Images
             if( isset($dpuk_sync_data['gallery_image']) && !empty($dpuk_sync_data['gallery_image']) ) {

                $galleryImages = array();
                foreach( $dpuk_sync_data['gallery_image'] as $gallery_image ) {

                    $upload_gallery_image = new Image_Uploader;
                    $upload_gallery_image_id = $upload_gallery_image->upload_image($gallery_image);
                    $galleryImages[$gallery_image] = $upload_gallery_image_id;

                }

                $product_id = $post_id;
                update_post_meta($product_id, '_product_image_gallery', implode(',',$galleryImages)); 
            }
            
           // Featured Image / Product Image
            if( isset($dpuk_sync_data['featured_image']) && !empty($dpuk_sync_data['featured_image']) ) {
                
                $image_url        = $dpuk_sync_data['featured_image'];

                $upload_featured_image = new Image_Uploader;
                $upload_featured_image_id = $upload_featured_image->upload_image($image_url);
                $data = set_post_thumbnail( $post_id, $upload_featured_image_id );

            }

            do_action( 'dpuk_after_save_data', $post_id, $dpuk_sync_data );

            $return['status'] = __('success', 'dpuk-post-sync');
            $return['msg'] = __('Data proccessed successfully', 'dpuk-post-sync');
            $return['post_id'] = $post_id;
            $return['post_action'] = $post_action;
            $return['post-type'] = $dpuk_sync_data['post_type'];
            $return['product-type'] = $dpuk_sync_data['woo_product_type'];

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
			Root\write_log('get_post_id_by_slug = '.$post_id[0]->ID);
			return $post_id = '';
		}
		return $post_id[0]->ID;            
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
}
