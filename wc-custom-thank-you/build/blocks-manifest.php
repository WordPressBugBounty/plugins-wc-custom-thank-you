<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'wccty/block-wc-custom-thank-you',
		'version' => '0.1.0',
		'title' => 'Order Confirmation',
		'category' => 'woocommerce',
		'icon' => 'feedback',
		'description' => 'Display complete WooCommerce order details including items, totals, and customer information.',
		'example' => array(
			
		),
		'attributes' => array(
			'accentColor' => array(
				'type' => 'string',
				'default' => '#4caf50'
			),
			'accentTextColor' => array(
				'type' => 'string',
				'default' => '#ffffff'
			),
			'sectionBackgroundColor' => array(
				'type' => 'string',
				'default' => '#f9f9f9'
			),
			'paymentBackgroundColor' => array(
				'type' => 'string',
				'default' => '#e3f2fd'
			),
			'paymentBorderColor' => array(
				'type' => 'string',
				'default' => '#2196f3'
			),
			'paymentTextColor' => array(
				'type' => 'string',
				'default' => '#333333'
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'color' => array(
				'background' => true,
				'text' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true
			),
			'spacing' => array(
				'padding' => true,
				'margin' => true
			),
			'border' => array(
				'color' => true,
				'radius' => true,
				'width' => true
			)
		),
		'textdomain' => 'wc-custom-thank-you',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);
