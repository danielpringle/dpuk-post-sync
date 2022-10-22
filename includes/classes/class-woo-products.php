<?php
/**
 * WooCommerce Products
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

class Woo_Products {

	Public function __construct() {
        Root\write_log('Woo product');
		//add_action( "init", array( $this, "initiate_build" )); 
        add_action( "init", array( $this, "product_args" )); 
	} // end __construct


    public function initiate_build(){

      $product_type = 'simple';

      switch ($product_type) {
        case "simple":
          $this->simple_product();
          break;
        case "variable":
          $this->variable_product();
          break;
        default:
          $this->simple_product();
      }

    }

    public function simple_product(){
        echo "load simple as default";

        // Check if product exists


        $product_args = $this->product_args();

        // CRUD object
        $product = new \WC_Product_Simple();

        $product->set_name( 'Wizard Hat' ); // product title

        $product->set_slug( 'medium-size-wizard-hat-in-new-york' );

        $product->set_regular_price($product_args['regular_price']); // in current shop currency

        $product->set_short_description( '<p>Here it is... A WIZARD HAT!</p><p>Only here and now.</p>' );
        // you can also add a full product description
        // $product->set_description( 'long description here...' );

        $product->set_image_id( 90 );

        // let's suppose that our 'Accessories' category has ID = 19 
        $product->set_category_ids( array( 19 ) );
        // you can also use $product->set_tag_ids() for tags, brands etc

        $product->save();

    }

    
    public function variable_product(){
        echo "a variable product";

         // Check if product exists

          // Check if variation exists
    }

    public function product_args(){


        //$get_product_object = wc_get_product_object( 'simple', '2316');
        $product = wc_get_product( 2316);

         //Root\write_log($product);

        // $woo_product = [
        //     'type'               => '', // Simple product by default
        //     'parent_id'          => 0,
        //     'name'               => __("The product title", "dpuk_text_domain"),
        //     'description'        => __("The product description…", "dpuk_text_domain"),
        //     'short_description'  => __("The product short description…", "dpuk_text_domain"),
        //     'sku'                => '',
        //     'regular_price'      => '5.00',
        //     'sale_price'         => '',
        //     'stock_status'       => 'instock',
        //     'stock'              => '', // Set a minimal stock quantity
        //     'image_id'           => '', // optional
        //     'gallery_ids'        => array(), // optional
        //     'reviews_allowed'    => true,
        //     'tax_class'          => '', // optional
        //     'weight'             => '', // optional
        //     'length'             => '',
        //     'width'              => '',
        //     'height'             => '',
        //     'upsell_ids'         => array(),
        //     'cross_sell_ids'     => array(),
        //     'category_ids'       => array(),
        //     'tag_ids'            => array(),
        //     'shipping_class_id'  => '',
        //     'attributes'         => array(
        //         // Taxonomy and term name values
        //         'pa_color' => array(
        //             'term_names' => array('Red', 'Blue'),
        //             'is_visible' => true,
        //             'for_variation' => false,
        //         ),
        //         'pa_size' =>  array(
        //             'term_names' => array('X Large'),
        //             'is_visible' => true,
        //             'for_variation' => false,
        //         ),
        //     ),
        // ];

        $woo_product = [];

        // Get Product General Info
        $product_type               = $product->get_type();
        $product_name               = $product->get_name();
        $product_status             = $product->get_status();
        $product_is_featured        = $product->get_featured();
        $product_catalog_visibility = $product->get_catalog_visibility();
        $product_description        = $product->get_description();
        $product_short_description  = $product->get_short_description();
        $product_sku                = $product->get_sku();
        // Get Product Prices
        $product_price              = $product->get_price();
        $product_regular_price      = $product->get_regular_price();
        $product_sale_price         = $product->get_sale_price();
        $product_date_on_sale_from  = $product->get_date_on_sale_from();
        $product_date_on_sale_to    = $product->get_date_on_sale_to();

  
        if($product_type) {
            $woo_product['type'] = $product_type;
        }
        if($product_name) {
            $woo_product['name'] = $product_name;
        }
        if($product_status) {
            $woo_product['status'] = $product_status;
        }
        if($product_is_featured) {
            $woo_product['featured'] = $product_is_featured;
        }
        if($product_catalog_visibility) {
            $woo_product['catalog_visibility'] = $product_catalog_visibility;
        }
        if($product_description ) {
            $woo_product['description'] = $product_description ;
        }
        if($product_short_description ) {
            $woo_product['short_description'] = $product_short_description ;
        }
        if($product_sku ) {
            $woo_product['sku'] = $product_sku  ;
        }
        if($product_price ) {
            $woo_product['price'] = $product_price  ;
        }
        if($product_regular_price  ) {
            $woo_product['regular_price '] = $product_regular_price   ;
        }
        if($product_sale_price  ) {
            $woo_product['sale_price '] = $product_sale_price   ;
        }
        if($product_date_on_sale_from ) {
            $woo_product['date_on_sale_from'] = $product_date_on_sale_from  ;
        }
        if($product_date_on_sale_to ) {
            $woo_product['date_on_sale_to'] = $product_date_on_sale_to  ;
        }






        Root\write_log($woo_product);
        //return $woo_product;
    }
}

