<?php
/**
 * Admin settings integration.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Admin', false ) ) {
	return;
}

/**
 * Adds the Custom Thank You option in WooCommerce settings.
 */
final class WCCTY_Admin {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'woocommerce_settings_pages', array( $this, 'add_settings' ) );

		// Ensure our option is strictly sanitized (WooCommerce settings API sanitizes, but we harden it).
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'sanitize_option' ), 10, 3 );
	}

	/**
	 * Add the Thank You page dropdown in Settings > Advanced > Page setup.
	 *
	 * @param  array $settings The core settings.
	 * @return array
	 */
	public function add_settings( $settings ) {
		$settings[] = array(
			'title' => esc_html__( 'Custom Thank You', 'wc-custom-thank-you' ),
			'type'  => 'title',
			'id'    => 'wc_custom_thankyou_options',
		);

		$settings[] = array(
			'title'    => esc_html__( 'Thank You Page', 'wc-custom-thank-you' ),
			'id'       => 'woocommerce_custom_thankyou_page_id',
			'type'     => 'single_select_page',
			'default'  => '',
			'class'    => 'wc-enhanced-select-nostd',
			'css'      => 'min-width:300px;',
			'desc_tip' => true,
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wc_custom_thankyou_options',
		);

		return $settings;
	}

	/**
	 * Sanitize our settings field value.
	 *
	 * @param mixed $value     Already-sanitized value.
	 * @param array $option    Option definition (includes the `id`).
	 * @param mixed $raw_value Raw (unsanitized) value.
	 * @return mixed
	 */
	public function sanitize_option( $value, $option, $raw_value ) {
		if ( empty( $option['id'] ) || WCCTY_Frontend::OPTION_PAGE_ID !== $option['id'] ) {
			return $value;
		}

		$page_id = absint( $raw_value );

		if ( $page_id > 0 ) {
			$post = get_post( $page_id );

			if ( ! ( $post instanceof WP_Post ) || 'page' !== $post->post_type || 'trash' === $post->post_status ) {
				$page_id = 0;
			}
		}

		return $page_id ? (string) $page_id : '';
	}
}