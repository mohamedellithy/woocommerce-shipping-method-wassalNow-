<?php 

/**
 * Plugin Name: WassalNow Woocommerce Shipping
 * Plugin URI: http://wassalnow.com/
 * Author: mohamed ellithy
 * Author URI: https://mostaql.com/u/mohamedeloms
 * Version: 2.0.3
 * Description: WassalNow is your reliable and affordable app for on demand shipping. Ship your packages with our reliable and trained representatives during morning and evening with same day delivery and shipment insurance included. 
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: WOSW
*/
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WOSW_VERSION', '2.0.3' );
define( 'WOSW_DB_VERSION', '2.0' );
define( 'WOSW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOSW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


require_once WOSW_PLUGIN_DIR.'App/functions.php';


// here create page for form wassalNow tracking-order
// put shortcode [wasslnow-trackorder]
function wasslnow_tracking_active() {
  if( empty( get_option('wasslnow_tracking_active_page_id') ) ):
      // Create post object
      $my_post = array(
        'post_title'    => wp_strip_all_tags( 'Wasslnow track-order' ),
        'post_content'  => '[wasslnow-trackorder]',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'     => 'page',
      );

      // Insert the post into the database
     $page_id   =  wp_insert_post( $my_post );
     update_option('wasslnow_tracking_active_page_id',$page_id);
  endif;
}

register_activation_hook(__FILE__, 'wasslnow_tracking_active');
