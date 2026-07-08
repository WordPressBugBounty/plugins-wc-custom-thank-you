<?php
/**
 * Order Confirmation block — "Default" template.
 *
 * The original dashboard layout: header, details cards, items table, compact
 * totals and payment box. Rendered by includes/../build/render.php, which
 * provides all the $wccty_* variables used here.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="order-header">
	<h2>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: order number. */
				__( 'Order #%s', 'wc-custom-thank-you' ),
				$wccty_order_data['order_number']
			)
		);
		?>
	</h2>
	<p class="order-date">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: formatted order date. */
				__( 'Order Date: %s', 'wc-custom-thank-you' ),
				$wccty_order_date
			)
		);
		?>
	</p>
	<p class="order-status">
		<strong><?php esc_html_e( 'Status:', 'wc-custom-thank-you' ); ?></strong>
		<span class="status-badge"><?php echo esc_html( wc_get_order_status_name( $wccty_order_data['status'] ) ); ?></span>
	</p>
</div>

<?php if ( $wccty_show['showOrderNote'] && '' !== (string) $wccty_order_data['customer_note'] ) : ?>
	<div class="order-customer-note">
		<h3><?php esc_html_e( 'Order Note', 'wc-custom-thank-you' ); ?></h3>
		<?php /* Customer note is plain text entered at checkout; escape then preserve line breaks. */ ?>
		<p><?php echo nl2br( esc_html( $wccty_order_data['customer_note'] ) ); ?></p>
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

<?php if ( $wccty_show['showOrderItems'] ) : ?>
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
				<?php foreach ( $wccty_items_display as $wccty_item_row ) : ?>
					<tr>
						<td>
							<div class="order-item-product">
								<?php echo wp_kses_post( $wccty_item_row['image'] ); ?>
								<div class="order-item-meta">
									<?php if ( $wccty_item_row['url'] ) : ?>
										<a class="order-item-name" href="<?php echo esc_url( $wccty_item_row['url'] ); ?>"><?php echo esc_html( $wccty_item_row['name'] ); ?></a>
									<?php else : ?>
										<span class="order-item-name"><?php echo esc_html( $wccty_item_row['name'] ); ?></span>
									<?php endif; ?>
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
							</div>
						</td>
						<td><?php echo esc_html( $wccty_item_row['quantity'] ); ?></td>
						<td><?php echo wp_kses_post( $wccty_item_row['total'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
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

<?php if ( $wccty_show['showPaymentMethod'] && $wccty_order_data['payment_method'] ) : ?>
	<div class="payment-method">
		<p><strong><?php esc_html_e( 'Payment Method:', 'wc-custom-thank-you' ); ?></strong> <?php echo esc_html( $wccty_order_data['payment_method'] ); ?></p>
	</div>
<?php endif; ?>
