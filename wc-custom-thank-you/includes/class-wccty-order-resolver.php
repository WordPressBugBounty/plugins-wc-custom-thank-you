<?php
/**
 * Order resolution and validation.
 *
 * Single source of truth for turning the current request into a validated
 * WC_Order. Both the frontend (legacy template / shortcode) and the Order
 * Confirmation block route through this helper so order resolution and the
 * security check stay consistent.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Order_Resolver', false ) ) {
	return;
}

/**
 * Resolves and validates the WooCommerce order for the current request.
 */
final class WCCTY_Order_Resolver {

	/**
	 * Resolve and validate the order for the current request.
	 *
	 * Returns a valid WC_Order only when the request carries an order key that
	 * matches the resolved order. The result is memoized for the whole request.
	 *
	 * @return WC_Order|false
	 */
	public static function get_order_from_request() {
		static $resolved = false;
		static $order    = false;

		if ( $resolved ) {
			return $order;
		}

		$resolved = true;

		if ( ! function_exists( 'wc_get_order' ) ) {
			$order = false;
			return $order;
		}

		$order_id  = self::get_order_id_from_request();
		$order_key = self::get_order_key_from_request();

		// Derive the ID from the key when the request only carries a key.
		if ( ! $order_id && '' !== $order_key && function_exists( 'wc_get_order_id_by_order_key' ) ) {
			$order_id = absint( wc_get_order_id_by_order_key( $order_key ) );
		}

		// Apply the same filters WooCommerce core applies on its thank you page.
		$order_id  = absint( apply_filters( 'woocommerce_thankyou_order_id', $order_id ) );
		$order_key = wc_clean( apply_filters( 'woocommerce_thankyou_order_key', $order_key ) );

		if ( $order_id <= 0 || ! is_string( $order_key ) || '' === $order_key ) {
			$order = false;
			return $order;
		}

		$maybe_order = wc_get_order( $order_id );

		// Require a valid order whose key matches (timing-safe comparison).
		if (
			$maybe_order instanceof WC_Order
			&& $maybe_order->get_id() === $order_id
			&& hash_equals( (string) $maybe_order->get_order_key(), (string) $order_key )
		) {
			$order = $maybe_order;
		} else {
			$order = false;
		}

		return $order;
	}

	/**
	 * Get the sanitized order ID from the request.
	 *
	 * Checks the custom Thank You page parameter (`order`) first, then the core
	 * order-received endpoint (query var and query string), so the same helper
	 * works whether the request lands on the custom page or the WC endpoint.
	 *
	 * @return int
	 */
	public static function get_order_id_from_request() {
		$order_id = filter_input(
			INPUT_GET,
			'order',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
				),
			)
		);

		if ( is_int( $order_id ) && $order_id > 0 ) {
			return $order_id;
		}

		if ( function_exists( 'get_query_var' ) ) {
			$order_received = absint( get_query_var( 'order-received' ) );

			if ( $order_received > 0 ) {
				return $order_received;
			}
		}

		global $wp;

		if ( isset( $wp->query_vars['order-received'] ) ) {
			$order_received = absint( $wp->query_vars['order-received'] );

			if ( $order_received > 0 ) {
				return $order_received;
			}
		}

		$order_received_get = filter_input(
			INPUT_GET,
			'order-received',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
				),
			)
		);

		if ( is_int( $order_received_get ) && $order_received_get > 0 ) {
			return $order_received_get;
		}

		return 0;
	}

	/**
	 * Get the sanitized, format-validated order key from the request.
	 *
	 * @return string
	 */
	public static function get_order_key_from_request() {
		$order_key = filter_input( INPUT_GET, 'key', FILTER_UNSAFE_RAW );

		if ( ! is_string( $order_key ) || '' === $order_key ) {
			return '';
		}

		$order_key = sanitize_text_field( $order_key );

		if ( '' === $order_key ) {
			return '';
		}

		// Only allow the characters used by WooCommerce order keys.
		if ( 64 < strlen( $order_key ) || ! preg_match( '/^[A-Za-z0-9_]+$/', $order_key ) ) {
			return '';
		}

		return $order_key;
	}
}
