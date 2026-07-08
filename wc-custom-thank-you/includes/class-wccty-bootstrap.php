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

		// Bootstrap the plugin once WooCommerce is initialized.
		add_action( 'woocommerce_loaded', array( __CLASS__, 'boot_plugin' ) );

		// Blocks.
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );

		// Elementor widget (hooks are no-ops unless Elementor is active).
		WCCTY_Elementor::init();

		// Plugin action links.
		add_filter(
			'plugin_action_links_' . plugin_basename( WC_CUSTOM_THANKYOU_FILE ),
			array( __CLASS__, 'plugin_action_links' ),
			PHP_INT_MAX
		);
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

		self::version_block_styles_by_mtime();
	}

	/**
	 * Version the block's stylesheets by file modification time.
	 *
	 * block.json registers block styles with its static `version`, so CSS changes
	 * are not picked up until that value changes (leaving editors and browsers on
	 * stale cached CSS). Using filemtime busts the cache whenever the CSS actually
	 * changes. The editor script already uses a content hash, so it is left alone.
	 *
	 * @return void
	 */
	private static function version_block_styles_by_mtime() {
		$styles = array(
			'wccty-block-wc-custom-thank-you-style'        => 'build/style-index.css',
			'wccty-block-wc-custom-thank-you-editor-style' => 'build/index.css',
		);

		$wp_styles = wp_styles();

		foreach ( $styles as $handle => $relative_path ) {
			$path = WC_CUSTOM_THANKYOU_PATH . $relative_path;

			if ( isset( $wp_styles->registered[ $handle ] ) && file_exists( $path ) ) {
				$wp_styles->registered[ $handle ]->ver = (string) filemtime( $path );
			}
		}
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

			// Deep-link to the Order Confirmation section heading on the Advanced tab.
			if ( class_exists( 'WCCTY_Admin' ) ) {
				$settings_url .= '#' . WCCTY_Admin::SETTINGS_ANCHOR;
			}

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
