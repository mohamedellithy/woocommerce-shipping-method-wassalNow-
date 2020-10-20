<?php 

/**
 * @package front display wosw shipment
 * init settings for install shipping method
 **/
if ( ! defined( 'WPINC' ) ) {
    die; 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	class front_wosw_shipment extends process_wosw_shipment{
         

	  	function __construct(){

            $this->process_wosw_shipment_init();

            //here add styles and scripts for show trackorder shipment
            add_action('wp_enqueue_scripts',array($this,'styles_and_scripts_wosw'));
            

            add_filter( 'woocommerce_my_account_my_orders_columns', array($this,'wosw_wc_add_my_account_orders_column') );

            //woocommerce show trackorder on table orders account
            add_action( 'woocommerce_my_account_my_orders_column_wosw-trackorder', array($this,'wosw_wc_myaccount_orders_trackorder') ,20,1);
            
            //woocommerce show trackorder on dashboard
            add_action('woocommerce_order_details_after_order_table', array($this,'show_wassal_trackorder_after_order_table'), 10, 4 );
            
            //add shortcode form trackorder
            add_shortcode('wassalnow-trackorder',array($this,'wassalNow_form_track_order'));

	  	}




	  	/**
		 * Adds a new column to the "My Orders" table in the account.
		 *
		 * @param string[] $columns the columns in the orders table
		 * @return string[] updated columns
		 */
		function wosw_wc_add_my_account_orders_column( $columns ) {

		    $new_columns = array();

		    foreach ( $columns as $key => $name ) {

		        $new_columns[ $key ] = $name;

		        // add ship-to after order status column
		        if ( 'order-status' === $key ) {
		            
		            $new_columns['wosw-trackorder'] = __( 'Order in WassalNow','wosw');
		        }
		    }

		    return $new_columns;
	    }  

        

        /**
		 * Adds data to the custom "ship to" column in "My Account > Orders".
		 *
		 * @param \WC_Order $order the order object for the row
		 */
	    function wosw_wc_myaccount_orders_trackorder( $order ){
            $this->order_id = $order->get_id();
            echo ( ($this->get_shipping_method_id() == 'wassalnow') ? $this->display_trackNo_And_shipmentID($order):__('No support WassalNow - '.$this->get_shipping_method_id(),'theme'));
	    }


	     /**
         * display-trackNo-And-shipmentID in admin add contents to order 
         *
         * @access public
         * @return string
         */
        function display_trackNo_And_shipmentID( $order ){ 
            //get Order ID  
            $this->order_id = $order->get_id();

            // if shipping method is wassalnow
            if('wassalnow' == $this->get_shipping_method_id() ):
               
                 return '<div class="dropdown">
						  <button  data-value="'.$this->order_id.'" class="dropbtn wassal_dropbtn">'.__('Track-Order').'
						  </button>
						  <div id="myDropdown'.$this->order_id.'" class="dropdown-content">
						    <a href="#myDropdown">'.$this->button_track_order($order).'</a>
						    <a href="#myDropdown">'.$this->button_cancel_order($order).'</a>
						  </div>
						</div>';
                      
            endif;
        }

        
        /**
         * display-Trackor in dashboard account add contents to order 
         *
         * @access public
         * @return string
         */
		function show_wassal_trackorder_after_order_table( $order, $sent_to_admin = '', $plain_text = '', $email = '' ) {
		    // Only on "My Account" > "Order View"
		    if ( is_wc_endpoint_url( 'view-order' ) ) {
		    	
		    	//get Order ID  
                $this->order_id = $order->get_id();
		    	
		    	if($this->get_shipping_method_id() == 'wassalnow'):
			
			        echo  '<div class="show-trackorder-dashboard">'.
			                '<img src="https://wassalnow.com/wp-content/uploads/2020/04/WNlogo-1.png" width="100px" height="100px" />'.
			                '<label> '.__('WassalNow Track order : ').' </label>'.
			                $this->display_trackNo_And_shipmentID($order).
			               '</div>';

			    endif;
		    }
		}

        
        /**
         * form trackorder shortcode 
         *
         * @access public
         * @return string
         */
		function wassalNow_form_track_order(){
            echo  '<div class="container-form-wassalnow">
	                    <div class="show-trackorder-dashboard form-trackorder">'.
		                    '<form method="POST" class="track_order_form">'.
				                '<div class="wassalNow-continer-img"><img src="https://wassalnow.com/wp-content/uploads/2020/04/WNlogo-1.png" /></div>'.
				                '<p> '.__('WassalNow Track Number : ').' </p>'.
				                '<div class="wassalNow-container-input"><input  type="text" class="trackNo_input" name="wassalNow_trackNo" required/></div>'.
				                '<input  type="text" class="trackNo_input" name="wassalNow_error" value="'.__('Order Wait Admin Approve to Shipping','theme').'" hidden/>'.
				                '<button type="submit" class="track_order_button " name="track_order_button" > '.__('Track Order','theme').'</button>'.
				                /*'<button type="submit" class="cancel_order_button" name="cancel_order_button" >'.__('Cancel Order','theme').'</button>'.*/
			                '</form>'.
		               '</div>'.
	               '</div>';
		}

	}

	new front_wosw_shipment();
 
}