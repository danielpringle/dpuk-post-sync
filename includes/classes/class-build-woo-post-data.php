<?php
/**
 *
 * Methods for building products
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

class Build_Woo_Post_Data {

	public function collect_the_post_data($post_ID, $post, $product) {

        Root\write_log('Class Woo_Post_Data -> collect_settings_data');

        $product = wc_get_product( $post_ID );
       
        //Root\write_log($product->get_children());


        $post_args = (array) $post;

        /**
         *  Product Image 
         */
        $product_image = $product->get_image_id(); // Get main image ID.
        if( $product_image ) {
            $post_args['featured_image'] = get_the_post_thumbnail_url($post_ID);
        }

        /**
         *  Product Image galley - get all urls
         */
        // Get product gallery image ids
        $gallery_image_ids = $product->get_gallery_image_ids();
        // Check if product gallery image ids is not empty
        if ( ! empty($gallery_image_ids) ){
            $gallery_images = array();
            foreach( $gallery_image_ids as $gallery_image_id ) {
                $gallery_images[$gallery_image_id ] = wp_get_attachment_url( $gallery_image_id );
            }
            $post_args['gallery_image'] = $gallery_images;
        }

        $post_metas = get_post_meta($post_ID);
        if( !empty($post_metas) ) {
            foreach ( $post_metas as $meta_key => $meta_value ) {
                if( $meta_key != 'sps_website' ) {
                    $post_args['meta'][$meta_key] = isset($meta_value['0']) ? maybe_unserialize( $meta_value['0'] ) : '';
                }
            }
        }


        $taxonomies = get_object_taxonomies( $post_args['post_type'] );
        if( !empty($taxonomies) ) {
            $taxonomies_data = array();
            foreach ($taxonomies as $taxonomy) {
                $taxonomies_data[$taxonomy] = wp_get_post_terms( $post_ID, $taxonomy );
            }
            $post_args['taxonomies'] = $taxonomies_data;
        }
            



        if ( $product->is_type('variable') ) {

            $post_args['woo_product_type'] = ['variable-product'];

            // foreach ( $product->get_children() as $child_id ) {
            //    $variation = wc_get_product( $child_id ); 
            //    if ( $variation && $variation->exists() ) {
            //     $sku = $variation->get_sku();
            //     $post_args['variation'][$child_id] = $sku;
            //    } 
            // }

            $variations = $product->get_available_variations();
            if ( $variations ) {
                foreach ( $variations as $meta_key => $var_value ) {

                    unset($var_value['image_id']);
                    unset($var_value['[variation_id]']);
                    
                    $post_args['variations'][$meta_key] = [$var_value];
                }
            }
        }


        Root\write_log($post_args);
        
        return $post_args;
	}
}
