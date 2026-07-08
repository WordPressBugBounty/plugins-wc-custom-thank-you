<?php
/**
 * Order Confirmation block — "Hero + summary cards" template.
 *
 * A bold success banner, a row of at-a-glance stat cards, then the order items,
 * totals and addresses. Rendered by build/render.php, which provides all the
 * $wccty_* variables used here.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wccty-hero-banner">
	<span class="wccty-hero-check" aria-hidden="true">
		<svg viewBox="0 0 24 24" width="28" height="28" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
		</svg>
	</span>
	<?php
	$wccty_hero_title = isset( $wccty_attributes['heroTitle'] ) ? (string) $wccty_attributes['heroTitle'] : '';
	if ( '' === trim( wp_strip_all_tags( $wccty_hero_title ) ) ) {
		$wccty_hero_title = __( 'Thank you for your order!', 'wc-custom-thank-you' );
	}
	?>
	<h2 class="wccty-hero-title"><?php echo wp_kses_post( $wccty_hero_title ); ?></h2>
	<p class="wccty-hero-subtitle">
		<?php
		echo esc_html(
			sprintf(
				/* translators: 1: order number, 2: order status. */
				__( 'Order #%1$s · %2$s', 'wc-custom-thank-you' ),
				$wccty_order_data['order_number'],
				wc_get_order_status_name( $wccty_order_data['status'] )
			)
		);
		?>
	</p>
</div>

<div class="wccty-hero-cards">
	<div class="wccty-hero-card">
		<span class="wccty-hero-card-label"><?php esc_html_e( 'Order date', 'wc-custom-thank-you' ); ?></span>
		<span class="wccty-hero-card-value"><?php echo esc_html( $wccty_order_date ); ?></span>
	</div>

	<?php if ( $wccty_show['showOrderTotals'] ) : ?>
		<div class="wccty-hero-card">
			<span class="wccty-hero-card-label"><?php esc_html_e( 'Total', 'wc-custom-thank-you' ); ?></span>
			<span class="wccty-hero-card-value"><?php echo wp_kses_post( wc_price( $wccty_order_data['total'], array( 'currency' => $wccty_order_data['currency'] ) ) ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( $wccty_show['showPaymentMethod'] && $wccty_order_data['payment_method'] ) : ?>
		<div class="wccty-hero-card">
			<span class="wccty-hero-card-label"><?php esc_html_e( 'Payment', 'wc-custom-thank-you' ); ?></span>
			<span class="wccty-hero-card-value"><?php echo esc_html( $wccty_order_data['payment_method'] ); ?></span>
		</div>
	<?php endif; ?>
</div>

<?php if ( $wccty_show['showOrderNote'] && '' !== (string) $wccty_order_data['customer_note'] ) : ?>
	<div class="order-customer-note">
		<h3><?php esc_html_e( 'Order Note', 'wc-custom-thank-you' ); ?></h3>
		<p><?php echo nl2br( esc_html( $wccty_order_data['customer_note'] ) ); ?></p>
	</div>
<?php endif; ?>

<?php if ( $wccty_show['showOrderItems'] ) : ?>
	<div class="wccty-hero-items">
		<h3><?php esc_html_e( 'Order Items', 'wc-custom-thank-you' ); ?></h3>
		<ul class="wccty-hero-item-list">
			<?php foreach ( $wccty_items_display as $wccty_item_row ) : ?>
				<li class="wccty-hero-item">
					<?php echo wp_kses_post( $wccty_item_row['image'] ); ?>
					<div class="wccty-hero-item-meta">
						<?php if ( $wccty_item_row['url'] ) : ?>
							<a class="order-item-name" href="<?php echo esc_url( $wccty_item_row['url'] ); ?>"><?php echo esc_html( $wccty_item_row['name'] ); ?></a>
						<?php else : ?>
							<span class="order-item-name"><?php echo esc_html( $wccty_item_row['name'] ); ?></span>
						<?php endif; ?>
						<span class="wccty-hero-item-qty">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: quantity ordered. */
									__( 'Qty: %s', 'wc-custom-thank-you' ),
									$wccty_item_row['quantity']
								)
							);
							?>
						</span>
						<?php if ( '' !== (string) $wccty_item_row['sku'] ) : ?>
							<span class="order-item-sku">
								<?php
								printf(
									/* translators: %s: product SKU. */
									esc_html__( 'SKU: %s', 'wc-custom-thank-you' ),
									esc_html( $wccty_item_row['sku'] )
								);
								?>
							</span>
						<?php endif; ?>
					</div>
					<span class="wccty-hero-item-price"><?php echo wp_kses_post( $wccty_item_row['total'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php if ( $wccty_show['showDownloads'] && ! empty( $wccty_downloads ) ) : ?>
	<div class="order-downloads">
		<h3><?php esc_html_e( 'Downloads', 'wc-custom-thank-you' ); ?></h3>
		<ul>
			<?php foreach ( $wccty_downloads as $wccty_download ) : ?>
				<?php
				$wccty_download_url  = ! empty( $wccty_download['download_url'] ) ? $wccty_download['download_url'] : '';
				$wccty_download_name = '';

				if ( ! empty( $wccty_download['download_name'] ) ) {
					$wccty_download_name = $wccty_download['download_name'];
				} elseif ( ! empty( $wccty_download['product_name'] ) ) {
					$wccty_download_name = $wccty_download['product_name'];
				}

				if ( '' === $wccty_download_name ) {
					$wccty_download_name = __( 'Download', 'wc-custom-thank-you' );
				}
				?>
				<li>
					<?php if ( $wccty_download_url ) : ?>
						<a href="<?php echo esc_url( $wccty_download_url ); ?>"><?php echo esc_html( $wccty_download_name ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $wccty_download_name ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php if ( $wccty_show['showOrderTotals'] && ! empty( $wccty_order_totals ) ) : ?>
	<div class="order-totals">
		<table>
			<tbody>
				<?php foreach ( $wccty_order_totals as $wccty_total_key => $wccty_total_row ) : ?>
					<tr class="<?php echo esc_attr( 'order_total' === $wccty_total_key ? 'order-total' : 'order-total-line order-total-' . $wccty_total_key ); ?>">
						<th><?php echo esc_html( $wccty_total_row['label'] ); ?></th>
						<td><?php echo wp_kses_post( $wccty_total_row['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

<?php if ( $wccty_show_details ) : ?>
	<div class="order-details">
		<?php if ( $wccty_show['showCustomerDetails'] ) : ?>
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
		<?php endif; ?>

		<?php if ( $wccty_show_addresses ) : ?>
			<div class="addresses">
				<?php if ( $wccty_show['showBillingAddress'] ) : ?>
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
				<?php endif; ?>

				<?php if ( $wccty_show['showShippingAddress'] ) : ?>
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
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
