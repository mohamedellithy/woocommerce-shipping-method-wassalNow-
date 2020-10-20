<?php 

/**
 * @package admin display wosw shipment
 * init settings for install shipping method
 **/
if ( ! defined( 'WPINC' ) ) {
    die; 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	class admin_wosw_shipment extends process_wosw_shipment{

	  	function __construct(){

            $this->process_wosw_shipment_init();

	  		// Display a custom field value on the admin order edit page
            add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this,'display_trackNo_And_shipmentID'), 10, 1 );
            
            //here add styles and scripts for show trackorder shipment
            add_action('admin_enqueue_scripts',array($this,'styles_and_scripts_wosw'));

            // in case order status completed 
            add_action('woocommerce_order_status_completed', array($this,'wosw_order_status_complete'));
            
            // in case order status cancelled
            add_action( 'woocommerce_order_status_cancelled', array($this,'wosw_order_status_cancelled'));


           
            // ADDING  NEW COLUMN trackorder for wassalNow
            add_filter( 'manage_edit-shop_order_columns', array($this,'custom_track_order_wassalNow_column'), 20 );
            
            // Adding custom fields meta data for each new column (example)
            add_action( 'manage_shop_order_posts_custom_column' , array($this,'custom_orders_list_trackorder_column_content'), 20, 2 );

            add_action('admin_footer',array($this,'wassalNow_add_admin_styles_scripts'));
      
	  	}
          
        /**
         * display-trackNo-And-shipmentID in admin add contents to order 
         *
         * @access public
         * @return string
         */
        function display_trackNo_And_shipmentID( $order ){ 
             
             $this->order_id = $order->get_id();

            // if shipping method is wasslnow
            if('wasslnow' == $this->get_shipping_method_id() ):
                 echo "<img src='https://wassalnow.com/wp-content/uploads/2020/04/WNlogo-1.png' width='100px' height='100px' /><br/>".
                      "<label>Shipping method : </label><a href='#'> ".$this->get_shipping_method_id()."</a><br/>".
                      "<label>Shippment ID    : </label><a href='#'> ".($order->get_meta('_wassalNow_Track_order')['shipmentId'] ?? 'Not shipping until')."</a><br/>".
                      "<label>Track Number    : </label><a href='#'> ".($order->get_meta('_wassalNow_Track_order')['trackingNumber'] ?? 'Not shipping until')."</a><br/>".
                      "<label>Order Status ID : </label><a href='#'> ".($order->get_meta('_wassalNow_Track_order')['orderStatusId'] ?? 'Not shipping until')."</a>".
                      '<hr/>'.
                      $this->button_track_order($order).
                      $this->button_cancel_order($order);
            endif;
        }



        /**
         *  order table track wassalNow column wassalNow 
         *
         * @access public
         * @return string
         */
        function custom_track_order_wassalNow_column($columns){

                $reordered_columns = array();
                // Inserting columns to a specific location
                foreach( $columns as $key => $column){
                    $reordered_columns[$key] = $column;
                    if( $key ==  'order_status' ){
                        // Inserting after "Status" column
                        $reordered_columns['my-column-trackorder'] = __( 'Order in WassalNow','wosw');
                    }
                }
                return $reordered_columns;
        }
        

        /**
         *  order table track wassalNow column wassalNow 
         *
         * @access public
         * @return string
         */
        function custom_orders_list_trackorder_column_content( $column, $post_id ){
            $this->order_id = $post_id;
            $order = wc_get_order( $post_id );
            switch ( $column ){
                 case 'my-column-trackorder' :
                     echo ( ($this->get_shipping_method_id() == 'wasslnow') ? $this->button_track_order($order):__('No support WassalNow - '.$this->get_shipping_method_id(),'theme') );
                 break;
            }

        }


        /**
         *  order table track wassalNow column wassalNow 
         *
         * @access public
         * @return string
         */
        function wassalNow_add_admin_styles_scripts(){
           ?>
               <style type="text/css">
                    .swal2-title
                    {
                      font-size: 1.6em !important;
                      line-height: 1.3em;
                    }
               </style>
           <?php 
        }
	
	}

	new admin_wosw_shipment();
 
}