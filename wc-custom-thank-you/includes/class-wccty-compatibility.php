<?php
/**
 * WooCommerce feature compatibility declarations.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Compatibility', false ) ) {
	return;
}

/**
 * Handles WooCommerce feature compatibility (HPOS + Cart & Checkout Blocks).
 */
final class WCCTY_Compatibility {

	/**
	 * Declare compatibility with WooCommerce features.
	 *
	 * @return void
	 */
	public static function declare() {
		if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		if ( ! defined( 'WC_CUSTOM_THANKYOU_FILE' ) ) {
			return;
		}

		// High-Performance Order Storage.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			WC_CUSTOM_THANKYOU_FILE,
			true
		);

		// Cart & Checkout blocks.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'cart_checkout_blocks',
			WC_CUSTOM_THANKYOU_FILE,
			true
		);
	}
}
