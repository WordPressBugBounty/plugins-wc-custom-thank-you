<?php
/**
 * Render callback for the Custom Thank You block.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes is provided by the block render context. */
$wccty_attributes = ( isset( $attributes ) && is_array( $attributes ) ) ? $attributes : array();

$wccty_accent_color = isset( $wccty_attributes['accentColor'] ) ? sanitize_hex_color( $wccty_attributes['accentColor'] ) : '';
if ( ! $wccty_accent_color ) {
	$wccty_accent_color = '#4caf50';
}

$wccty_accent_text_color = isset( $wccty_attributes['accentTextColor'] ) ? sanitize_hex_color( $wccty_attributes['accentTextColor'] ) : '';
if ( ! $wccty_accent_text_color ) {
	$wccty_accent_text_color = '#ffffff';
}

$wccty_section_background_color = isset( $wccty_attributes['sectionBackgroundColor'] ) ? sanitize_hex_color( $wccty_attributes['sectionBackgroundColor'] ) : '';
if ( ! $wccty_section_background_color ) {
	$wccty_section_background_color = '#f9f9f9';
}

$wccty_payment_background_color = isset( $wccty_attributes['paymentBackgroundColor'] ) ? sanitize_hex_color( $wccty_attributes['paymentBackgroundColor'] ) : '';
if ( ! $wccty_payment_background_color ) {
	$wccty_payment_background_color = '#e3f2fd';
}

$wccty_payment_border_color = isset( $wccty_attributes['paymentBorderColor'] ) ? sanitize_hex_color( $wccty_attributes['paymentBorderColor'] ) : '';
if ( ! $wccty_payment_border_color ) {
	$wccty_payment_border_color = '#2196f3';
}

$wccty_payment_text_color = isset( $wccty_attributes['paymentTextColor'] ) ? sanitize_hex_color( $wccty_attributes['paymentTextColor'] ) : '';
if ( ! $wccty_payment_text_color ) {
	$wccty_payment_text_color = '#333333';
}

$wccty_inline_style = sprintf(
	'--wccty-accent-color:%1$s;--wccty-accent-text-color:%2$s;--wccty-section-bg:%3$s;--wccty-payment-bg:%4$s;--wccty-payment-border-color:%5$s;--wccty-payment-text-color:%6$s;',
	$wccty_accent_color,
	$wccty_accent_text_color,
	$wccty_section_background_color,
	$wccty_payment_background_color,
	$wccty_payment_border_color,
	$wccty_payment_text_color
);

$wccty_wrapper_attributes = get_block_wrapper_attributes(
	array(
		'style' => $wccty_inline_style,
	)
);

// Check if WooCommerce is active.
if ( ! class_exists( 'WooCommerce' ) ) {
	?>
	<div <?php echo wp_kses_data( $wccty_wrapper_attributes ); ?>>
		<div class="order-confirmation-error">
			<p><?php esc_html_e( 'WooCommerce is not installed or activated. This block requires WooCommerce to function.', 'wc-custom-thank-you' ); ?></p>
		</div>
	</div>
	<?php
	return;
}

/*
 * Resolve the order being viewed.
 *
 * On the "Order received" endpoint the order ID is typically in the query var
 * (pretty permalinks), while the order key is passed as a `key` query string.
 * Relying on the `order-received` query parameter fails for the common permalink structure:
 * /checkout/order-received/123/?key=wc_order_...
 */
global $wp;

$wccty_order_id  = absint( get_query_var( 'order-received' ) );
$wccty_order_key = '';

if ( ! $wccty_order_id && isset( $wp->query_vars['order-received'] ) ) {
	$wccty_order_id = absint( $wp->query_vars['order-received'] );
}

/*
 * Fallback for non-pretty permalinks (/?order-received=123).
 *
 * Use filter_input() to avoid direct processing of superglobals and ensure the
 * value is validated as an integer.
 */
if ( ! $wccty_order_id ) {
	$wccty_order_received = filter_input(
		INPUT_GET,
		'order-received',
		FILTER_VALIDATE_INT,
		array(
			'options' => array(
				'min_range' => 1,
			),
		)
	);

	if ( null !== $wccty_order_received && false !== $wccty_order_received ) {
		$wccty_order_id = absint( $wccty_order_received );
	}
}

/*
 * Order key passed as `?key=...`.
 *
 * Use filter_input() to avoid direct processing of superglobals, then sanitize
 * and validate to an expected safe format.
 */
$wccty_order_key_raw = filter_input( INPUT_GET, 'key', FILTER_UNSAFE_RAW );
if ( is_string( $wccty_order_key_raw ) && '' !== $wccty_order_key_raw ) {
	$wccty_order_key = sanitize_text_field( $wccty_order_key_raw );

	// Basic validation: only allow characters used by WooCommerce order keys.
	if ( $wccty_order_key && ( 64 < strlen( $wccty_order_key ) || ! preg_match( '/^[A-Za-z0-9_]+$/', $wccty_order_key ) ) ) {
		$wccty_order_key = '';
	}
}

// If we couldn't get the order ID, attempt to derive it from the order key.
if ( ! $wccty_order_id && $wccty_order_key ) {
	$wccty_order_id = absint( wc_get_order_id_by_order_key( $wccty_order_key ) );
}

$wccty_order = $wccty_order_id ? wc_get_order( $wccty_order_id ) : false;

// Security: require a valid order key for guests; allow logged-in owners.
if ( $wccty_order ) {
	$wccty_is_valid = false;

	if ( $wccty_order_key && hash_equals( $wccty_order->get_order_key(), $wccty_order_key ) ) {
		$wccty_is_valid = true;
	} elseif ( is_user_logged_in() && (int) $wccty_order->get_user_id() === (int) get_current_user_id() ) {
		$wccty_is_valid = true;
	}

	if ( ! $wccty_is_valid ) {
		$wccty_order = false;
	}
}

// Check if order exists.
if ( ! $wccty_order ) {
	?>
	<div <?php echo wp_kses_data( $wccty_wrapper_attributes ); ?>>
		<div class="order-confirmation-error">
			<p><?php esc_html_e( 'No order found. Please complete a purchase to view order details.', 'wc-custom-thank-you' ); ?></p>
		</div>
	</div>
	<?php
	return;
}

// Get order data.
$wccty_order_data = array(
	'id'             => $wccty_order->get_id(),
	'order_number'   => $wccty_order->get_order_number(),
	'date_created'   => $wccty_order->get_date_created(),
	'status'         => $wccty_order->get_status(),
	'currency'       => $wccty_order->get_currency(),
	'total'          => $wccty_order->get_total(),
	'subtotal'       => $wccty_order->get_subtotal(),
	'shipping_total' => $wccty_order->get_shipping_total(),
	'tax_total'      => $wccty_order->get_total_tax(),
	'payment_method' => $wccty_order->get_payment_method_title(),
	'customer_note'  => $wccty_order->get_customer_note(),
);

// Get billing address.
$wccty_billing_address = array(
	'first_name' => $wccty_order->get_billing_first_name(),
	'last_name'  => $wccty_order->get_billing_last_name(),
	'company'    => $wccty_order->get_billing_company(),
	'address_1'  => $wccty_order->get_billing_address_1(),
	'address_2'  => $wccty_order->get_billing_address_2(),
	'city'       => $wccty_order->get_billing_city(),
	'state'      => $wccty_order->get_billing_state(),
	'postcode'   => $wccty_order->get_billing_postcode(),
	'country'    => $wccty_order->get_billing_country(),
	'email'      => $wccty_order->get_billing_email(),
	'phone'      => $wccty_order->get_billing_phone(),
);

// Get shipping address.
$wccty_shipping_address = array(
	'first_name' => $wccty_order->get_shipping_first_name(),
	'last_name'  => $wccty_order->get_shipping_last_name(),
	'company'    => $wccty_order->get_shipping_company(),
	'address_1'  => $wccty_order->get_shipping_address_1(),
	'address_2'  => $wccty_order->get_shipping_address_2(),
	'city'       => $wccty_order->get_shipping_city(),
	'state'      => $wccty_order->get_shipping_state(),
	'postcode'   => $wccty_order->get_shipping_postcode(),
	'country'    => $wccty_order->get_shipping_country(),
);

// Get order items.
$wccty_items = $wccty_order->get_items();

?>
<div <?php echo wp_kses_data( $wccty_wrapper_attributes ); ?>>
	<div class="order-header">
		<h2>
			<?php
			$wccty_order_heading = sprintf(
				/* translators: %s: order number. */
				__( 'Order #%s', 'wc-custom-thank-you' ),
				$wccty_order_data['order_number']
			);
			echo esc_html( $wccty_order_heading );
			?>
		</h2>
		<p class="order-date">
			<?php
			$wccty_date_format = get_option( 'date_format' );
			if ( ! is_string( $wccty_date_format ) || '' === $wccty_date_format ) {
				$wccty_date_format = 'F j, Y';
			}

			$wccty_formatted_order_date = '';
			if ( $wccty_order_data['date_created'] instanceof WC_DateTime ) {
				$wccty_formatted_order_date = $wccty_order_data['date_created']->date_i18n( $wccty_date_format );
			}

			if ( '' === $wccty_formatted_order_date ) {
				$wccty_formatted_order_date = __( 'N/A', 'wc-custom-thank-you' );
			}

			$wccty_order_date = sprintf(
				/* translators: %s: formatted order date. */
				__( 'Order Date: %s', 'wc-custom-thank-you' ),
				$wccty_formatted_order_date
			);
			echo esc_html( $wccty_order_date );
			?>
		</p>
		<p class="order-status">
			<strong><?php esc_html_e( 'Status:', 'wc-custom-thank-you' ); ?></strong>
			<span class="status-badge"><?php echo esc_html( wc_get_order_status_name( $wccty_order_data['status'] ) ); ?></span>
		</p>
	</div>

	<div class="order-details">
		<div class="customer-details">
			<h3><?php esc_html_e( 'Customer Details', 'wc-custom-thank-you' ); ?></h3>
			<p>
				<?php echo esc_html( $wccty_billing_address['first_name'] . ' ' . $wccty_billing_address['last_name'] ); ?><br>
				<?php echo esc_html( $wccty_billing_address['email'] ); ?><br>
				<?php if ( $wccty_billing_address['phone'] ) : ?>
					<?php echo esc_html( $wccty_billing_address['phone'] ); ?>
				<?php endif; ?>
			</p>
		</div>

		<div class="addresses">
			<div class="billing-address">
				<h3><?php esc_html_e( 'Billing Address', 'wc-custom-thank-you' ); ?></h3>
				<p>
					<?php if ( $wccty_billing_address['company'] ) : ?>
						<?php echo esc_html( $wccty_billing_address['company'] ); ?><br>
					<?php endif; ?>
					<?php echo esc_html( $wccty_billing_address['address_1'] ); ?><br>
					<?php if ( $wccty_billing_address['address_2'] ) : ?>
						<?php echo esc_html( $wccty_billing_address['address_2'] ); ?><br>
					<?php endif; ?>
					<?php echo esc_html( $wccty_billing_address['city'] . ', ' . $wccty_billing_address['state'] . ' ' . $wccty_billing_address['postcode'] ); ?><br>
					<?php echo esc_html( WC()->countries->countries[ $wccty_billing_address['country'] ] ?? $wccty_billing_address['country'] ); ?>
				</p>
			</div>

			<div class="shipping-address">
				<h3><?php esc_html_e( 'Shipping Address', 'wc-custom-thank-you' ); ?></h3>
				<?php if ( $wccty_shipping_address['address_1'] ) : ?>
					<p>
						<?php if ( $wccty_shipping_address['company'] ) : ?>
							<?php echo esc_html( $wccty_shipping_address['company'] ); ?><br>
						<?php endif; ?>
						<?php echo esc_html( $wccty_shipping_address['address_1'] ); ?><br>
						<?php if ( $wccty_shipping_address['address_2'] ) : ?>
							<?php echo esc_html( $wccty_shipping_address['address_2'] ); ?><br>
						<?php endif; ?>
						<?php echo esc_html( $wccty_shipping_address['city'] . ', ' . $wccty_shipping_address['state'] . ' ' . $wccty_shipping_address['postcode'] ); ?><br>
						<?php echo esc_html( WC()->countries->countries[ $wccty_shipping_address['country'] ] ?? $wccty_shipping_address['country'] ); ?>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'Same as billing address', 'wc-custom-thank-you' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="order-items">
		<h3><?php esc_html_e( 'Order Items', 'wc-custom-thank-you' ); ?></h3>
		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Product', 'wc-custom-thank-you' ); ?></th>
					<th><?php esc_html_e( 'Quantity', 'wc-custom-thank-you' ); ?></th>
					<th><?php esc_html_e( 'Price', 'wc-custom-thank-you' ); ?></th>
				</tr>
			</thead>
			<tbody>

				<?php foreach ( $wccty_items as $wccty_item ) : ?>
					<?php
					$wccty_product_name = $wccty_item->get_name();
					$wccty_quantity     = $wccty_item->get_quantity();
					$wccty_total        = $wccty_item->get_total();

					$wccty_product     = $wccty_item->get_product();
					$wccty_product_url = '';

					if ( $wccty_product instanceof WC_Product ) {
						$wccty_product_id = $wccty_product->get_id();

						// Variations are not publicly queryable; link to the parent product when possible.
						if ( $wccty_product->is_type( 'variation' ) && $wccty_product->get_parent_id() ) {
							$wccty_product_id = $wccty_product->get_parent_id();
						}

						// Only link to published products (avoid 404s for trashed/private items).
						if ( 'publish' === get_post_status( $wccty_product_id ) ) {
							$wccty_link_product = $wccty_product;

							if ( $wccty_product_id !== $wccty_product->get_id() ) {
								$wccty_link_product = wc_get_product( $wccty_product_id );
							}

							if ( $wccty_link_product instanceof WC_Product ) {
								$wccty_product_url = $wccty_link_product->get_permalink( $wccty_item );
							}
						}
					}

					/**
					 * Filter the product URL used for the order item name link.
					 *
					 * Return an empty string to disable linking for a specific item.
					 *
					 * @param string        $wccty_product_url Product URL (or empty string for no link).
					 * @param WC_Order_Item $wccty_item        Order item object.
					 * @param WC_Order      $wccty_order       Order object.
					 */
					$wccty_product_url = apply_filters( 'wccty_order_item_product_url', $wccty_product_url, $wccty_item, $wccty_order );
					?>
					<tr>
						<td>
							<?php if ( $wccty_product_url ) : ?>
								<a href="<?php echo esc_url( $wccty_product_url ); ?>">
									<?php echo esc_html( $wccty_product_name ); ?>
								</a>
							<?php else : ?>
								<?php echo esc_html( $wccty_product_name ); ?>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $wccty_quantity ); ?></td>
						<td><?php echo wp_kses_post( wc_price( $wccty_total, array( 'currency' => $wccty_order_data['currency'] ) ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="order-totals">
		<table>
			<tbody>
				<tr>
					<th><?php esc_html_e( 'Subtotal:', 'wc-custom-thank-you' ); ?></th>
					<td><?php echo wp_kses_post( wc_price( $wccty_order_data['subtotal'], array( 'currency' => $wccty_order_data['currency'] ) ) ); ?></td>
				</tr>
				<?php if ( $wccty_order_data['shipping_total'] > 0 ) : ?>
					<tr>
						<th><?php esc_html_e( 'Shipping:', 'wc-custom-thank-you' ); ?></th>
						<td><?php echo wp_kses_post( wc_price( $wccty_order_data['shipping_total'], array( 'currency' => $wccty_order_data['currency'] ) ) ); ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( $wccty_order_data['tax_total'] > 0 ) : ?>
					<tr>
						<th><?php esc_html_e( 'Tax:', 'wc-custom-thank-you' ); ?></th>
						<td><?php echo wp_kses_post( wc_price( $wccty_order_data['tax_total'], array( 'currency' => $wccty_order_data['currency'] ) ) ); ?></td>
					</tr>
				<?php endif; ?>
				<tr class="order-total">
					<th><?php esc_html_e( 'Total:', 'wc-custom-thank-you' ); ?></th>
					<td><?php echo wp_kses_post( wc_price( $wccty_order_data['total'], array( 'currency' => $wccty_order_data['currency'] ) ) ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<?php if ( $wccty_order_data['payment_method'] ) : ?>
		<div class="payment-method">
			<p><strong><?php esc_html_e( 'Payment Method:', 'wc-custom-thank-you' ); ?></strong> <?php echo esc_html( $wccty_order_data['payment_method'] ); ?></p>
		</div>
	<?php endif; ?>
</div>