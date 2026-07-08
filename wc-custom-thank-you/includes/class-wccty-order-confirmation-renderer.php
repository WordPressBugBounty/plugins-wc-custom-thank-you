<?php
/**
 * Shared renderer for the Order Confirmation output.
 *
 * Single source of truth for turning a set of attributes (template, colors,
 * section visibility) plus a resolved order into the confirmation markup. Both
 * the Gutenberg block (build/render.php) and the Elementor widget route through
 * here so the two surfaces stay pixel-for-pixel identical and share the same
 * templates in includes/templates/.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Order_Confirmation_Renderer', false ) ) {
	return;
}

/**
 * Renders the Order Confirmation for the block and the Elementor widget.
 */
final class WCCTY_Order_Confirmation_Renderer {

	/**
	 * Base wrapper class the shared stylesheet is scoped to.
	 *
	 * The block editor generates this class automatically for the block (from the
	 * block name), and the SCSS in src/style.scss nests every rule under it. The
	 * Elementor widget has no such auto-generated class, so it must add this one
	 * to its wrapper for the shared CSS (build/style-index.css) to apply.
	 *
	 * @var string
	 */
	const STYLE_WRAPPER_CLASS = 'wp-block-wccty-block-wc-custom-thank-you';

	/**
	 * Templates that ship with the plugin. Whitelisted so the selected value is
	 * always safe to use inside a file path.
	 *
	 * @var string[]
	 */
	const ALLOWED_TEMPLATES = array( 'default', 'woocommerce', 'hero' );

	/**
	 * Section-visibility attributes and their defaults. Defaults keep every
	 * section visible so existing placements are unaffected.
	 *
	 * @var array<string,bool>
	 */
	const VISIBILITY_DEFAULTS = array(
		'showCustomerDetails' => true,
		'showBillingAddress'  => true,
		'showShippingAddress' => true,
		'showOrderNote'       => true,
		'showOrderItems'      => true,
		'showOrderTotals'     => true,
		'showPaymentMethod'   => true,
		'showDownloads'       => true,
		'showProductImages'   => true,
		'showSku'             => false,
	);

	/**
	 * Map of CSS custom property to the attribute that supplies its value.
	 *
	 * @var array<string,string>
	 */
	const COLOR_MAP = array(
		'--wccty-accent-color'         => 'accentColor',
		'--wccty-accent-text-color'    => 'accentTextColor',
		'--wccty-section-bg'           => 'sectionBackgroundColor',
		'--wccty-payment-bg'           => 'paymentBackgroundColor',
		'--wccty-payment-border-color' => 'paymentBorderColor',
		'--wccty-payment-text-color'   => 'paymentTextColor',
	);

	/**
	 * Whether a real order confirmation has been rendered on this request.
	 *
	 * Lets WCCTY_Frontend detect the confirmation output (block or widget) and
	 * skip the legacy WooCommerce thank-you template to avoid duplicate details.
	 *
	 * @var bool
	 */
	private static $has_rendered_order = false;

	/**
	 * Whether a real order confirmation has been rendered on this request.
	 *
	 * @return bool
	 */
	public static function has_rendered_order() {
		return self::$has_rendered_order;
	}

	/**
	 * Normalize the selected template to one of the whitelisted values.
	 *
	 * @param array $attributes Attributes bag.
	 * @return string
	 */
	public static function get_template( $attributes ) {
		$template = isset( $attributes['template'] ) ? sanitize_key( $attributes['template'] ) : 'default';

		if ( ! in_array( $template, self::ALLOWED_TEMPLATES, true ) ) {
			$template = 'default';
		}

		return $template;
	}

	/**
	 * Wrapper class for the selected template (e.g. "wccty-template-default").
	 *
	 * @param array $attributes Attributes bag.
	 * @return string
	 */
	public static function get_template_class( $attributes ) {
		return 'wccty-template-' . self::get_template( $attributes );
	}

	/**
	 * Sanitize a color value for use inside an inline CSS custom property.
	 *
	 * Accepts more than hex, because the editor's color picker stores theme-palette
	 * colors as CSS variables (e.g. Astra uses var(--ast-global-color-0)). Also
	 * allows rgb()/hsl() and named colors. Each value must match a single color
	 * token exactly, so it cannot inject extra declarations (no ; { } sneaks in).
	 *
	 * @param string $value Raw color value.
	 * @return string Sanitized value, or empty string when it is not a valid color.
	 */
	public static function sanitize_color( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		$hex = sanitize_hex_color( $value );
		if ( $hex ) {
			return $hex;
		}

		// Theme-palette CSS variable: var(--name) or var(--name, fallback).
		if ( preg_match( '/^var\(\s*--[A-Za-z0-9_-]+\s*(?:,\s*[^;{}()]+)?\)$/', $value ) ) {
			return $value;
		}

		// rgb()/rgba()/hsl()/hsla() with only numeric / percentage components.
		if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\(\s*[0-9.,%\/\s]+\)$/i', $value ) ) {
			return $value;
		}

		// Named color keyword (e.g. "rebeccapurple").
		if ( preg_match( '/^[A-Za-z]+$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Build the inline style string of CSS custom properties for the wrapper.
	 *
	 * Only attributes the merchant has actually set are emitted, so the output
	 * never hardcodes default colors — the SCSS fallbacks (and the theme's
	 * styling) apply until overridden.
	 *
	 * @param array $attributes Attributes bag.
	 * @return string Style declarations (with trailing ";") or empty string.
	 */
	public static function build_color_style( $attributes ) {
		$style_parts = array();

		foreach ( self::COLOR_MAP as $css_var => $attribute_key ) {
			if ( empty( $attributes[ $attribute_key ] ) ) {
				continue;
			}

			$color_value = self::sanitize_color( $attributes[ $attribute_key ] );

			if ( '' !== $color_value ) {
				$style_parts[] = $css_var . ':' . $color_value;
			}
		}

		if ( empty( $style_parts ) ) {
			return '';
		}

		return implode( ';', $style_parts ) . ';';
	}

	/**
	 * Resolve the section-visibility flags, applying defaults for anything unset.
	 *
	 * @param array $attributes Attributes bag.
	 * @return array<string,bool>
	 */
	public static function get_visibility( $attributes ) {
		$show = array();

		foreach ( self::VISIBILITY_DEFAULTS as $key => $default ) {
			$show[ $key ] = isset( $attributes[ $key ] ) ? (bool) $attributes[ $key ] : $default;
		}

		return $show;
	}

	/**
	 * Render the Order Confirmation markup.
	 *
	 * @param array         $attributes         Attributes bag (template, colors, visibility, heroTitle).
	 * @param string        $inner_content      Pre-rendered custom content (block inner blocks); may be empty.
	 * @param string        $wrapper_attributes HTML attribute string for the outer element (class/style/id/etc.).
	 * @param WC_Order|null $order              Explicit order to render; when null, it is resolved from the request.
	 * @param bool          $is_preview         When true (e.g. Elementor editor) a missing order shows a hint, not an error.
	 * @return string
	 */
	public static function render( $attributes, $inner_content = '', $wrapper_attributes = '', $order = null, $is_preview = false ) {
		$attributes    = is_array( $attributes ) ? $attributes : array();
		$inner_content = (string) $inner_content;

		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return self::render_message(
				$wrapper_attributes,
				__( 'WooCommerce is not installed or activated. This block requires WooCommerce to function.', 'wc-custom-thank-you' )
			);
		}

		/*
		 * Resolve and validate the order for this request.
		 *
		 * Shared with the frontend (legacy template / shortcode) via
		 * WCCTY_Order_Resolver so order resolution and the security check stay
		 * consistent: a valid order requires a matching order key. An explicit
		 * order (used for editor previews) bypasses the request lookup.
		 */
		if ( ! $order instanceof WC_Order ) {
			$order = class_exists( 'WCCTY_Order_Resolver' ) ? WCCTY_Order_Resolver::get_order_from_request() : false;
		}

		// Check if order exists.
		if ( ! $order instanceof WC_Order ) {
			if ( $is_preview ) {
				return self::render_message(
					$wrapper_attributes,
					__( 'Order Confirmation — this area displays the customer\'s order details (items, totals, and addresses) on your Thank You page. There is no order to preview here yet.', 'wc-custom-thank-you' )
				);
			}

			return self::render_message(
				$wrapper_attributes,
				__( 'No order found. Please complete a purchase to view order details.', 'wc-custom-thank-you' )
			);
		}

		$html = self::render_order( $attributes, $inner_content, $wrapper_attributes, $order );

		// Signal real (non-preview) confirmation output so the frontend can avoid
		// duplicating the legacy WooCommerce thank-you template.
		if ( ! $is_preview ) {
			self::$has_rendered_order = true;
		}

		return $html;
	}

	/**
	 * Render a wrapped, single-message notice (error / preview hint).
	 *
	 * @param string $wrapper_attributes HTML attribute string for the outer element.
	 * @param string $message            Message text.
	 * @return string
	 */
	private static function render_message( $wrapper_attributes, $message ) {
		ob_start();
		?>
		<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
			<div class="order-confirmation-error">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Assemble the display data and render the selected template partial.
	 *
	 * @param array    $attributes         Attributes bag.
	 * @param string   $inner_content      Pre-rendered custom content; may be empty.
	 * @param string   $wrapper_attributes HTML attribute string for the outer element.
	 * @param WC_Order $order              Resolved order.
	 * @return string
	 */
	private static function render_order( $attributes, $inner_content, $wrapper_attributes, $order ) {
		// Template partials read these $wccty_* variables from the local scope.
		$wccty_attributes    = $attributes;
		$wccty_inner_content = $inner_content;
		$wccty_template      = self::get_template( $attributes );
		$wccty_order         = $order;

		/*
		 * Assemble the display data. Gathering lives in WCCTY_Order_Presenter (and is
		 * filterable via `wccty_order_confirmation_data`), so the templates stay focused
		 * on presentation.
		 */
		$wccty_data             = WCCTY_Order_Presenter::get_data( $wccty_order );
		$wccty_order_data       = $wccty_data['order'];
		$wccty_billing_address  = $wccty_data['billing'];
		$wccty_shipping_address = $wccty_data['shipping'];
		$wccty_items            = $wccty_data['items'];
		$wccty_order_totals     = $wccty_data['totals'];
		$wccty_downloads        = isset( $wccty_data['downloads'] ) && is_array( $wccty_data['downloads'] ) ? $wccty_data['downloads'] : array();

		// Section visibility. block.json / the widget controls supply the same defaults.
		$wccty_show           = self::get_visibility( $attributes );
		$wccty_show_addresses = $wccty_show['showBillingAddress'] || $wccty_show['showShippingAddress'];
		$wccty_show_details   = $wccty_show['showCustomerDetails'] || $wccty_show_addresses;

		// Presentation-ready order items, shared by every template.
		$wccty_items_display = WCCTY_Order_Presenter::get_items_display(
			$wccty_order,
			$wccty_show['showProductImages'],
			$wccty_show['showSku']
		);

		// Formatted order date, shared by every template.
		$wccty_date_format = get_option( 'date_format' );
		if ( ! is_string( $wccty_date_format ) || '' === $wccty_date_format ) {
			$wccty_date_format = 'F j, Y';
		}

		$wccty_order_date = '';
		if ( $wccty_order_data['date_created'] instanceof WC_DateTime ) {
			$wccty_order_date = $wccty_order_data['date_created']->date_i18n( $wccty_date_format );
		}
		if ( '' === $wccty_order_date ) {
			$wccty_order_date = __( 'N/A', 'wc-custom-thank-you' );
		}

		// Locate the selected template partial (whitelisted above; fall back to default).
		$wccty_template_file = defined( 'WC_CUSTOM_THANKYOU_PATH' )
			? WC_CUSTOM_THANKYOU_PATH . 'includes/templates/' . $wccty_template . '.php'
			: '';

		if ( ! $wccty_template_file || ! is_readable( $wccty_template_file ) ) {
			$wccty_template_file = defined( 'WC_CUSTOM_THANKYOU_PATH' )
				? WC_CUSTOM_THANKYOU_PATH . 'includes/templates/default.php'
				: '';
		}

		ob_start();
		?>
		<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
			<?php if ( '' !== trim( $wccty_inner_content ) ) : ?>
				<?php
				/*
				 * Merchant-authored nested blocks (headings, buttons, upsells, patterns, etc.).
				 * Already rendered and sanitized by the block editor / core, so it is output as-is.
				 */
				?>
				<div class="wccty-custom-content">
					<?php echo $wccty_inner_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner block content is rendered and escaped by WordPress core. ?>
				</div>
			<?php endif; ?>

			<?php
			if ( $wccty_template_file && is_readable( $wccty_template_file ) ) {
				require $wccty_template_file;
			}
			?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
