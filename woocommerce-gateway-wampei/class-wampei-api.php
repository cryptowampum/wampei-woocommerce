<?php

class Wampei {
	
	/**
	 * Wampei constructor.
	 *
	 * @var array   $credentials        All the settings as saved in the admin area such as API username & password
	 * @var object  $api_connection     Connect to Wampum API ready to process queries and get responses
	 */
	function __construct() {
		
		/**
		 * Load the Wampum Payment Gateway to access the user stored
		 * API credentials & settings.
		 */
		$wampei_gateway       = new WC_Wampei_Gateway();
		$this->credentials    = $wampei_gateway->settings;
		$this->api_connection = $this->connect();
		
	}
	
	
	/**
	 * @return resource
	 */
	private function connect() {
		
		/**
		 * Set method and headers to connect to Wampei API to submit, query and update
		 * invoices.
		 *
		 * @return = API connection
		 */
		$query = array(
			'http' => array(
				'method' => 'GET',
				'header' => 'Authorization: Basic ' . base64_encode( htmlspecialchars_decode( $this->credentials['api_username'] . ':' . $this->credentials['api_password'] ) )
			)
		);
		
		return stream_context_create( $query );
		
	}
	
	
	/**
	 * @param $order_id WooCommerce Order ID
	 */
	public function process_invoice( $order_id,$my) {
		
		if ( $this->check_for_invoice( $order_id ) ) {
			
			$this->pay_order( $order_id );
			
		} else {
			
			$response = $this->submit_invoice( $order_id );
			if($my==false)
			 {
			$this->generate_html( array(
				'order_post_id'   => $order_id,
				'amount'          => $response->priceBTC,
				'qr_image'        => $response->qrImage,
				'pay_url'         => $response->bitcoinUri,
				'invoice_address' => $response->address,
				'btcToUsd'		  =>$response->btcToUsd
			));
			 }
			return $response;
			
		}
		
	}

	/**
	 * Update each WooCommerce order that is created via Wampei gateway and yet to be paid for.
	 */
	public function update_orders() {
		
		$orders = get_posts( array( 'post_type' => 'shop_order', 'post_status' => 'wc-on-hold' ) );
		
		foreach ( $orders as $order ) {
			
			$wampei_address = $this->check_for_invoice( $order->ID );
			
			if ( $wampei_address ) {
				
				$this->check_invoice_status( $wampei_address );
			}
		}
		
	}
	
	
	/**
	 * @param $order_id WooCommerce Order ID
	 *
	 * @return string   Wampei Invoice Address
	 */
	private function check_for_invoice( $order_id ) {
		
		return get_post_meta( $order_id, '_wampei_invoice_address', TRUE );
		
	}
	
	
	/**
	 * Update the WooCommerce Order order status
	 *
	 * @param $wampum_address       Wampei Invoice Address
	 */
	private function check_invoice_status( $wampei_address ) {
		
		$response = $this->get_invoice( $wampei_address );
		
		$this->update_order_status( $response->status, $response->desc );
				
	}
	
	
	/**
	 * Pay for a single WooCommerce Order & update WooCommerce Order status.
	 *
	 * @param $order_id WooCommerce Order ID
	 */
	private function pay_order( $order_id ) {
		
		$wampei_address = $this->check_for_invoice( $order_id );
		$response       = $this->get_invoice( $wampei_address );
		
		if ( $response->status == 'EXPIRED' ) {
			
			$this->generate_error( array(
				'message' => 'Invoice Expired'
			) );
			
		} elseif ( $response->status == 'PAID_BTC' ) {
			
			$this->generate_error( array(
				'message' => 'Invoice Paid'
			) );
			
		} else {
			
			$this->generate_html( array(
				'order_post_id'   => $order_id,
				'amount'          => $response->priceBTC,
				'qr_image'        => $response->qrImage,
				'pay_url'         => $response->bitcoinUri,
				'invoice_address' => $response->address
			) );
			
			
		}
		
	}
	
	
	/**
	 * Determine what the correlating WooCommerce order status should be based
	 * on Wampum invoice status and update WooCommerce Order status
	 *
	 * @param $invoice_status   Wampei Invoice Status
	 * @param $order_id         WooCommerce Order ID
	 */
	private function update_order_status( $invoice_status, $order_id ) {
		
		switch ( $invoice_status ) {
			
			case 'PAID_BTC':
				$order_status = 'processing';
				break;
			case 'CLOSED':
				$order_status = 'processing';
				break;
			case 'UNDERPAID_BTC':
				$order_status = 'on-hold';
				break;
			case 'EXPIRED':
				$order_status = 'cancelled';
				break;
			case 'EXPIRED_UNDERPAID':
				$order_status = 'cancelled';
				break;
			case 'PAID_CASH':
				$order_status = 'processing';
				break;
			case 'OVERPAID_BTC':
				$order_status = 'processing';
				break;
			default:
				$order_status = 'on-hold';
			
		}
		
		$order = new WC_Order( $order_id );
		
		
		if ( $order->status != $order_status ) {
			$order->update_status( $order_status, 'Order ' . ucfirst( $order_status ) );
		}
		
	}
	
	
	/**
	 * Send the data object to Wampei to register an invoice with the Wampei Registry
	 *
	 * @param $order_id             WooCommerce Order ID
	 *
	 * @return array|mixed|object   Get back the raw Wampei invoice submission data
	 */
	private function submit_invoice( $order_id ) {
		
		/**
		 * Ready our invoice submission by getting USD amount
		 * and Unique Identifier for reconciliation.
		 */
		$order = new WC_Order( $order_id );
		
		$invoice_params = array(
			'url'   => $this->credentials['api_url'],
			'total' => $order->get_total(),
			'desc'  => $order_id
		);
		
		
		/**
		 * Submit invoice to Wampei Register for processing
		 * and QR/URL generations.
		 */
		$response = json_decode( file_get_contents( $invoice_params['url'] . '/invoice/remote/terminal/json?usd=' . $invoice_params['total'] . '&desc=' . $invoice_params['desc'], FALSE, $this->api_connection ) );
		
		return $response;
		
	}
	
	
	/**
	 * Get an existing invoice from Wampei Registry
	 *
	 * @param $wampum_address       Wampei Invoice Address
	 *
	 * @return array|mixed|object   Existing invoice data object
	 */
	private function get_invoice( $wampei_address ) {
		
		$response = json_decode( file_get_contents( $this->credentials['api_url'] . '/invoice/remote/status/json?address=' . $wampei_address, FALSE, $this->api_connection ) );
		
		return $response;
		
	}
	
	
	/**
	 * Output the payment HTML for users to pay Wampei invoices
	 *
	 * @param array $params Required data to output to the frontend for users to pay invoices
	 */
	private function generate_html( $params = array() ) {
		
		echo '<div class="wampei">';
		
		if ( $params['invoice_address'] ) {
			
			update_post_meta( $params['order_post_id'], '_wampei_invoice_address', $params['invoice_address'] );
			echo '<div class="wampei__col-1">';
			echo '<div class="wampei__row">';
			echo '<h3>Scan with wallet</h3>';
			echo '<a href="' . $params['pay_url'] . '"/><img class="wampei__qr-code" src="' . $params['qr_image'] . '"/></a>';
			echo '</div>';
			echo '</div>';
			
			echo '<div class="wampei__col-2">';
			echo '<div class="wampei__row">';
			echo '<h3>Amount in Bitcoin</h3>';
			echo '<p>' . $params['amount'] . '</p>';
			echo '</div>';
			echo '</div>';
			echo '<div class="wampei__col-2">';
			echo '<div class="wampei__row">';
			echo '<h3>Bitcoin Exchange Rate</h3>';
			echo '<p>$ ' . $params['btcToUsd'] . ' / BTC </p>';
			echo '</div>';
			echo '<div class="wampei__row">';
			echo '<h3>Bitcoin Wallet URL</h3>';
			echo '<p> <a href="' . $params['pay_url'] . '"/>Link</a></p>';
			echo '</div>';
			echo '</div>';
			
		} else {
			
			echo '<h3>Error occurred.</h3>';
			
		}
		
		echo '</div>';
		
	}
	
	
	/**
	 * Output a message to the frontend when required to prompt users
	 *
	 * @param array $params The necessary elements to build the message
	 */
	public function generate_error( $params = array() ) {
		
		echo '<div class="wampei">';
		
		echo '<h3><strong>Error - </strong>' . $params['message'] . '</h3>';
		
		echo '</div>';
		
	}
	
}
