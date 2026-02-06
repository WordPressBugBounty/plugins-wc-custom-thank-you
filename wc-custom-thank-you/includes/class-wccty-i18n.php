<?php
/**
 * Internationalization functionality.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_I18n', false ) ) {
	return;
}

/**
 * Plugin i18n bootstrapper.
 *
 * Plugins hosted on WordPress.org don't need to manually load their translation
 * files. WordPress will load translations for the plugin text domain as needed,
 * as long
 * as the plugin header includes the correct `Text Domain` and `Domain Path`.
 */
final class WCCTY_I18n {

	/**
	 * Initialize i18n functionality.
	 *
	 * Intentionally left blank. Translations are loaded automatically by
	 * WordPress based on the plugin headers.
	 *
	 * @return void
	 */
	public function init() {}
}
