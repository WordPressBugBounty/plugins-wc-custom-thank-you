<?php
/**
 * Main plugin orchestrator.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_Custom_Thankyou', false ) ) {
	return;
}

/**
 * Main plugin class.
 */
final class WC_Custom_Thankyou {

	/**
	 * Class instance.
	 *
	 * @var WC_Custom_Thankyou|null
	 */
	protected static $instance = null;

	/**
	 * Configured Thank You page ID, captured when the plugin boots.
	 *
	 * Retained only as a read-only backwards-compatibility snapshot for any
	 * third-party code that reads `WC_Custom_Thankyou::instance()->page_id`.
	 * It is not used internally and may be stale if the setting changes during
	 * the request; internal code reads the option directly instead. Do not add
	 * new internal dependencies on this property.
	 *
	 * @var int
	 */
	public $page_id = 0;

	/**
	 * Admin component.
	 *
	 * @var WCCTY_Admin
	 */
	private $admin;

	/**
	 * Frontend component.
	 *
	 * @var WCCTY_Frontend
	 */
	private $frontend;

	/**
	 * Main WC_Custom_Thankyou instance.
	 *
	 * @return WC_Custom_Thankyou
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wc-custom-thank-you' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'wc-custom-thank-you' ), '1.0.0' );
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	private function __construct() {
		$this->page_id = absint( get_option( WCCTY_Frontend::OPTION_PAGE_ID ) );

		$this->admin    = new WCCTY_Admin();
		$this->frontend = new WCCTY_Frontend();

		$this->admin->init();
		$this->frontend->init();
	}
}
