
/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

import { InspectorControls, PanelColorSettings, useBlockProps } from '@wordpress/block-editor';
import { BaseControl, ColorPalette, PanelBody } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {
		accentColor,
		accentTextColor,
		sectionBackgroundColor,
		paymentBackgroundColor,
		paymentBorderColor,
		paymentTextColor,
	} = attributes;

	const blockProps = useBlockProps( {
		style: {
			'--wccty-accent-color': accentColor,
			'--wccty-accent-text-color': accentTextColor,
			'--wccty-section-bg': sectionBackgroundColor,
			'--wccty-payment-bg': paymentBackgroundColor,
			'--wccty-payment-border-color': paymentBorderColor,
			'--wccty-payment-text-color': paymentTextColor,
		},
	} );

	return (
		<>
			<InspectorControls>
				<PanelColorSettings
					title={ __( 'Status badge', 'wc-custom-thank-you' ) }
					colorSettings={ [
						{
							value: accentColor,
							onChange: ( value ) =>
								setAttributes( {
									accentColor: value || '#4caf50',
								} ),
							label: __( 'Background', 'wc-custom-thank-you' ),
						},
						{
							value: accentTextColor,
							onChange: ( value ) =>
								setAttributes( {
									accentTextColor: value || '#ffffff',
								} ),
							label: __( 'Text', 'wc-custom-thank-you' ),
						},
					] }
				/>

				<PanelBody
					title={ __( 'Section background (addresses, totals)', 'wc-custom-thank-you' ) }
					initialOpen={ true }
				>
					<BaseControl label={ __( 'Background', 'wc-custom-thank-you' ) }>
						<ColorPalette
							value={ sectionBackgroundColor }
							onChange={ ( value ) =>
								setAttributes( {
									sectionBackgroundColor: value || '#f9f9f9',
								} )
							}
						/>
					</BaseControl>
				</PanelBody>

				<PanelColorSettings
					title={ __( 'Payment box', 'wc-custom-thank-you' ) }
					colorSettings={ [
						{
							value: paymentBackgroundColor,
							onChange: ( value ) =>
								setAttributes( {
									paymentBackgroundColor: value || '#e3f2fd',
								} ),
							label: __( 'Background', 'wc-custom-thank-you' ),
						},
						{
							value: paymentTextColor,
							onChange: ( value ) =>
								setAttributes( {
									paymentTextColor: value || '#333333',
								} ),
							label: __( 'Text', 'wc-custom-thank-you' ),
						},
						{
							value: paymentBorderColor,
							onChange: ( value ) =>
								setAttributes( {
									paymentBorderColor: value || '#2196f3',
								} ),
							label: __( 'Border', 'wc-custom-thank-you' ),
						},
					] }
				/>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="order-header">
					<h2>{ __( 'Order #12345', 'wc-custom-thank-you' ) }</h2>
					<p className="order-date">
						{ __( 'Order Date: January 1, 2024', 'wc-custom-thank-you' ) }
					</p>
					<p className="order-status">
						<strong>{ __( 'Status:', 'wc-custom-thank-you' ) }</strong>
						<span className="status-badge">
							{ __( 'Processing', 'wc-custom-thank-you' ) }
						</span>
					</p>
				</div>

				<div className="order-details">
					<div className="customer-details">
						<h3>{ __( 'Customer Details', 'wc-custom-thank-you' ) }</h3>
						<p>
							{ __( 'John Doe', 'wc-custom-thank-you' ) }
							<br />
							{ __( 'john@example.com', 'wc-custom-thank-you' ) }
						</p>
					</div>

					<div className="addresses">
						<div className="billing-address">
							<h3>{ __( 'Billing Address', 'wc-custom-thank-you' ) }</h3>
							<p>
								{ __( '123 Main Street', 'wc-custom-thank-you' ) }
								<br />
								{ __( 'City, State 12345', 'wc-custom-thank-you' ) }
							</p>
						</div>

						<div className="shipping-address">
							<h3>{ __( 'Shipping Address', 'wc-custom-thank-you' ) }</h3>
							<p>
								{ __( '123 Main Street', 'wc-custom-thank-you' ) }
								<br />
								{ __( 'City, State 12345', 'wc-custom-thank-you' ) }
							</p>
						</div>
					</div>
				</div>

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
								<td>{ __( 'Sample Product', 'wc-custom-thank-you' ) }</td>
								<td>2</td>
								<td>$50.00</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div className="order-totals">
					<table>
						<tbody>
							<tr>
								<th>{ __( 'Subtotal:', 'wc-custom-thank-you' ) }</th>
								<td>$50.00</td>
							</tr>
							<tr>
								<th>{ __( 'Shipping:', 'wc-custom-thank-you' ) }</th>
								<td>$10.00</td>
							</tr>
							<tr>
								<th>{ __( 'Tax:', 'wc-custom-thank-you' ) }</th>
								<td>$5.00</td>
							</tr>
							<tr className="order-total">
								<th>{ __( 'Total:', 'wc-custom-thank-you' ) }</th>
								<td>$65.00</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div className="payment-method">
					<p>
						<strong>{ __( 'Payment Method:', 'wc-custom-thank-you' ) }</strong>{ ' ' }
						{ __( 'Credit Card', 'wc-custom-thank-you' ) }
					</p>
				</div>
			</div>
		</>
	);
}