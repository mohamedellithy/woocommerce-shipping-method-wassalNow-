<?php 

/**
 * @package process wosw shipment
 * init settings for install shipping method
 **/
if ( ! defined( 'WPINC' ) ) {
    die; 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    class process_wosw_shipment extends wosw_api{
    	
    	public $customer_data = array(); // array to load & store customer data shipping for create new order
    	public $order_id; // order ID that have shippment wassalNow
    	function process_wosw_shipment_init(){
             
             if(empty(get_option('wassalnow_api')))
                return;
             
             //set api authentaction senderID & senderApiKey
             self::$senderId     = get_option('wassalnow_api')['senderId'] ?? ''; 
             self::$senderApiKey = get_option('wassalnow_api')['senderApiKey'] ?? '';


    	    
             //here ajax track-order shipping in wosw
            add_action('wp_ajax_admin_track_wosw_order',       array($this,'admin_track_wosw_order'));
            add_action('wp_ajax_nopriv_admin_track_wosw_order',array($this,'admin_track_wosw_order'));
            
             //here ajax cancel-order shipping in wosw
            add_action('wp_ajax_admin_cancel_wosw_order',       array($this,'admin_cancel_wosw_order'));
            add_action('wp_ajax_nopriv_admin_cancel_wosw_order',array($this,'admin_cancel_wosw_order'));
            
            
        }



        /**
         * wosw-order-status-complete   
         * get order status completed
         * @access public
         * @return void
         */
        function wosw_order_status_complete($order_id){
            


             // instance order id 
             $this->order_id = $order_id;

             // Get an instance of the WC_Order Object from the Order ID (if required)
    	    	 $order = wc_get_order( $order_id );

             // check if wassalNow shipping created for this order
    		      if($this->have_shipment_wassalNow($order))
                return;
           	 
               // if shipping method is wasslnow
               if('wasslnow' == $this->get_shipping_method_id() ):

                   // create order shipment in WassalNow by using Api 
                   $status_Create_order = $this->Create_a_New_Shipment($this->get_customer_shipping_info());
                   //$status_Create_order = $this->Create_a_New_Shipment($this->get_customer_shipping_info());
                 
                   // update order with trackNo and shipmentID
                   update_option('wassalnow_test', $status_Create_order['trackingNumber'] );
                   add_post_meta( $order_id,'_wassalNow_Track_order',$status_Create_order);

               endif;

        }


        /**
         * wosw-order-status-cancelled   
         * get order status cancelled
         * @access public
         * @return void
         */
        function wosw_order_status_cancelled($order_id){
            $this->order_id =$order_id;

        	  // Get an instance of the WC_Order Object from the Order ID (if required)
        	  $order = wc_get_order( $order_id );
             
             // check if wassalNow shipping created 
        	  if($this->have_shipment_wassalNow($order) == false)
        	   	return;
             
             // if shipping method is wasslnow
              if('wasslnow' == $this->get_shipping_method_id() ):
                 
                 // create order shipment in WassalNow by using Api 
                 $status_Create_order = $this->Cancel_a_Shipment( $this->have_shipment_wassalNow($order) );
                 
                 // update order with trackNo and shipmentID
                 //update_post_meta( $order_id,'_wassalNow_Track_order', '');
    		
    		      endif;
        }
         

        /**
         * wosw-order-status-cancelled   
         * get order status cancelled
         * @access public
         * @return boolean
         */
        function have_shipment_wassalNow($order){
            if(!empty($order->get_meta( '_wassalNow_Track_order') ) ):
                return $order->get_meta( '_wassalNow_Track_order');
            else:
            	return false;
            endif;
        }
         


         /**
         * get-customer-shipping-info  
         * get customer shipping info data
         * @access public
         * @return array
         */
        function get_customer_shipping_info(){
        	// Get an instance of the WC_Order Object from the Order ID (if required)
    		$order = wc_get_order( $this->order_id );

    		// Get the order meta data in an unprotected array
    		$data  = $order->get_data(); // The Order data

    		// get payment method title
    		$payment_method = $data['payment_method'];
            
            // check if order cache on delivery or not ( COD )
    		if($payment_method=='cod')
    			$COD = $order->get_total() ?? 0 ;

    		## BILLING INFORMATION:

    		$shipping_email      = $data['shipping']['email']  ?? $data['billing']['email'];
    		$shipping_phone      = $data['shipping']['phone'] ?? $data['billing']['phone'];

    		$shipping_first_name = $data['shipping']['first_name'] ?? $data['billing']['first_name'];
    		$shipping_last_name  = $data['shipping']['last_name']  ?? $data['billing']['last_name'];
    		$shipping_company    = $data['billing']['company'];
    		$shipping_address_1  = $data['shipping']['address_1']  ?? $data['billing']['address_1'];
    		$shipping_address_2  = $data['shipping']['address_2']  ?? $data['billing']['address_2'];
    		$shipping_city       = $data['shipping']['city']       ?? $data['billing']['city'];
    		$shipping_state      = $data['shipping']['state']      ?? $data['billing']['state'];
    		$shipping_postcode   = $data['shipping']['postcode']   ?? $data['billing']['postcode'];
            

            // here handel shipment data for create new shipment item
            $shipping_data = array(
               'customerMobile' => $shipping_phone,
               'customerName'   => $shipping_first_name.' '.$shipping_last_name,
               'customerAddress'=> 'Address 1 : '.($shipping_address_1     ?? 'Not Added').
                                   '  | Address 2 : '.($shipping_address_2 ?? 'Not Added').
                                   '  | state/town : '.($shipping_state    ?? 'Not Added').
                                   '  | postcode :'.($shipping_postcode    ?? 'Not Added'),
               'customerCity'    => trim(str_replace('Governorate', '', $shipping_city)),
               'promoCode'       => '',       
               'COD'             => $COD ?? 0,
               'shipmentContents'=> $this->get_order_contents($order) ?? '',
            );

            return $shipping_data;
        }
        



        /**
         * get-order-contents add contents to order 
         *
         * @access public
         * @return string
         */
        function get_order_contents($order){
            if(empty($order))
          	   return;
            
            // shipment Contents (description items in order )
            $shipmentContents = '';


            // Iterating through each WC_Order_Item_Product objects
            foreach ($order->get_items() as $item_key => $item ):
                  $shipmentContents .= " Item Name : ".$item->get_name().' - ';
                  $shipmentContents .= " Item Quantity : ".$item->get_quantity().' |  ';
           	endforeach;
             
            // return contetn of shippments
           	return $shipmentContents; 
        }

        
        /**
         * get-shipping-method-id add contents to order 
         *
         * @access public
         * @return string
         */

        function get_shipping_method_id(){
            // Get an instance of the WC_Order Object from the Order ID (if required)
            $order = wc_get_order( $this->order_id );           
            
            // get shipment details from item data
            foreach($order->get_items('shipping') as  $item_id => $shipping_item_obj):
                return $shipping_item_obj->get_method_id();
            endforeach;

        }

        


        /**
         * add styles and scripts add contents to order 
         *
         * @access public
         * @return string
         */
        function styles_and_scripts_wosw(){
             // sweetalert2 css 
             wp_enqueue_style( 'wosw-sweetalert2-css', WOSW_PLUGIN_URL.'/App/dist/css/sweetalert2.min.css' );
            
             // wosw-style css 
             if( !is_admin() ):
                wp_enqueue_style( 'wosw-style', WOSW_PLUGIN_URL.'/App/dist/css/wosw-style.css' );
             endif;
             


             // sweetalert2 js 
             wp_enqueue_script( 'wosw-sweetalert2-js', WOSW_PLUGIN_URL.'/App/dist/js/sweetalert2.all.min.js', array(), '1.0.0', true );
            
             // script js 
             wp_enqueue_script( 'wosw-script', WOSW_PLUGIN_URL.'/App/dist/js/wosw-script.js', array(), '1.0.0', true );
             wp_localize_script( 'wosw-script','wosw_admin_ajax_trackorder',array('ajaxurl' => admin_url( 'admin-ajax.php' ) ), array(), '1.0.0', true );
        }
        


     

        /**
         * button track order in admin add contents to order 
         *
         * @access public
         * @return string
         */
        function button_track_order($order){
            $button_track_order = "<button 
                                       class='button button-info show_track_order' 
                                       type='button'
                                       data-error='".__('Wait Admin Approve Order to Start Shipping!','theme')."'
                                       data-value='".($order->get_meta('_wassalNow_Track_order')['trackingNumber'] ?? '')."'>
                                       ".__('Track Order','theme')."
                                  </button>";
            return $button_track_order;
        }

        /**
         * button cancel track order in admin add contents to order 
         *
         * @access public
         * @return string
         */
        function button_cancel_order($order){
            $button_cancel_order = "<button 
                                       class='button button-danger cancel_order_wosw' 
                                       type='button'
                                       style='margin-left:22px;background-color:#bf3939;border: 1px solid #bf3939;color: white;'
                                       data-questionair='".__('Are you sure to cancel shipping ?','theme')."'
                                       data-confirm='".__('Confirme','theme')."'
                                       data-cancel ='".__('Cancel','theme')."'
                                       data-value='".($order->get_meta('_wassalNow_Track_order')['shipmentId'] ?? '')."'>
                                       ".__('Cancel Order','theme')."
                                  </button>";
            return $button_cancel_order;
        }

        
        /**
         * ajax admin track wassalNow order 
         *
         * @access public
         * @return string
         */
        function admin_track_wosw_order(){

           // check if have values of order 
           if( !empty( $_POST['order_items_track_info'] ) ):

              // set array for tracking order
              $trackitems_attr   = array('trackingNumber'=> strip_tags( trim($_POST['order_items_track_info']) ) );

              // @return json result of tracking order in wassalNow 
              $result = $this->Track_a_Shipment($trackitems_attr);
              
              if(!empty($result)){
                $result['buttonText'] = __('Done','theme');
                $result['titleText']  = __('TrackNo : ','theme');                
              }       

              echo json_encode($result);

           endif;

           wp_die();
        }
        

         /**
         * ajax admin cancel wassalNow order 
         *
         * @access public
         * @return string
         */
        function admin_cancel_wosw_order(){
           // check if have values of order 
           if( !empty( $_POST['order_items_shipmentId'] ) ):

              // set array for tracking order
              $shipmentId_attr   = array('shipmentId'=>strip_tags( trim($_POST['order_items_shipmentId'])) );

              // @return json result of tracking order in wassalNow
              $result = $this->Cancel_a_Shipment($shipmentId_attr);
              
              if(!empty($result)){
                $result['buttonText'] = __('Done','theme');
                $result['titleText']  = __('Cancel Order shipping','theme');
              }

              echo json_encode($result);                
           endif;

           wp_die();
        }

    
    }

}
