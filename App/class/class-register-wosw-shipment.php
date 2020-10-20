<?php 

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function wasslnow_shipping_method() {
        if ( ! class_exists( 'WassalNow_Shipping_Method' ) ){
            class WassalNow_Shipping_Method extends WC_Shipping_Method {

                public $wassalnow_api;
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    // here connect 
                    $this->wassalnow_api      = new wosw_api();
                    
                    // init primary data of shipping payment           
                    $this->id                 = 'wasslnow'; 
                    $this->method_title       = __( 'WassalNow Shipping', 'wasslnow' );  
                    $this->method_description = __( 'Custom Shipping Method for WassalNow', 'wasslnow' ); 
 

                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'EG', // Egypt Country
                    );
 
                    // init form fields and settings
                    $this->init();
 
                    // set enable and title
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';

                    $this->title   = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'WassalNow Shipping', 'wasslnow' );


                    $this->title   = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'WassalNow Shipping', 'wassalnow' );
                    
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'WassalNow Shipping', 'wassalnow' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
                    
                    //set senderid and senderApiKey values
                    $this->wassalnow_api::$senderId     = $this->settings['senderId'];
                    $this->wassalnow_api::$senderApiKey = $this->settings['senderApiKey'];
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                    
                    // here init wosw shipment
                    update_option('wassalnow_api',$this->settings);
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
                         'enabled' => array(
                              'title' => __( 'Enable', 'wasslnow' ),
                              'type' => 'checkbox',
                              'description' => __( 'Enable this shipping.', 'wasslnow' ),
                              'default' => 'yes'
                          ),
                         'title' => array(
                             'title' => __( 'Title', 'wasslnow' ),
                              'type' => 'text',
                              'description' => __( 'Title to be display on site', 'wasslnow' ),
                              'default' => __( 'WassalNow Shipping', 'wasslnow' )
                          ),
                         'senderId' => array(
                              'title' => __( 'senderId ', 'wasslnow' ),
                              'type' => 'text',
                              'description' => __( 'senderId  to be display on site', 'wasslnow' ),
                              'default' => __( '', 'wasslnow' )
                          ),
                         'senderApiKey' => array(
                              'title' => __( 'senderApiKey ', 'wasslnow' ),
                              'type' => 'text',
                              'description' => __( 'senderApiKey  to be display on site', 'wasslnow' ),
                              'default' => __( '', 'wasslnow' )
                          ),
                          'destinationFrom' => array(
                              'title' => __( 'From', 'wassalnow' ),
                              'type' => 'select',
                              'description' => __( 'shipping from ', 'wassalnow' ),
                              'default' => __( '', 'wassalnow' ),
                              'options' => array('Alexandria'=>'Alexandria','Cairo'=>'Cairo','Giza'=>'Giza'),
                          ),
                    );
 
                }
 
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package ) {
                    // price shipment array
                    $city_from_shipping = array('from'=>$this->settings['destinationFrom']);
                    $price_shipment     = $this->wassalnow_api->Check_Shipping_Prices($city_from_shipping);                    
                    $rate = array(
                      'label' => $this->title,
                      'cost'  => $price_shipment[ $package['destination']['city'] ] ?? 0 ,
                      'calc_tax' => 'per_item',
                    );

                    // Register the rate
                    $this->add_rate( $rate );

                   
                    
                }
            }
        }
    }
    add_filter( 'woocommerce_shipping_methods', 'add_wasslnow_shipping_method',10 );
    

    // add shipment WassalNow shipment 
    add_action( 'woocommerce_shipping_init', 'wasslnow_shipping_method' ,10);
    function add_wasslnow_shipping_method( $methods ) {
        $methods[] = 'WassalNow_Shipping_Method';
        return $methods;
    }
 

}

