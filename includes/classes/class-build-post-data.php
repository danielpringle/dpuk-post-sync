<?php
/**
 * Methods for building posts
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

class Build_Post_Data {

	public function collect_the_post_data($post_ID, $post) {

        Root\write_log('Class Build_Post_Data -> collect_settings_data');

        $post_args = (array) $post;

        if( has_post_thumbnail($post_ID) ) {
            $post_args['featured_image'] = get_the_post_thumbnail_url($post_ID);
         }

         if( !empty($post->post_parent) ) {
            $post_args['post_parent_slug'] = get_post_field("post_name", $post->post_parent);
         }

         $taxonomies = get_object_taxonomies( $post_args['post_type'] );
         if( !empty($taxonomies) ) {
             $taxonomies_data = array();
             foreach ($taxonomies as $taxonomy) {
                 $taxonomies_data[$taxonomy] = wp_get_post_terms( $post_ID, $taxonomy );
             }
             $post_args['taxonomies'] = $taxonomies_data;
         }

         $post_metas = get_post_meta($post_ID);
         if( !empty($post_metas) ) {
             foreach ( $post_metas as $meta_key => $meta_value ) {
                 if( $meta_key != 'sps_website' ) {
                    $post_args['meta'][$meta_key] = isset($meta_value['0']) ? maybe_unserialize( $meta_value['0'] ) : '';
                 }
             }
         }
         return $post_args;
	}
}
