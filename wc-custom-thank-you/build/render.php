<?php
/**
 * Render callback for the Custom Thank You block.
 *
 * Builds the block wrapper (with its supports classes, plus the template class
 * and any color custom properties) and hands off to the shared renderer, which
 * resolves the order and dispatches to the selected template partial in
 * includes/templates/. The same renderer powers the Elementor widget.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @package WC_Custom_Thank_You
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $attributes is provided by the block render context. */
$wccty_attributes = ( isset( $attributes ) && is_array( $attributes ) ) ? $attributes : array();

/* phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $content is the rendered inner blocks, provided by the block render context. */
$wccty_inner_content = isset( $content ) ? (string) $content : '';

if ( ! class_exists( 'WCCTY_Order_Confirmation_Renderer' ) ) {
	return;
}

// Merge the template class and color custom properties into the block wrapper so
// they sit alongside the block supports (color, spacing, border, typography).
$wccty_wrapper_args = array(
	'class' => WCCTY_Order_Confirmation_Renderer::get_template_class( $wccty_attributes ),
);

$wccty_color_style = WCCTY_Order_Confirmation_Renderer::build_color_style( $wccty_attributes );
if ( '' !== $wccty_color_style ) {
	$wccty_wrapper_args['style'] = $wccty_color_style;
}

$wccty_wrapper_attributes = get_block_wrapper_attributes( $wccty_wrapper_args );

echo WCCTY_Order_Confirmation_Renderer::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer returns fully-escaped, trusted markup.
	$wccty_attributes,
	$wccty_inner_content,
	$wccty_wrapper_attributes
);
