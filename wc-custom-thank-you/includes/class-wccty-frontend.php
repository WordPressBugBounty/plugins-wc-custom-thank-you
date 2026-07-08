<?php
/**
 * Frontend functionality (redirect + content rendering).
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCCTY_Frontend', false ) ) {
	return;
}

/**
 * Frontend hooks for the custom Thank You page.
 */
final class WCCTY_Frontend {

	/**
	 * Option name storing the page ID.
	 *
	 * @var string
	 */
	const OPTION_PAGE_ID = 'woocommerce_custom_thankyou_page_id';

	/**
	 * Whether the order confirmation block has been rendered on this request.
	 *
	 * @var bool
	 */
	private $order_confirmation_block_rendered = false;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'template_redirect', array( $this, 'maybe_redirect_after_purchase' ) );

		// Append thankyou markup to the custom page content.
		add_filter( 'the_content', array( $this, 'append_thankyou_to_page_content' ), 999 );

		// Detect whether the order confirmation block is present so we can avoid duplicating output.
		add_filter( 'render_block', array( $this, 'maybe_flag_order_confirmation_block' ), 10, 2 );

		// Make WooCommerce treat the custom page as "order received" and "checkout".
		add_filter( 'woocommerce_is_order_received_page', array( $this, 'is_order_received_page' ) );
		add_filter( 'woocommerce_is_checkout', array( $this, 'is_checkout' ) );

		// Shortcode for FSE / explicit placement.
		add_shortcode( 'wc_custom_thankyou', array( $this, 'shortcode_thankyou' ) );
	}

	/**
	 * Get configured Thank You page ID.
	 *
	 * @return int
	 */
	private function get_page_id() {
		return absint( get_option( self::OPTION_PAGE_ID ) );
	}

	/**
	 * Check whether current request is for the configured Thank You page.
	 *
	 * @return bool
	 */
	private function is_custom_thankyou_page() {
		$page_id = $this->get_page_id();

		return ( $page_id > 0 ) && is_page( $page_id );
	}

	/**
	 * Make WooCommerce treat the custom Thank You page as a checkout page.
	 *
	 * This allows WooCommerce to enqueue its usual checkout styles and scripts.
	 *
	 * @param bool $is_checkout Original value.
	 * @return bool
	 */
	public function is_checkout( $is_checkout ) {
		if ( $this->is_custom_thankyou_page() ) {
			return true;
		}

		return $is_checkout;
	}

	/**
	 * Checks if the page shown is the Custom Thank You page and returns true in that case.
	 *
	 * @param  bool $is_order_received_page Original value from the filter.
	 * @return bool
	 */
	public function is_order_received_page( $is_order_received_page ) {
		if ( $this->is_custom_thankyou_page() ) {
			return true;
		}

		return $is_order_received_page;
	}

	/**
	 * Redirect customer from the core order received endpoint to the custom page.
	 *
	 * @return void
	 */
	public function maybe_redirect_after_purchase() {
		// Only act on the core "order-received" endpoint.
		if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}

		$page_id = $this->get_page_id();
		if ( ! $page_id ) {
			return;
		}

		$order_id = absint( get_query_var( 'order-received' ) );
		if ( ! $order_id ) {
			return;
		}

		$order_key = WCCTY_Order_Resolver::get_order_key_from_request();
		if ( '' === $order_key ) {
			return;
		}

		$redirect = get_permalink( $page_id );
		if ( ! $redirect ) {
			return;
		}

		$redirect = add_query_arg(
			array(
				'order' => $order_id,
				'key'   => $order_key,
			),
			$redirect
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Append the WooCommerce thank you markup to the content of the custom page.
	 *
	 * @param string $content Page content.
	 * @return string
	 */
	public function append_thankyou_to_page_content( $content ) {
		static $handled = false;

		// Only run once per request, on the configured Thank You page.
		if ( $handled || ! $this->is_custom_thankyou_page() ) {
			return $content;
		}

		$handled = true;

		$order_id  = WCCTY_Order_Resolver::get_order_id_from_request();
		$order_key = WCCTY_Order_Resolver::get_order_key_from_request();

		if ( ! $order_id || '' === $order_key ) {
			return $content;
		}

		// If the page already contains the order confirmation block, do not append the legacy template output.
		if ( $this->request_uses_order_confirmation_block() ) {
			$order = WCCTY_Order_Resolver::get_order_from_request();

			if ( $order ) {
				$actions_output = $this->maybe_run_thankyou_actions_without_template( $order );

				if ( '' !== $actions_output ) {
					return $content . $actions_output;
				}
			}

			return $content;
		}

		$markup = $this->generate_thankyou_markup();

		if ( '' === $markup ) {
			// If something went wrong, at least return the original content.
			return $content;
		}

		return $content . $markup;
	}

	/**
	 * Flag when the order confirmation block is rendered.
	 *
	 * This helps us avoid appending the legacy WooCommerce thankyou template when the page
	 * already renders order details via the block (including when the block is inside a
	 * reusable block or pattern).
	 *
	 * @param string $block_content Rendered block content.
	 * @param array  $block         Full block, including name and attrs.
	 * @return string
	 */
	public function maybe_flag_order_confirmation_block( $block_content, $block ) {
		if ( empty( $block['blockName'] ) || 'wccty/block-wc-custom-thank-you' !== $block['blockName'] ) {
			return $block_content;
		}

		if ( ! $this->is_custom_thankyou_page() ) {
			return $block_content;
		}

		$this->order_confirmation_block_rendered = true;

		return $block_content;
	}

	/**
	 * Check if this request is rendering the order confirmation block on the custom Thank You page.
	 *
	 * @return bool
	 */
	private function request_uses_order_confirmation_block() {
		if ( $this->order_confirmation_block_rendered ) {
			return true;
		}

		// The Order Confirmation Elementor widget (and the block) flag the shared
		// renderer when they output real order details, so we detect either one.
		if ( class_exists( 'WCCTY_Order_Confirmation_Renderer' ) && WCCTY_Order_Confirmation_Renderer::has_rendered_order() ) {
			return true;
		}

		if ( ! function_exists( 'has_block' ) ) {
			return false;
		}

		$page_id = $this->get_page_id();

		global $post;

		$post_to_check = $post;

		if ( ! ( $post_to_check instanceof WP_Post ) || (int) $post_to_check->ID !== $page_id ) {
			$post_to_check = get_post( $page_id );
		}

		if ( $post_to_check instanceof WP_Post && has_block( 'wccty/block-wc-custom-thank-you', $post_to_check ) ) {
			return true;
		}

		// Block themes: the block may live in the site template / template parts
		// rather than the page content. Such a block can render *after* the_content,
		// so checking the page alone would miss it and we'd duplicate the output.
		return $this->current_template_has_order_confirmation_block();
	}

	/**
	 * Whether the current block-theme template (or a template part it references)
	 * contains the order confirmation block.
	 *
	 * @return bool
	 */
	private function current_template_has_order_confirmation_block() {
		if ( empty( $GLOBALS['_wp_current_template_content'] ) || ! is_string( $GLOBALS['_wp_current_template_content'] ) ) {
			return false;
		}

		return $this->content_has_order_confirmation_block( $GLOBALS['_wp_current_template_content'] );
	}

	/**
	 * Recursively check block markup (and any referenced template parts) for the block.
	 *
	 * @param string $content Block markup to inspect.
	 * @return bool
	 */
	private function content_has_order_confirmation_block( $content ) {
		if ( ! is_string( $content ) || '' === $content ) {
			return false;
		}

		if ( has_block( 'wccty/block-wc-custom-thank-you', $content ) ) {
			return true;
		}

		// Only descend into template parts when there are any to descend into.
		if ( ! function_exists( 'parse_blocks' ) || false === strpos( $content, 'wp:template-part' ) ) {
			return false;
		}

		foreach ( parse_blocks( $content ) as $block ) {
			if ( $this->block_tree_references_order_confirmation_block( $block ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Walk a parsed block tree, resolving template parts and checking their content.
	 *
	 * @param array $block Parsed block.
	 * @return bool
	 */
	private function block_tree_references_order_confirmation_block( $block ) {
		if (
			isset( $block['blockName'] ) && 'core/template-part' === $block['blockName']
			&& ! empty( $block['attrs']['slug'] ) && function_exists( 'get_block_template' )
		) {
			$theme = ! empty( $block['attrs']['theme'] ) ? $block['attrs']['theme'] : get_stylesheet();
			$part  = get_block_template( $theme . '//' . $block['attrs']['slug'], 'wp_template_part' );

			if ( $part instanceof WP_Block_Template && has_block( 'wccty/block-wc-custom-thank-you', (string) $part->content ) ) {
				return true;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $inner_block ) {
				if ( $this->block_tree_references_order_confirmation_block( $inner_block ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Run core "thankyou" side-effects and actions without rendering the legacy template markup.
	 *
	 * When the order confirmation block is used, we don't want to duplicate the order overview
	 * from `checkout/thankyou.php`, but we still need to:
	 * - Clear cart + awaiting payment session (matching core behavior)
	 * - Fire thankyou hooks so payment gateways / extensions still work
	 *
	 * Any output produced by hooked callbacks will be appended to the page content.
	 *
	 * @param WC_Order $order Order instance.
	 * @return string
	 */
	private function maybe_run_thankyou_actions_without_template( $order ) {
		static $ran = false;

		if ( $ran ) {
			return '';
		}

		$ran = true;

		// Empty awaiting payment session in a way that matches core behavior.
		if ( WC()->session ) {
			WC()->session->set( 'order_awaiting_payment', false );
		}

		// Empty current cart.
		if ( WC()->cart ) {
			wc_empty_cart();
		}

		ob_start();

		$order_id       = $order->get_id();
		$payment_method = $order->get_payment_method();

		/*
		 * Keep gateway-specific hooks (e.g. BACS instructions), but avoid duplicating
		 * WooCommerce core "order details" + "customer details" markup when the page uses
		 * the order confirmation block.
		 */
		if ( $payment_method ) {
			do_action( 'woocommerce_thankyou_' . $payment_method, $order_id );
		}

		// Temporarily remove core output callbacks from the generic thankyou hook.
		$removed_actions = array();
		$core_callbacks  = array(
			'woocommerce_order_details_table',
			'woocommerce_customer_details',
			'woocommerce_order_again_button',
		);

		foreach ( $core_callbacks as $callback ) {
			$priority = has_action( 'woocommerce_thankyou', $callback );

			if ( false !== $priority ) {
				remove_action( 'woocommerce_thankyou', $callback, $priority );
				$removed_actions[ $callback ] = $priority;
			}
		}

		do_action( 'woocommerce_thankyou', $order_id );

		// Restore core callbacks so we don't affect other requests/contexts.
		foreach ( $removed_actions as $callback => $priority ) {
			add_action( 'woocommerce_thankyou', $callback, $priority );
		}

		$output = (string) ob_get_clean();

		return wp_kses_post( $output );
	}

	/**
	 * Shortcode handler: [wc_custom_thankyou]
	 *
	 * @return string
	 */
	public function shortcode_thankyou() {
		// If the page already contains the order confirmation block, do not output the legacy template.
		if ( $this->is_custom_thankyou_page() && $this->request_uses_order_confirmation_block() ) {
			$order = WCCTY_Order_Resolver::get_order_from_request();

			if ( $order ) {
				return $this->maybe_run_thankyou_actions_without_template( $order );
			}

			return '';
		}

		return $this->generate_thankyou_markup();
	}

	/**
	 * Generate the WooCommerce thank you content for the current request.
	 *
	 * @return string
	 */
	private function generate_thankyou_markup() {
		$order_id  = WCCTY_Order_Resolver::get_order_id_from_request();
		$order_key = WCCTY_Order_Resolver::get_order_key_from_request();

		// Stay silent unless the request carries both an order ID and key.
		if ( ! $order_id || '' === $order_key ) {
			return '';
		}

		wc_print_notices();

		$order = WCCTY_Order_Resolver::get_order_from_request();

		if ( ! $order ) {
			$wccty_order_received_text = apply_filters(
				'woocommerce_thankyou_order_received_text',
				__( 'Thank you. Your order has been received.', 'wc-custom-thank-you' ),
				null
			);

			return '<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">' .
				wp_kses_post( $wccty_order_received_text ) .
				'</p>';
		}

		// Empty awaiting payment session in a way that matches core behavior.
		if ( WC()->session ) {
			WC()->session->set( 'order_awaiting_payment', false );
		}

		// Empty current cart.
		if ( WC()->cart ) {
			wc_empty_cart();
		}

		ob_start();

		wc_get_template(
			'checkout/thankyou.php',
			array(
				'order' => $order,
			)
		);

		return (string) ob_get_clean();
	}
}