<?php
/**
 * Plugin bootstrapper.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Bootstrap', false ) ) {
	return;
}

/**
 * Handles plugin bootstrapping (hooks registration, blocks, settings link, etc).
 */
final class WCCTY_Bootstrap {

	/**
	 * Boot the plugin.
	 *
	 * @return void
	 */
	public static function init() {
		// WooCommerce feature compatibility (HPOS + Cart & Checkout Blocks).
		add_action( 'before_woocommerce_init', array( 'WCCTY_Compatibility', 'declare' ) );

		// i18n.
		self::boot_i18n();

		// Bootstrap the plugin once WooCommerce is initialized.
		add_action( 'woocommerce_loaded', array( __CLASS__, 'boot_plugin' ) );

		// Blocks.
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );

		// Plugin action links.
		add_filter(
			'plugin_action_links_' . plugin_basename( WC_CUSTOM_THANKYOU_FILE ),
			array( __CLASS__, 'plugin_action_links' ),
			PHP_INT_MAX
		);
	}

	/**
	 * Boot i18n.
	 *
	 * @return void
	 */
	private static function boot_i18n() {
		$i18n = new WCCTY_I18n();
		$i18n->init();
	}

	/**
	 * Boot the main plugin orchestrator.
	 *
	 * @return void
	 */
	public static function boot_plugin() {
		WC_Custom_Thankyou::instance();
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 *
	 * @return void
	 */
	public static function register_blocks() {
		register_block_type( WC_CUSTOM_THANKYOU_PATH . 'build/' );
	}

	/**
	 * Add a "Settings" action link on the Plugins screen.
	 *
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		if ( current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- `manage_woocommerce` is a WooCommerce capability (registered by WooCommerce); used here to restrict access to the Settings link.
			$settings_url = add_query_arg(
				array(
					'page' => 'wc-settings',
					'tab'  => 'advanced',
				),
				admin_url( 'admin.php' )
			);

			$settings_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $settings_url ),
				esc_html__( 'Settings', 'wc-custom-thank-you' )
			);

			// Ensure the Settings link is always the first action link.
			$links = array_merge(
				array(
					'settings' => $settings_link,
				),
				$links
			);
		}

		return $links;
	}
}
