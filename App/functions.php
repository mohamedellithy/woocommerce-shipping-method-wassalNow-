<?php

/**
 * @package functions.php 
 * Woocommerce Shipping Wassal 
 * Plugin URI: http://wassalnow.com/
*/

require_once WOSW_PLUGIN_DIR.'App/class/class-wosw-api.php';
require_once WOSW_PLUGIN_DIR.'App/class/class-process-wosw-shipment.php';
require_once WOSW_PLUGIN_DIR.'App/class/class-register-wosw-shipment.php';
require_once WOSW_PLUGIN_DIR.'App/class/class-admin-wosw-shipment.php';
require_once WOSW_PLUGIN_DIR.'App/class/class-front-wosw-shipment.php';


/**
 * @package woocommerce_shipping_calculator_enable_city @return true
 * @package woocommerce_shipping_calculator_enable_postcode @return true
 * allow reload and update total price in checkout page 
 **/
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_true' );
add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_true' );


// WooCommerce Checkout Fields Hook
add_filter( 'woocommerce_checkout_fields' , 'wosw_wc_checkout_fields' ,99,1);
// This example changes the default placeholder text for the state drop downs to "Select A State"
function wosw_wc_checkout_fields( $fields ) {

    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $chosen_shipping = $chosen_methods[0]; 
    if ($chosen_shipping == 'wasslnow') {
 
       // connect with shipment method destinations and prices 
         $get_all_destinations_wosw = new wosw_api();
         $city_from_shipping = array('from'=>get_option('wassalnow_api')['destinationFrom']);
         //$get_all_destinations_wosw->Check_Shipping_Prices( $city_from_shipping );
         $options_destination_in_wassalNow = array_combine( array_keys($get_all_destinations_wosw->Check_Shipping_Prices( $city_from_shipping ) ), array_keys($get_all_destinations_wosw->Check_Shipping_Prices( $city_from_shipping ) ) );
       // shipping form city
         $fields['shipping']['shipping_city']['type']    = 'select';
         $fields['shipping']['shipping_city']['options'] = $options_destination_in_wassalNow;
      
       //Billing form city
         $fields['billing']['billing_city']['type']      = 'select';
         $fields['billing']['billing_city']['options']   = $options_destination_in_wassalNow;    }
    
    return $fields;
}


// update form Billing and shipment data after choose payment shipment
add_action('wp_footer','update_checkout',99);
function update_checkout(){ 
    if( !is_cart() ): ?>
      <script> 
          jQuery(document).on('change','.shipping_method',function(){
               setTimeout(function(){
                window.location.reload();
               },2000);
          });
      </script>
    <?php 
    endif;
}

