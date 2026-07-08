<?php
/**
 * Assembles the display data for the Order Confirmation block.
 *
 * Keeps data-gathering out of the render template: render.php stays focused on
 * presentation, and extenders get a single filter to adjust every value the
 * block shows.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Order_Presenter', false ) ) {
	return;
}

/**
 * Turns a WC_Order into the structured data the block template renders.
 */
final class WCCTY_Order_Presenter {

	/**
	 * Build the display data for an order.
	 *
	 * @param WC_Order $order Order instance.
	 * @return array{
	 *     order:     array<string,mixed>,
	 *     billing:   array<string,string>,
	 *     shipping:  array<string,string>,
	 *     items:     array<int,WC_Order_Item>,
	 *     downloads: array<int,array<string,mixed>>,
	 *     totals:    array<string,array<string,string>>
	 * }
	 */
	public static function get_data( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return array(
				'order'     => array(),
				'billing'   => array(),
				'shipping'  => array(),
				'items'     => array(),
				'downloads' => array(),
				'totals'    => array(),
			);
		}

		// WooCommerce's own totals breakdown reconciles with the grand total
		// (includes discounts, fees and coupons). The payment method has its own
		// section in the template, so drop it here to avoid duplicating it.
		$totals = $order->get_order_item_totals();
		if ( isset( $totals['payment_method'] ) ) {
			unset( $totals['payment_method'] );
		}

		$data = array(
			'order'     => array(
				'id'             => $order->get_id(),
				'order_number'   => $order->get_order_number(),
				'date_created'   => $order->get_date_created(),
				'status'         => $order->get_status(),
				'currency'       => $order->get_currency(),
				'total'          => $order->get_total(),
				'subtotal'       => $order->get_subtotal(),
				'shipping_total' => $order->get_shipping_total(),
				'tax_total'      => $order->get_total_tax(),
				'payment_method' => $order->get_payment_method_title(),
				'customer_note'  => $order->get_customer_note(),
			),
			'billing'   => array(
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
				'company'    => $order->get_billing_company(),
				'address_1'  => $order->get_billing_address_1(),
				'address_2'  => $order->get_billing_address_2(),
				'city'       => $order->get_billing_city(),
				'state'      => $order->get_billing_state(),
				'postcode'   => $order->get_billing_postcode(),
				'country'    => $order->get_billing_country(),
				'email'      => $order->get_billing_email(),
				'phone'      => $order->get_billing_phone(),
			),
			'shipping'  => array(
				'first_name' => $order->get_shipping_first_name(),
				'last_name'  => $order->get_shipping_last_name(),
				'company'    => $order->get_shipping_company(),
				'address_1'  => $order->get_shipping_address_1(),
				'address_2'  => $order->get_shipping_address_2(),
				'city'       => $order->get_shipping_city(),
				'state'      => $order->get_shipping_state(),
				'postcode'   => $order->get_shipping_postcode(),
				'country'    => $order->get_shipping_country(),
			),
			'items'     => $order->get_items(),
			'downloads' => $order->get_downloadable_items(),
			'totals'    => $totals,
		);

		/**
		 * Filter the data used to render the Order Confirmation block.
		 *
		 * @param array    $data  Structured display data (order, billing, shipping, items, downloads, totals).
		 * @param WC_Order $order Order being displayed.
		 */
		return apply_filters( 'wccty_order_confirmation_data', $data, $order );
	}

	/**
	 * Resolve each order item into presentation-ready parts, so every template
	 * can render items without repeating the product-link / image / SKU logic.
	 *
	 * @param WC_Order $order       Order instance.
	 * @param bool     $show_images Whether to include product image markup.
	 * @param bool     $show_sku    Whether to include the product SKU.
	 * @return array<int,array<string,mixed>> Rows with name, quantity, total (HTML), url, image (HTML), sku.
	 */
	public static function get_items_display( $order, $show_images = true, $show_sku = false ) {
		$rows = array();

		if ( ! $order instanceof WC_Order ) {
			return $rows;
		}

		$currency = $order->get_currency();

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			$url     = '';
			$image   = '';
			$sku     = '';

			if ( $product instanceof WC_Product ) {
				$product_id = $product->get_id();

				// Variations are not publicly queryable; link to the parent product when possible.
				if ( $product->is_type( 'variation' ) && $product->get_parent_id() ) {
					$product_id = $product->get_parent_id();
				}

				// Only link to published products (avoid 404s for trashed/private items).
				if ( 'publish' === get_post_status( $product_id ) ) {
					$link_product = $product;

					if ( $product_id !== $product->get_id() ) {
						$link_product = wc_get_product( $product_id );
					}

					if ( $link_product instanceof WC_Product ) {
						$url = $link_product->get_permalink( $item );
					}
				}

				if ( $show_images ) {
					// get_image() returns safe <img> markup, using the WooCommerce placeholder when no image is set.
					$image = $product->get_image( 'woocommerce_gallery_thumbnail', array( 'class' => 'order-item-image' ) );
				}

				if ( $show_sku ) {
					$sku = (string) $product->get_sku();
				}
			}

			/**
			 * Filter the product URL used for the order item name link.
			 *
			 * Return an empty string to disable linking for a specific item.
			 *
			 * @param string        $url   Product URL (or empty string for no link).
			 * @param WC_Order_Item $item  Order item object.
			 * @param WC_Order      $order Order object.
			 */
			$url = apply_filters( 'wccty_order_item_product_url', $url, $item, $order );

			$rows[] = array(
				'name'     => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'total'    => wc_price( $item->get_total(), array( 'currency' => $currency ) ),
				'url'      => $url,
				'image'    => $image,
				'sku'      => $sku,
			);
		}

		return $rows;
	}
}
