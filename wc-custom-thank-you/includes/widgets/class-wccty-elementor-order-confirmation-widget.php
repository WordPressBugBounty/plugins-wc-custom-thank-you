<?php
/**
 * "Order Confirmation" Elementor widget.
 *
 * Mirrors the Gutenberg block's options (template, section visibility, colors,
 * product images/SKU) and renders through the shared
 * WCCTY_Order_Confirmation_Renderer, so both surfaces produce identical output.
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * Renders the WooCommerce order confirmation on an Elementor page.
 */
class WCCTY_Elementor_Order_Confirmation_Widget extends \Elementor\Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wccty-order-confirmation';
	}

	/**
	 * Widget title shown in the editor.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Order Confirmation', 'wc-custom-thank-you' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-woocommerce';
	}

	/**
	 * Editor categories the widget belongs to.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( WCCTY_Elementor::CATEGORY );
	}

	/**
	 * Search keywords for the editor panel.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'woocommerce', 'order', 'confirmation', 'thank you', 'thankyou', 'receipt', 'checkout' );
	}

	/**
	 * Front-end style dependencies (shared with the block).
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( WCCTY_Elementor::STYLE_HANDLE );
	}

	/**
	 * Register the widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->register_layout_controls();
		$this->register_section_controls();
		$this->register_item_controls();
		$this->register_color_controls();
	}

	/**
	 * Layout controls: template selector and (Hero-only) heading.
	 *
	 * @return void
	 */
	private function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'wc-custom-thank-you' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'template',
			array(
				'label'   => __( 'Layout', 'wc-custom-thank-you' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default'     => __( 'Default', 'wc-custom-thank-you' ),
					'woocommerce' => __( 'WooCommerce Core', 'wc-custom-thank-you' ),
					'hero'        => __( 'Hero + summary cards', 'wc-custom-thank-you' ),
				),
			)
		);

		$this->add_control(
			'hero_title',
			array(
				'label'       => __( 'Hero heading', 'wc-custom-thank-you' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Thank you for your order!', 'wc-custom-thank-you' ),
				'label_block' => true,
				'condition'   => array(
					'template' => 'hero',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Section-visibility toggles.
	 *
	 * @return void
	 */
	private function register_section_controls() {
		$this->start_controls_section(
			'section_sections',
			array(
				'label' => __( 'Sections', 'wc-custom-thank-you' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$toggles = array(
			'show_customer_details' => __( 'Customer details', 'wc-custom-thank-you' ),
			'show_billing_address'  => __( 'Billing address', 'wc-custom-thank-you' ),
			'show_shipping_address' => __( 'Shipping address', 'wc-custom-thank-you' ),
			'show_order_note'       => __( 'Order note', 'wc-custom-thank-you' ),
			'show_order_items'      => __( 'Order items', 'wc-custom-thank-you' ),
			'show_order_totals'     => __( 'Order totals', 'wc-custom-thank-you' ),
			'show_payment_method'   => __( 'Payment method', 'wc-custom-thank-you' ),
			'show_downloads'        => __( 'Downloads (downloadable orders)', 'wc-custom-thank-you' ),
		);

		foreach ( $toggles as $control_id => $label ) {
			$this->add_control(
				$control_id,
				array(
					'label'        => $label,
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => __( 'Show', 'wc-custom-thank-you' ),
					'label_off'    => __( 'Hide', 'wc-custom-thank-you' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Order-item controls: product images and SKU.
	 *
	 * @return void
	 */
	private function register_item_controls() {
		$this->start_controls_section(
			'section_items',
			array(
				'label' => __( 'Order items', 'wc-custom-thank-you' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_product_images',
			array(
				'label'        => __( 'Show product images', 'wc-custom-thank-you' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'wc-custom-thank-you' ),
				'label_off'    => __( 'Hide', 'wc-custom-thank-you' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_sku',
			array(
				'label'        => __( 'Show product SKU', 'wc-custom-thank-you' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'wc-custom-thank-you' ),
				'label_off'    => __( 'Hide', 'wc-custom-thank-you' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Color controls (Style tab). These mirror the block: the payment box colors
	 * only apply to the Default layout, and the WooCommerce Core layout inherits
	 * the theme's styling, so a notice replaces the pickers there.
	 *
	 * Each picker maps to a CSS custom property on the widget wrapper via
	 * `selectors`, and is flagged `render_type => 'ui'`, so Elementor updates the
	 * injected CSS live instead of re-rendering the (server-side) widget on every
	 * change — no flicker. The custom property inherits down to the confirmation
	 * markup, exactly like the block's inline style. See COLOR_MAP on the renderer.
	 *
	 * @return void
	 */
	private function register_color_controls() {
		$this->start_controls_section(
			'section_colors',
			array(
				'label' => __( 'Colors', 'wc-custom-thank-you' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'woocommerce_colors_notice',
			array(
				'type'      => \Elementor\Controls_Manager::RAW_HTML,
				'raw'       => __( 'The WooCommerce Core layout uses your theme\'s WooCommerce styling, so these color options do not apply.', 'wc-custom-thank-you' ),
				'classes'   => 'elementor-panel-alert elementor-panel-alert-info',
				'condition' => array(
					'template' => 'woocommerce',
				),
			)
		);

		// control id => [ label, css custom property, visibility condition ].
		$colors = array(
			'accent_color'             => array(
				__( 'Status badge / hero background', 'wc-custom-thank-you' ),
				'--wccty-accent-color',
				array( 'template!' => 'woocommerce' ),
			),
			'accent_text_color'        => array(
				__( 'Status badge / hero text', 'wc-custom-thank-you' ),
				'--wccty-accent-text-color',
				array( 'template!' => 'woocommerce' ),
			),
			'section_background_color' => array(
				__( 'Section background (cards, totals)', 'wc-custom-thank-you' ),
				'--wccty-section-bg',
				array( 'template!' => 'woocommerce' ),
			),
			'payment_background_color' => array(
				__( 'Payment box background', 'wc-custom-thank-you' ),
				'--wccty-payment-bg',
				array( 'template' => 'default' ),
			),
			'payment_text_color'       => array(
				__( 'Payment box text', 'wc-custom-thank-you' ),
				'--wccty-payment-text-color',
				array( 'template' => 'default' ),
			),
			'payment_border_color'     => array(
				__( 'Payment box border', 'wc-custom-thank-you' ),
				'--wccty-payment-border-color',
				array( 'template' => 'default' ),
			),
		);

		foreach ( $colors as $control_id => $config ) {
			list( $label, $css_var, $condition ) = $config;

			$this->add_control(
				$control_id,
				array(
					'label'       => $label,
					'type'        => \Elementor\Controls_Manager::COLOR,
					'render_type' => 'ui',
					'selectors'   => array(
						'{{WRAPPER}}' => $css_var . ': {{VALUE}};',
					),
					'condition'   => $condition,
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Map the widget settings onto the attribute bag the shared renderer expects.
	 *
	 * @param array $settings Widget settings from get_settings_for_display().
	 * @return array
	 */
	private function build_attributes( $settings ) {
		$is_on = static function ( $value ) {
			return 'yes' === $value;
		};

		return array(
			'template'               => isset( $settings['template'] ) ? $settings['template'] : 'default',
			'heroTitle'              => isset( $settings['hero_title'] ) ? $settings['hero_title'] : '',

			'accentColor'            => isset( $settings['accent_color'] ) ? $settings['accent_color'] : '',
			'accentTextColor'        => isset( $settings['accent_text_color'] ) ? $settings['accent_text_color'] : '',
			'sectionBackgroundColor' => isset( $settings['section_background_color'] ) ? $settings['section_background_color'] : '',
			'paymentBackgroundColor' => isset( $settings['payment_background_color'] ) ? $settings['payment_background_color'] : '',
			'paymentTextColor'       => isset( $settings['payment_text_color'] ) ? $settings['payment_text_color'] : '',
			'paymentBorderColor'     => isset( $settings['payment_border_color'] ) ? $settings['payment_border_color'] : '',

			'showCustomerDetails'    => $is_on( isset( $settings['show_customer_details'] ) ? $settings['show_customer_details'] : 'yes' ),
			'showBillingAddress'     => $is_on( isset( $settings['show_billing_address'] ) ? $settings['show_billing_address'] : 'yes' ),
			'showShippingAddress'    => $is_on( isset( $settings['show_shipping_address'] ) ? $settings['show_shipping_address'] : 'yes' ),
			'showOrderNote'          => $is_on( isset( $settings['show_order_note'] ) ? $settings['show_order_note'] : 'yes' ),
			'showOrderItems'         => $is_on( isset( $settings['show_order_items'] ) ? $settings['show_order_items'] : 'yes' ),
			'showOrderTotals'        => $is_on( isset( $settings['show_order_totals'] ) ? $settings['show_order_totals'] : 'yes' ),
			'showPaymentMethod'      => $is_on( isset( $settings['show_payment_method'] ) ? $settings['show_payment_method'] : 'yes' ),
			'showDownloads'          => $is_on( isset( $settings['show_downloads'] ) ? $settings['show_downloads'] : 'yes' ),
			'showProductImages'      => $is_on( isset( $settings['show_product_images'] ) ? $settings['show_product_images'] : 'yes' ),
			'showSku'                => $is_on( isset( $settings['show_sku'] ) ? $settings['show_sku'] : '' ),
		);
	}

	/**
	 * Whether the widget is being rendered inside the Elementor editor/preview,
	 * where there is no real order in the request.
	 *
	 * @return bool
	 */
	private function is_elementor_preview() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$elementor = \Elementor\Plugin::instance();

		if ( isset( $elementor->editor ) && $elementor->editor->is_edit_mode() ) {
			return true;
		}

		if ( isset( $elementor->preview ) && $elementor->preview->is_preview_mode() ) {
			return true;
		}

		return false;
	}

	/**
	 * Grab the most recent order so the editor can show a realistic preview.
	 *
	 * Only ever used inside the Elementor editor/preview (never on the front end),
	 * where the user already has permission to manage the site's content.
	 *
	 * @return WC_Order|null
	 */
	private function get_preview_order() {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return null;
		}

		$orders = wc_get_orders(
			array(
				'limit'   => 1,
				'orderby' => 'date',
				'order'   => 'DESC',
				'type'    => 'shop_order',
			)
		);

		if ( ! empty( $orders ) && $orders[0] instanceof WC_Order ) {
			return $orders[0];
		}

		return null;
	}

	/**
	 * Render the widget on the front end (and in the editor preview).
	 *
	 * @return void
	 */
	protected function render() {
		if ( ! class_exists( 'WCCTY_Order_Confirmation_Renderer' ) ) {
			return;
		}

		$settings   = $this->get_settings_for_display();
		$attributes = $this->build_attributes( $settings );

		$is_preview = $this->is_elementor_preview();
		$order      = $is_preview ? $this->get_preview_order() : null;

		// Build the wrapper the same way the block does. The base STYLE_WRAPPER_CLASS
		// is what the shared stylesheet is scoped to (the block gets it automatically);
		// the template class selects the layout; the hook class scopes any future
		// widget-only CSS.
		//
		// Colors are intentionally NOT emitted as an inline style here: they are set
		// as CSS custom properties on the widget wrapper by Elementor (via each color
		// control's `selectors`), which keeps the editor live-preview flicker-free and
		// lets those properties inherit into this markup. An inline custom property
		// here would shadow Elementor's value and break the live preview.
		$classes = WCCTY_Order_Confirmation_Renderer::STYLE_WRAPPER_CLASS
			. ' ' . WCCTY_Order_Confirmation_Renderer::get_template_class( $attributes )
			. ' wccty-elementor-order-confirmation';

		$wrapper = 'class="' . esc_attr( $classes ) . '"';

		echo WCCTY_Order_Confirmation_Renderer::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer returns fully-escaped, trusted markup.
			$attributes,
			'',
			$wrapper,
			$order,
			$is_preview
		);
	}
}
