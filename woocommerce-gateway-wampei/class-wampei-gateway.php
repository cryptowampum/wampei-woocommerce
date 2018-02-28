<?php

/**
 * Class WC_Wampei_Gateway
 *
 * Extension of WC_Payment_Gateway class to create Wampei Payment Gateway
 *
 * This class extends the WooCommerce payment gateway class to generate our admin area settings
 * forms and include the gateway in available gateway choices for users.
 */
class WC_Wampei_Gateway extends WC_Payment_Gateway {
	
	public function __construct() {
		
		/**
		 * @var string $id                 ID of the payment gateway
		 * @var string $icon               URL to image file for icon to appear during checkout
		 * @var bool   $has_fields         If gateway requires form fields during checkout
		 * @var string $method_title       Visual name of the gateway
		 * @var string $method_description Description that appear in admin area
		 */
		
		$this->id                 = 'wampei';
		$this->icon               = '';
		$this->has_fields         = FALSE;
		$this->method_title       = 'Wampei Bitcoin';
		$this->method_description = 'Use Bitcoin for purchases with Wampei';
		$this->supports           = array(
			'subscriptions',
			'products'
		);
		
		$this->init_form_fields();
		$this->init_settings();
		
		$this->title = $this->get_option( 'title' );
		
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'update_store_currency' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
		
	}
	
	public function update_store_currency(){
		
		update_option( 'woocommerce_currency', strtoupper( $_POST[ 'woocommerce_wampei_currency' ] ), TRUE );
				
	}
	
	/**
	 * Define our settings form fields for setting up the gateway.
	 */
	public function init_form_fields() {
		
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-wampei' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Wampei Bitcoin Payment', 'woocommerce-wampei' ),
				'default' => 'no'
			),
			'currency'     => array(
				'title'   => __( 'Wampei Registry Currency', 'woocommerce-wampei' ),
				'type'    => 'select',
				'options' => array(
					'usd' => __( 'United States Dollar', 'woocommerce-wampei' ),
					'eur' => __( 'Euro', 'woocommerce-wampei' )
				),
				'description' => __( 'Must match Wampei Registry currency and will change store currency', 'woocommerce-wampei' )
			),
			'title'        => array(
				'title'       => __( 'Title', 'woocommerce-wampei' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-wampei' ),
				'default'     => __( 'Wampei Bitcoin', 'woocommerce-wampei' ),
				'desc_tip'    => TRUE,
			),
			'description'  => array(
				'title'   => __( 'Customer Message', 'woocommerce-wampei' ),
				'type'    => 'textarea',
				'default' => ''
			),
			'api_url'      => array(
				'title'       => __( 'Wampei URL', 'woocommerce-wampei' ),
				'type'        => 'url',
				'description' => __( 'Your Wampei issued API URL', 'woocommerce-wampei' )
			),
			'api_username' => array(
				'title'       => __( 'Wampei Username', 'woocommerce-wampei' ),
				'type'        => 'text',
				'description' => __( 'Your Wampei issued API username', 'woocommerce-wampei' )
			),
			'api_password' => array(
				'title'       => __( 'Wampei Password', 'woocommerce-wampum' ),
				'type'        => 'text',
				'description' => __( 'Your  Wampei issued API password', 'woocommerce-wampei' )
			)
		);
		
	}
	
	
	/**
	 * Trigger the necessary functionality to process an order and add it to the database, output messages etc.
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		
		global $woocommerce;
		
		$order = new WC_Order( $order_id );
		
		//Mark as processing until we've checked invoice payment status
		$order->update_status( 'on-hold', __( 'System is validating payment reception', 'woocommerce' ) );
		$order->add_order_note( sprintf( __( 'Wampei charge processing', 'woocommerce-gateway-wampei' ) ) );
		
		//Reduce Stock Level
		$order->reduce_order_stock();
		
		//Clear the cart of the order items
		$woocommerce->cart->empty_cart();
		
		//Generate thankyou redirect
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		);
		
	}
	
}
