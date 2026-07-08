/**
 * Persist the nested blocks so the dynamic renderer (render.php) receives them
 * as $content. The order details themselves are rendered server-side; only the
 * merchant's custom inner blocks are saved here.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 */
import { InnerBlocks } from '@wordpress/block-editor';

export default function save() {
	return <InnerBlocks.Content />;
}
