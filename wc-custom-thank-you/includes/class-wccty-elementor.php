<?php
/**
 * Elementor integration.
 *
 * Registers the widget category, the "Order Confirmation" widget, and the
 * shared stylesheet so the widget looks identical to the Gutenberg block. All
 * hooks here are Elementor-specific, so nothing runs unless Elementor is active.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Elementor', false ) ) {
	return;
}

/**
 * Wires the Order Confirmation widget into Elementor.
 */
final class WCCTY_Elementor {

	/**
	 * Category slug for the plugin's Elementor widgets.
	 *
	 * @var string
	 */
	const CATEGORY = 'wccty';

	/**
	 * Handle for the shared front-end stylesheet (same CSS the block uses).
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'wccty-order-confirmation';

	/**
	 * Register Elementor hooks.
	 *
	 * These actions only fire when Elementor is loaded, so this is safe to call
	 * unconditionally from the plugin bootstrap.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/frontend/after_register_styles', array( __CLASS__, 'register_styles' ) );
	}

	/**
	 * Register the plugin's widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 * @return void
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			self::CATEGORY,
			array(
				'title' => __( 'Custom Thank You', 'wc-custom-thank-you' ),
				'icon'  => 'eicon-woocommerce',
			)
		);
	}

	/**
	 * Register the Order Confirmation widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public static function register_widgets( $widgets_manager ) {
		require_once WC_CUSTOM_THANKYOU_PATH . 'includes/widgets/class-wccty-elementor-order-confirmation-widget.php';

		if ( class_exists( 'WCCTY_Elementor_Order_Confirmation_Widget' ) ) {
			$widgets_manager->register( new WCCTY_Elementor_Order_Confirmation_Widget() );
		}
	}

	/**
	 * Register the shared stylesheet, versioned by file modification time so CSS
	 * changes are never served stale. Enqueued on demand via the widget's
	 * get_style_depends().
	 *
	 * @return void
	 */
	public static function register_styles() {
		$relative = 'build/style-index.css';
		$path     = WC_CUSTOM_THANKYOU_PATH . $relative;
		$version  = file_exists( $path ) ? (string) filemtime( $path ) : WC_CUSTOM_THANKYOU_VERSION;

		wp_register_style(
			self::STYLE_HANDLE,
			WC_CUSTOM_THANKYOU_URL . $relative,
			array(),
			$version
		);
	}
}
