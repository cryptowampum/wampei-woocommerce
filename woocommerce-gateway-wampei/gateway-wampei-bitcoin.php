<?php
/**
 * @author    Wampei Inc
 * @copyright Wampei Inc
 * @license   GPLv2 or later
 *
 * @wordpress-plugin
 * Plugin Name: WooCommerce Wampei Bitcoin Gateway
 * Plugin URI: https://github.com/cryptowampum/wampei-woocommerce
 * Description: Allows WooCommerce to leverage Wampei Register© Merchant wallet for accepting Bitcoin without a third party you can learn more about how a non-custodial wallet can help your business at http://wampei.com
 * WC tested up to: 4.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Author: Wampei, Inc
 * Author URI: https://wampei.com
 * Version: 1.4
 */

/*
WooCommerce Wampei Bitcoin Gateway is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WooCommerce Wampei Bitcoin Gateway is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with WooCommerce Wampei Bitcoin Gateway. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

/**
 * Run the Wampei gateway with necessary checks to make sure running it is valid
 * Note that the currency is setup in Wampei Register instance!
 *
 * @return WC_Wampei_Gateway    Wampei Payment Gateway
 */
 
 
 
function wampei_init_gateway() {
	
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		
		require_once( plugin_dir_path( __FILE__ ) . 'class-wampei-gateway.php' );
		
		function wampei_add_gateway( $methods ) {
			
			$methods[] = 'WC_Wampei_Gateway';
			
			return $methods;
			
		}
		
		add_filter( 'woocommerce_payment_gateways', 'wampei_add_gateway' );
		
		$active_currency = get_woocommerce_currency();
		if ( $active_currency != 'USD' && $active_currency != 'EUR' ) {
						
			function wampei_disable_gateway( $available_gateways ) {
				
				unset( $available_gateways['wampei'] );
				
				return $available_gateways;
				
			}
			
			add_filter( 'woocommerce_available_payment_gateways', 'wampei_disable_gateway' );
			
			function wampei_admin__error( $message = NULL ) {
				
				echo '<div class="notice notice-error is-dismissible">';
				echo __( '<p><strong>WooCommerce Wampei disabled.</strong> Wampei currently only supports United States Dollar & Euro currencies.</p>', 'wampei' );
				echo '</div>';
				
			}
			
			add_action( 'admin_notices', 'wampei_admin__error' );
			
		}
		
		return new WC_Wampei_Gateway();
		
	} else {
		
		function wampei_admin__error( $message = NULL ) {
			
			echo '<div class="notice notice-error is-dismissible">';
			echo __( '<p>WooCommerce must be active for WooCommerce Wampei Gateway to work. Please activate WooCommerce.</p>', 'wampei' );
			echo '</div>';
			
		}
		
		add_action( 'admin_notices', 'wampei_admin__error' );
		
	}
	
	
}

add_action( 'plugins_loaded', 'wampei_init_gateway' );


/**
 * Run the Wampei API class
 *
 * @param $order_id WooCommerce Order ID
 */
function wampei_init( $order_id ) {
	
	require_once( plugin_dir_path( __FILE__ ) . 'class-wampei-api.php' );
	
	$wampei = new Wampei();
	$my=false;
	return $wampei->process_invoice( $order_id,$my);
	
}

add_action( 'woocommerce_thankyou_wampei', 'wampei_init' );


/**
 * Register styles
 */
function wampei_plugin_styles(){
	wp_enqueue_style( 'wampei', plugin_dir_url( __FILE__ ) . '/css/style.css' );
}

add_action( 'wp_enqueue_scripts', 'wampei_plugin_styles' );


/**
 * Register new schedule
 */
function wampei_register_schedule( $schedules ) {
	
	$schedules[ 'twenty_min' ] = array(
		'interval' => 1200,
		'display'  => __( 'Every 20 Minutes' )
	);
	
	return $schedules;
	
}

add_filter( 'cron_schedules', 'wampei_register_schedule' );


/**
 * Register Wampei scheduled action on plugin activation
 */
function wampei_register_cron() {
	
	if ( !wp_next_scheduled( 'wampei_update_orders' ) ) {
		
		wp_schedule_event( time(), 'twenty_min', 'wampei_update_orders' );
		
	}
	
}

register_activation_hook( __FILE__, 'wampei_register_cron' );


/**
 * Trigger the scheduled order update loop
 */
function wampei_update_order_status() {
	
	require_once( plugin_dir_path( __FILE__ ) . 'class-wampei-api.php' );
	
	$wampei = new Wampei();
	
	$wampei->update_orders();
	
}

add_action( 'wampei_update_orders', 'wampei_update_order_status' );


/**
 * Remove scheduled action on plugin deactivation
 */
function wampei_deregister_cron() {
	
	wp_clear_scheduled_hook( 'wampei_update_orders' );
	
}
add_action( 'woocommerce_email_customer_details', 'add_order_email_instructions', 10, 2);
function add_order_email_instructions( $order,$imageurl ) {  
	
	$order_arr = json_decode($order);
	$order_id = $order_arr->id;
	require_once( plugin_dir_path( __FILE__ ) . 'class-wampei-api.php' );
	
	$wampei = new Wampei();
	$my=true;
	$response = $wampei->process_invoice( $order_id, $my );
	 
	echo '<h3>AMOUNT IN BITCOIN </h3>'.$response->priceBTC.'<h4>If you need to complete the payment do it <a href="'.$response->billURL.'">here</a></h4><p>';
	
 }
register_deactivation_hook( __FILE__, 'wampei_deregister_cron' );