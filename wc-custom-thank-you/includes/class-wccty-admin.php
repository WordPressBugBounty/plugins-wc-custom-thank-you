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
 * Adds the Order Confirmation option in WooCommerce settings.
 */
final class WCCTY_Admin {

	/**
	 * Anchor id rendered just before the settings heading, so the plugin's
	 * "Settings" action link can jump straight to the Order Confirmation section.
	 *
	 * @var string
	 */
	const SETTINGS_ANCHOR = 'wc_custom_thankyou_settings';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'woocommerce_settings_pages', array( $this, 'add_settings' ) );

		// Render the anchor for our custom `wccty_anchor` settings field type.
		add_action( 'woocommerce_admin_field_wccty_anchor', array( $this, 'render_anchor' ) );

		// Ensure our option is strictly sanitized (WooCommerce settings API sanitizes, but we harden it).
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'sanitize_option' ), 10, 3 );
	}

	/**
	 * Add the Order Confirmation page dropdown in Settings > Advanced > Page setup.
	 *
	 * @param  array $settings The core settings.
	 * @return array
	 */
	public function add_settings( $settings ) {
		// Empty anchor rendered just before the section heading, so the plugin's
		// "Settings" link can deep-link to this section (see render_anchor()).
		$settings[] = array(
			'type' => 'wccty_anchor',
			'id'   => self::SETTINGS_ANCHOR,
		);

		$settings[] = array(
			'title' => esc_html__( 'Order Confirmation', 'wc-custom-thank-you' ),
			'type'  => 'title',
			'id'    => 'wc_custom_thankyou_options',
		);

		$settings[] = array(
			'title'    => esc_html__( 'Order confirmation page', 'wc-custom-thank-you' ),
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
	 * Render the deep-link anchor for the `wccty_anchor` settings field type.
	 *
	 * WooCommerce renders section titles as a plain <h2> with no id, so this
	 * empty element (placed immediately before the heading) gives the "Settings"
	 * action link a stable target. `scroll-margin-top` keeps the heading clear of
	 * the fixed admin bar when the browser jumps to it.
	 *
	 * @param array $field Field definition (expects `id`).
	 * @return void
	 */
	public function render_anchor( $field ) {
		$id = ( is_array( $field ) && ! empty( $field['id'] ) ) ? $field['id'] : self::SETTINGS_ANCHOR;

		printf(
			'<div id="%s" style="scroll-margin-top:60px" aria-hidden="true"></div>',
			esc_attr( $id )
		);
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