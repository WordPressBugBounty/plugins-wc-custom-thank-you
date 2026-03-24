<?php
/**
 * Plugin Name: Custom Thank You Page for WooCommerce
 * Plugin URI:  https://wordpress.org/plugins/wc-custom-thank-you/
 * Description: A WooCommerce extension that allows you to define a custom Thank You (Order Confirmation) page.
 * Version:     2.1.0
 * Author:      Riaan Knoetze
 * Contributors: Nicola Mustone
 * Author URI:  https://profiles.wordpress.org/riaanknoetze/
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * Tested up to: 6.9
 *
 * WC requires at least: 8.0
 * WC tested up to: 10.6.1
 *
 * Text Domain: wc-custom-thank-you
 * Domain Path: /languages/
 *
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_CUSTOM_THANKYOU_VERSION', '2.1.0' );
define( 'WC_CUSTOM_THANKYOU_FILE', __FILE__ );
define( 'WC_CUSTOM_THANKYOU_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_CUSTOM_THANKYOU_URL', plugin_dir_url( __FILE__ ) );

require_once WC_CUSTOM_THANKYOU_PATH . 'includes/class-wccty-compatibility.php';
require_once WC_CUSTOM_THANKYOU_PATH . 'includes/class-wccty-i18n.php';
require_once WC_CUSTOM_THANKYOU_PATH . 'includes/class-wccty-admin.php';
require_once WC_CUSTOM_THANKYOU_PATH . 'includes/class-wccty-frontend.php';
require_once WC_CUSTOM_THANKYOU_PATH . 'includes/class-wc-custom-thankyou.php';
require_once WC_CUSTOM_THANKYOU_PATH . 'includes/class-wccty-bootstrap.php';

WCCTY_Bootstrap::init();
