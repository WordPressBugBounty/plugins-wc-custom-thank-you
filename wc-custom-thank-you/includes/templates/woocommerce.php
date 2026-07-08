<?php
/**
 * Order Confirmation block — "WooCommerce Core" template.
 *
 * Renders WooCommerce's native order-received markup and classes (order overview,
 * order details table, customer details columns) so it inherits the active
 * theme's WooCommerce styling. Rendered by build/render.php, which provides all
 * the $wccty_* variables used here.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="woocommerce">
	<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
		<?php esc_html_e( 'Thank you. Your order has been received.', 'wc-custom-thank-you' ); ?>
	</p>

	<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
		<li class="woocommerce-order-overview__order order">
			<?php esc_html_e( 'Order number:', 'wc-custom-thank-you' ); ?>
			<strong><?php echo esc_html( $wccty_order_data['order_number'] ); ?></strong>
		</li>
		<li class="woocommerce-order-overview__date date">
			<?php esc_html_e( 'Date:', 'wc-custom-thank-you' ); ?>
			<strong><?php echo esc_html( $wccty_order_date ); ?></strong>
		</li>
		<?php if ( $wccty_billing_address['email'] ) : ?>
			<li class="woocommerce-order-overview__email email">
				<?php esc_html_e( 'Email:', 'wc-custom-thank-you' ); ?>
				<strong><?php echo esc_html( $wccty_billing_address['email'] ); ?></strong>
			</li>
		<?php endif; ?>
		<?php if ( $wccty_show['showOrderTotals'] ) : ?>
			<li class="woocommerce-order-overview__total total">
				<?php esc_html_e( 'Total:', 'wc-custom-thank-you' ); ?>
				<strong><?php echo wp_kses_post( wc_price( $wccty_order_data['total'], array( 'currency' => $wccty_order_data['currency'] ) ) ); ?></strong>
			</li>
		<?php endif; ?>
		<?php if ( $wccty_show['showPaymentMethod'] && $wccty_order_data['payment_method'] ) : ?>
			<li class="woocommerce-order-overview__payment-method method">
				<?php esc_html_e( 'Payment method:', 'wc-custom-thank-you' ); ?>
				<strong><?php echo esc_html( $wccty_order_data['payment_method'] ); ?></strong>
			</li>
		<?php endif; ?>
	</ul>

	<?php if ( $wccty_show['showOrderNote'] && '' !== (string) $wccty_order_data['customer_note'] ) : ?>
		<p class="woocommerce-thankyou-order-note">
			<?php echo nl2br( esc_html( $wccty_order_data['customer_note'] ) ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $wccty_show['showOrderItems'] || ( $wccty_show['showOrderTotals'] && ! empty( $wccty_order_totals ) ) ) : ?>
		<section class="woocommerce-order-details">
			<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'wc-custom-thank-you' ); ?></h2>
			<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
				<?php if ( $wccty_show['showOrderItems'] ) : ?>
					<thead>
						<tr>
							<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'wc-custom-thank-you' ); ?></th>
							<th class="woocommerce-table__product-total product-total"><?php esc_html_e( 'Total', 'wc-custom-thank-you' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $wccty_items_display as $wccty_item_row ) : ?>
							<tr class="woocommerce-table__line-item order_item">
								<td class="woocommerce-table__product-name product-name">
									<div class="wccty-woo-item">
										<?php if ( '' !== (string) $wccty_item_row['image'] ) : ?>
											<span class="wccty-woo-item-image"><?php echo wp_kses_post( $wccty_item_row['image'] ); ?></span>
										<?php endif; ?>
										<span class="wccty-woo-item-name">
											<?php if ( $wccty_item_row['url'] ) : ?>
												<a href="<?php echo esc_url( $wccty_item_row['url'] ); ?>"><?php echo esc_html( $wccty_item_row['name'] ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $wccty_item_row['name'] ); ?>
											<?php endif; ?>
											<strong class="product-quantity">
												<?php
												echo esc_html(
													sprintf(
														/* translators: %s: item quantity. */
														__( '× %s', 'wc-custom-thank-you' ),
														$wccty_item_row['quantity']
													)
												);
												?>
											</strong>
											<?php if ( '' !== (string) $wccty_item_row['sku'] ) : ?>
												<br><small class="wccty-sku">
													<?php
													printf(
														/* translators: %s: product SKU. */
														esc_html__( 'SKU: %s', 'wc-custom-thank-you' ),
														esc_html( $wccty_item_row['sku'] )
													);
													?>
												</small>
											<?php endif; ?>
										</span>
									</div>
								</td>
								<td class="woocommerce-table__product-total product-total">
									<?php echo wp_kses_post( $wccty_item_row['total'] ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				<?php endif; ?>

				<?php if ( $wccty_show['showOrderTotals'] && ! empty( $wccty_order_totals ) ) : ?>
					<tfoot>
						<?php foreach ( $wccty_order_totals as $wccty_total_row ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $wccty_total_row['label'] ); ?></th>
								<td><?php echo wp_kses_post( $wccty_total_row['value'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tfoot>
				<?php endif; ?>
			</table>
		</section>
	<?php endif; ?>

	<?php if ( $wccty_show['showDownloads'] && ! empty( $wccty_downloads ) ) : ?>
		<section class="woocommerce-order-downloads">
			<h2 class="woocommerce-order-downloads__title"><?php esc_html_e( 'Downloads', 'wc-custom-thank-you' ); ?></h2>
			<ul class="woocommerce-order-downloads__list">
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
		</section>
	<?php endif; ?>

	<?php if ( $wccty_show_details && $wccty_show_addresses ) : ?>
		<section class="woocommerce-customer-details">
			<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
				<?php if ( $wccty_show['showBillingAddress'] ) : ?>
					<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
						<h2 class="woocommerce-column__title"><?php esc_html_e( 'Billing address', 'wc-custom-thank-you' ); ?></h2>
						<address>
							<?php echo wp_kses_post( $wccty_order->get_formatted_billing_address( esc_html__( 'N/A', 'wc-custom-thank-you' ) ) ); ?>
							<?php if ( $wccty_billing_address['phone'] ) : ?>
								<p class="woocommerce-customer-details--phone"><?php echo esc_html( $wccty_billing_address['phone'] ); ?></p>
							<?php endif; ?>
							<?php if ( $wccty_billing_address['email'] ) : ?>
								<p class="woocommerce-customer-details--email"><?php echo esc_html( $wccty_billing_address['email'] ); ?></p>
							<?php endif; ?>
						</address>
					</div>
				<?php endif; ?>

				<?php if ( $wccty_show['showShippingAddress'] ) : ?>
					<div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
						<h2 class="woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'wc-custom-thank-you' ); ?></h2>
						<address>
							<?php
							$wccty_ship_formatted = $wccty_order->get_formatted_shipping_address();
							if ( $wccty_ship_formatted ) {
								echo wp_kses_post( $wccty_ship_formatted );
							} else {
								esc_html_e( 'Same as billing address', 'wc-custom-thank-you' );
							}
							?>
						</address>
					</div>
				<?php endif; ?>
			</section>
		</section>
	<?php endif; ?>
</div>
