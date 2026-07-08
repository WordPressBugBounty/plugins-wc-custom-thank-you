
/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

import {
	InspectorControls,
	PanelColorSettings,
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import {
	Notice,
	PanelBody,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';

import './editor.scss';

/**
 * Starter content for the nested (custom) area. Merchants can add, remove, or
 * reorder anything here — headings, buttons, upsell blocks, patterns, etc.
 */
const TEMPLATE = [
	[
		'core/heading',
		{
			level: 2,
			content: __( 'Thank you for your order!', 'wc-custom-thank-you' ),
		},
	],
	[
		'core/paragraph',
		{
			content: __(
				'We appreciate your business. Your order details are below.',
				'wc-custom-thank-you'
			),
		},
	],
];

// Dummy order data used to preview every template in the editor.
const D = {
	orderNumber: '12345',
	date: 'January 1, 2024',
	status: __( 'Processing', 'wc-custom-thank-you' ),
	name: __( 'John Doe', 'wc-custom-thank-you' ),
	email: 'john@example.com',
	phone: '(555) 123-4567',
	product: __( 'Sample Product', 'wc-custom-thank-you' ),
	sku: 'SAMPLE-123',
	qty: 2,
	price: '$50.00',
	subtotal: '$50.00',
	shipping: '$10.00',
	tax: '$5.00',
	total: '$65.00',
	payment: __( 'Credit Card', 'wc-custom-thank-you' ),
};

/**
 * Build the inline style object, only emitting a CSS custom property when the
 * merchant has actually chosen a value. This keeps the block from hardcoding
 * default colors, so the theme's own styling can show through until overridden.
 *
 * @param {Object} attributes Block attributes.
 * @return {Object} Style object for the block wrapper.
 */
function buildStyle( attributes ) {
	const {
		accentColor,
		accentTextColor,
		sectionBackgroundColor,
		paymentBackgroundColor,
		paymentBorderColor,
		paymentTextColor,
	} = attributes;

	const style = {};

	if ( accentColor ) {
		style[ '--wccty-accent-color' ] = accentColor;
	}
	if ( accentTextColor ) {
		style[ '--wccty-accent-text-color' ] = accentTextColor;
	}
	if ( sectionBackgroundColor ) {
		style[ '--wccty-section-bg' ] = sectionBackgroundColor;
	}
	if ( paymentBackgroundColor ) {
		style[ '--wccty-payment-bg' ] = paymentBackgroundColor;
	}
	if ( paymentBorderColor ) {
		style[ '--wccty-payment-border-color' ] = paymentBorderColor;
	}
	if ( paymentTextColor ) {
		style[ '--wccty-payment-text-color' ] = paymentTextColor;
	}

	return style;
}

/** Product image placeholder shared by the previews. */
function ProductImage( { show } ) {
	if ( ! show ) {
		return null;
	}
	return (
		<span
			className="order-item-image order-item-image--placeholder"
			aria-hidden="true"
		/>
	);
}

/** Sample SKU line shared by the previews. */
function Sku( { show } ) {
	if ( ! show ) {
		return null;
	}
	return (
		<span className="order-item-sku">
			{ __( 'SKU: SAMPLE-123', 'wc-custom-thank-you' ) }
		</span>
	);
}

/** Sample addresses block (Default / Hero share the same markup). */
function AddressCards( { s } ) {
	const showAddresses = s.showBillingAddress || s.showShippingAddress;
	if ( ! s.showCustomerDetails && ! showAddresses ) {
		return null;
	}
	return (
		<div className="order-details">
			{ s.showCustomerDetails && (
				<div className="customer-details">
					<h3>{ __( 'Customer Details', 'wc-custom-thank-you' ) }</h3>
					<p>
						{ D.name }
						<br />
						{ D.email }
					</p>
				</div>
			) }
			{ showAddresses && (
				<div className="addresses">
					{ s.showBillingAddress && (
						<div className="billing-address">
							<h3>{ __( 'Billing Address', 'wc-custom-thank-you' ) }</h3>
							<p>
								{ __( '123 Main Street', 'wc-custom-thank-you' ) }
								<br />
								{ __( 'City, State 12345', 'wc-custom-thank-you' ) }
							</p>
						</div>
					) }
					{ s.showShippingAddress && (
						<div className="shipping-address">
							<h3>{ __( 'Shipping Address', 'wc-custom-thank-you' ) }</h3>
							<p>
								{ __( '123 Main Street', 'wc-custom-thank-you' ) }
								<br />
								{ __( 'City, State 12345', 'wc-custom-thank-you' ) }
							</p>
						</div>
					) }
				</div>
			) }
		</div>
	);
}

/** Sample totals table shared by Default / Hero. */
function TotalsTable() {
	return (
		<div className="order-totals">
			<table>
				<tbody>
					<tr>
						<th>{ __( 'Subtotal:', 'wc-custom-thank-you' ) }</th>
						<td>{ D.subtotal }</td>
					</tr>
					<tr>
						<th>{ __( 'Shipping:', 'wc-custom-thank-you' ) }</th>
						<td>{ D.shipping }</td>
					</tr>
					<tr>
						<th>{ __( 'Tax:', 'wc-custom-thank-you' ) }</th>
						<td>{ D.tax }</td>
					</tr>
					<tr className="order-total">
						<th>{ __( 'Total:', 'wc-custom-thank-you' ) }</th>
						<td>{ D.total }</td>
					</tr>
				</tbody>
			</table>
		</div>
	);
}

/** Sample order note shared by the previews. */
function OrderNote( { show } ) {
	if ( ! show ) {
		return null;
	}
	return (
		<div className="order-customer-note">
			<h3>{ __( 'Order Note', 'wc-custom-thank-you' ) }</h3>
			<p>
				{ __(
					'Please leave the package at the front door.',
					'wc-custom-thank-you'
				) }
			</p>
		</div>
	);
}

/** Sample downloads shared by the previews. */
function Downloads( { show } ) {
	if ( ! show ) {
		return null;
	}
	return (
		<div className="order-downloads">
			<h3>{ __( 'Downloads', 'wc-custom-thank-you' ) }</h3>
			<ul>
				<li>
					<a href="#downloads-preview">
						{ __( 'Sample Download File', 'wc-custom-thank-you' ) }
					</a>
				</li>
			</ul>
		</div>
	);
}

/** "Default" template preview. */
function DefaultPreview( { s } ) {
	return (
		<>
			<div className="order-header">
				<h2>{ __( 'Order #12345', 'wc-custom-thank-you' ) }</h2>
				<p className="order-date">
					{ __( 'Order Date: January 1, 2024', 'wc-custom-thank-you' ) }
				</p>
				<p className="order-status">
					<strong>{ __( 'Status:', 'wc-custom-thank-you' ) }</strong>
					<span className="status-badge">{ D.status }</span>
				</p>
			</div>

			<OrderNote show={ s.showOrderNote } />
			<AddressCards s={ s } />

			{ s.showOrderItems && (
				<div className="order-items">
					<h3>{ __( 'Order Items', 'wc-custom-thank-you' ) }</h3>
					<table>
						<thead>
							<tr>
								<th>{ __( 'Product', 'wc-custom-thank-you' ) }</th>
								<th>{ __( 'Quantity', 'wc-custom-thank-you' ) }</th>
								<th>{ __( 'Price', 'wc-custom-thank-you' ) }</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div className="order-item-product">
										<ProductImage show={ s.showProductImages } />
										<div className="order-item-meta">
											<span className="order-item-name">{ D.product }</span>
											<Sku show={ s.showSku } />
										</div>
									</div>
								</td>
								<td>{ D.qty }</td>
								<td>{ D.price }</td>
							</tr>
						</tbody>
					</table>
				</div>
			) }

			<Downloads show={ s.showDownloads } />
			{ s.showOrderTotals && <TotalsTable /> }

			{ s.showPaymentMethod && (
				<div className="payment-method">
					<p>
						<strong>{ __( 'Payment Method:', 'wc-custom-thank-you' ) }</strong>{ ' ' }
						{ D.payment }
					</p>
				</div>
			) }
		</>
	);
}

/** "Hero + summary cards" template preview. */
function HeroPreview( { s, heroTitle, setHeroTitle } ) {
	return (
		<>
			<div className="wccty-hero-banner">
				<span className="wccty-hero-check" aria-hidden="true">
					✓
				</span>
				<RichText
					tagName="h2"
					className="wccty-hero-title"
					value={ heroTitle }
					onChange={ setHeroTitle }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
					placeholder={ __(
						'Thank you for your order!',
						'wc-custom-thank-you'
					) }
				/>
				<p className="wccty-hero-subtitle">
					{ __( 'Order #12345 · Processing', 'wc-custom-thank-you' ) }
				</p>
			</div>

			<div className="wccty-hero-cards">
				<div className="wccty-hero-card">
					<span className="wccty-hero-card-label">
						{ __( 'Order date', 'wc-custom-thank-you' ) }
					</span>
					<span className="wccty-hero-card-value">{ D.date }</span>
				</div>
				{ s.showOrderTotals && (
					<div className="wccty-hero-card">
						<span className="wccty-hero-card-label">
							{ __( 'Total', 'wc-custom-thank-you' ) }
						</span>
						<span className="wccty-hero-card-value">{ D.total }</span>
					</div>
				) }
				{ s.showPaymentMethod && (
					<div className="wccty-hero-card">
						<span className="wccty-hero-card-label">
							{ __( 'Payment', 'wc-custom-thank-you' ) }
						</span>
						<span className="wccty-hero-card-value">{ D.payment }</span>
					</div>
				) }
			</div>

			<OrderNote show={ s.showOrderNote } />

			{ s.showOrderItems && (
				<div className="wccty-hero-items">
					<h3>{ __( 'Order Items', 'wc-custom-thank-you' ) }</h3>
					<ul className="wccty-hero-item-list">
						<li className="wccty-hero-item">
							<ProductImage show={ s.showProductImages } />
							<div className="wccty-hero-item-meta">
								<span className="order-item-name">{ D.product }</span>
								<span className="wccty-hero-item-qty">
									{ __( 'Qty: 2', 'wc-custom-thank-you' ) }
								</span>
								<Sku show={ s.showSku } />
							</div>
							<span className="wccty-hero-item-price">{ D.price }</span>
						</li>
					</ul>
				</div>
			) }

			<Downloads show={ s.showDownloads } />
			{ s.showOrderTotals && <TotalsTable /> }
			<AddressCards s={ s } />
		</>
	);
}

/** "WooCommerce Core" template preview. */
function WooPreview( { s } ) {
	const showAddresses = s.showBillingAddress || s.showShippingAddress;
	return (
		<div className="woocommerce wccty-woo-preview">
			<p className="woocommerce-notice woocommerce-notice--success">
				{ __(
					'Thank you. Your order has been received.',
					'wc-custom-thank-you'
				) }
			</p>

			<ul className="woocommerce-order-overview order_details">
				<li>
					{ __( 'Order number:', 'wc-custom-thank-you' ) }{ ' ' }
					<strong>{ D.orderNumber }</strong>
				</li>
				<li>
					{ __( 'Date:', 'wc-custom-thank-you' ) }{ ' ' }
					<strong>{ D.date }</strong>
				</li>
				<li>
					{ __( 'Email:', 'wc-custom-thank-you' ) }{ ' ' }
					<strong>{ D.email }</strong>
				</li>
				{ s.showOrderTotals && (
					<li>
						{ __( 'Total:', 'wc-custom-thank-you' ) }{ ' ' }
						<strong>{ D.total }</strong>
					</li>
				) }
				{ s.showPaymentMethod && (
					<li>
						{ __( 'Payment method:', 'wc-custom-thank-you' ) }{ ' ' }
						<strong>{ D.payment }</strong>
					</li>
				) }
			</ul>

			<OrderNote show={ s.showOrderNote } />

			{ ( s.showOrderItems || s.showOrderTotals ) && (
				<section className="woocommerce-order-details">
					<h2 className="woocommerce-order-details__title">
						{ __( 'Order details', 'wc-custom-thank-you' ) }
					</h2>
					<table className="woocommerce-table woocommerce-table--order-details shop_table order_details">
						{ s.showOrderItems && (
							<>
								<thead>
									<tr>
										<th>{ __( 'Product', 'wc-custom-thank-you' ) }</th>
										<th>{ __( 'Total', 'wc-custom-thank-you' ) }</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											{ D.product }{ ' ' }
											<strong className="product-quantity">× 2</strong>
											{ s.showSku && (
												<>
													<br />
													<small>
														{ __(
															'SKU: SAMPLE-123',
															'wc-custom-thank-you'
														) }
													</small>
												</>
											) }
										</td>
										<td>{ D.price }</td>
									</tr>
								</tbody>
							</>
						) }
						{ s.showOrderTotals && (
							<tfoot>
								<tr>
									<th>{ __( 'Subtotal:', 'wc-custom-thank-you' ) }</th>
									<td>{ D.subtotal }</td>
								</tr>
								<tr>
									<th>{ __( 'Total:', 'wc-custom-thank-you' ) }</th>
									<td>{ D.total }</td>
								</tr>
							</tfoot>
						) }
					</table>
				</section>
			) }

			<Downloads show={ s.showDownloads } />

			{ s.showCustomerDetails && showAddresses && (
				<section className="woocommerce-customer-details">
					<section className="woocommerce-columns woocommerce-columns--2 col2-set addresses">
						{ s.showBillingAddress && (
							<div className="woocommerce-column col-1">
								<h2 className="woocommerce-column__title">
									{ __( 'Billing address', 'wc-custom-thank-you' ) }
								</h2>
								<address>
									{ D.name }
									<br />
									{ __( '123 Main Street', 'wc-custom-thank-you' ) }
									<br />
									{ __( 'City, State 12345', 'wc-custom-thank-you' ) }
								</address>
							</div>
						) }
						{ s.showShippingAddress && (
							<div className="woocommerce-column col-2">
								<h2 className="woocommerce-column__title">
									{ __( 'Shipping address', 'wc-custom-thank-you' ) }
								</h2>
								<address>
									{ D.name }
									<br />
									{ __( '123 Main Street', 'wc-custom-thank-you' ) }
									<br />
									{ __( 'City, State 12345', 'wc-custom-thank-you' ) }
								</address>
							</div>
						) }
					</section>
				</section>
			) }
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		template,
		heroTitle,
		accentColor,
		accentTextColor,
		sectionBackgroundColor,
		paymentBackgroundColor,
		paymentBorderColor,
		paymentTextColor,
		showCustomerDetails,
		showBillingAddress,
		showShippingAddress,
		showOrderNote,
		showOrderItems,
		showOrderTotals,
		showPaymentMethod,
		showDownloads,
		showProductImages,
		showSku,
	} = attributes;

	// Section-visibility flags shared with the previews.
	const s = {
		showCustomerDetails,
		showBillingAddress,
		showShippingAddress,
		showOrderNote,
		showOrderItems,
		showOrderTotals,
		showPaymentMethod,
		showDownloads,
		showProductImages,
		showSku,
	};

	const blockProps = useBlockProps( {
		className: `wccty-template-${ template || 'default' }`,
		style: buildStyle( attributes ),
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wccty-custom-content' },
		{
			template: TEMPLATE,
			templateLock: false,
		}
	);

	const toggle = ( key ) => ( value ) => setAttributes( { [ key ]: value } );

	let Preview = DefaultPreview;
	if ( 'hero' === template ) {
		Preview = HeroPreview;
	} else if ( 'woocommerce' === template ) {
		Preview = WooPreview;
	}

	// Color controls depend on the template: the WooCommerce Core layout inherits
	// the theme's styling, and the payment box only exists in the Default layout.
	const colorSettings = [
		{
			value: accentColor,
			onChange: ( value ) => setAttributes( { accentColor: value } ),
			label: __( 'Status badge / hero background', 'wc-custom-thank-you' ),
		},
		{
			value: accentTextColor,
			onChange: ( value ) => setAttributes( { accentTextColor: value } ),
			label: __( 'Status badge / hero text', 'wc-custom-thank-you' ),
		},
		{
			value: sectionBackgroundColor,
			onChange: ( value ) =>
				setAttributes( { sectionBackgroundColor: value } ),
			label: __(
				'Section background (cards, totals)',
				'wc-custom-thank-you'
			),
		},
	];

	if ( 'default' === template || ! template ) {
		colorSettings.push(
			{
				value: paymentBackgroundColor,
				onChange: ( value ) =>
					setAttributes( { paymentBackgroundColor: value } ),
				label: __( 'Payment box background', 'wc-custom-thank-you' ),
			},
			{
				value: paymentTextColor,
				onChange: ( value ) =>
					setAttributes( { paymentTextColor: value } ),
				label: __( 'Payment box text', 'wc-custom-thank-you' ),
			},
			{
				value: paymentBorderColor,
				onChange: ( value ) =>
					setAttributes( { paymentBorderColor: value } ),
				label: __( 'Payment box border', 'wc-custom-thank-you' ),
			}
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Template', 'wc-custom-thank-you' ) }>
					<SelectControl
						label={ __( 'Layout', 'wc-custom-thank-you' ) }
						value={ template || 'default' }
						options={ [
							{
								label: __( 'Default', 'wc-custom-thank-you' ),
								value: 'default',
							},
							{
								label: __( 'WooCommerce Core', 'wc-custom-thank-you' ),
								value: 'woocommerce',
							},
							{
								label: __( 'Hero + summary cards', 'wc-custom-thank-you' ),
								value: 'hero',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { template: value } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Sections', 'wc-custom-thank-you' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Customer details', 'wc-custom-thank-you' ) }
						checked={ showCustomerDetails }
						onChange={ toggle( 'showCustomerDetails' ) }
					/>
					<ToggleControl
						label={ __( 'Billing address', 'wc-custom-thank-you' ) }
						checked={ showBillingAddress }
						onChange={ toggle( 'showBillingAddress' ) }
					/>
					<ToggleControl
						label={ __( 'Shipping address', 'wc-custom-thank-you' ) }
						checked={ showShippingAddress }
						onChange={ toggle( 'showShippingAddress' ) }
					/>
					<ToggleControl
						label={ __( 'Order note', 'wc-custom-thank-you' ) }
						checked={ showOrderNote }
						onChange={ toggle( 'showOrderNote' ) }
					/>
					<ToggleControl
						label={ __( 'Order items', 'wc-custom-thank-you' ) }
						checked={ showOrderItems }
						onChange={ toggle( 'showOrderItems' ) }
					/>
					<ToggleControl
						label={ __( 'Order totals', 'wc-custom-thank-you' ) }
						checked={ showOrderTotals }
						onChange={ toggle( 'showOrderTotals' ) }
					/>
					<ToggleControl
						label={ __( 'Payment method', 'wc-custom-thank-you' ) }
						checked={ showPaymentMethod }
						onChange={ toggle( 'showPaymentMethod' ) }
					/>
					<ToggleControl
						label={ __( 'Downloads (downloadable orders)', 'wc-custom-thank-you' ) }
						checked={ showDownloads }
						onChange={ toggle( 'showDownloads' ) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Order items', 'wc-custom-thank-you' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show product images', 'wc-custom-thank-you' ) }
						checked={ showProductImages }
						onChange={ toggle( 'showProductImages' ) }
					/>
					<ToggleControl
						label={ __( 'Show product SKU', 'wc-custom-thank-you' ) }
						checked={ showSku }
						onChange={ toggle( 'showSku' ) }
					/>
				</PanelBody>

				{ 'woocommerce' === template ? (
					<PanelBody title={ __( 'Colors', 'wc-custom-thank-you' ) }>
						<Notice status="info" isDismissible={ false }>
							{ __(
								'The WooCommerce Core layout uses your theme’s WooCommerce styling, so the block’s color options do not apply here.',
								'wc-custom-thank-you'
							) }
						</Notice>
					</PanelBody>
				) : (
					<PanelColorSettings
						title={ __( 'Colors', 'wc-custom-thank-you' ) }
						colorSettings={ colorSettings }
					/>
				) }
			</InspectorControls>

			<div { ...blockProps }>
				<div { ...innerBlocksProps } />
				<Preview
					s={ s }
					heroTitle={ heroTitle }
					setHeroTitle={ ( value ) =>
						setAttributes( { heroTitle: value } )
					}
				/>
			</div>
		</>
	);
}
