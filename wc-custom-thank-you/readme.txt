=== Custom Thank You Page for WooCommerce ===
Contributors: riaanknoetze, nicolamustone
Tags: woocommerce, custom thank you page, woo thank you page, order confirmation page, order received page
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Replace the default WooCommerce Thank You page (order received page) with a custom Thank You page.

== Description ==

The WooCommerce "thank you" page (order confirmation / order received page) is one of the most important pages in your store: it’s where customers look for confirmation, next steps, downloads, shipping info, and payment instructions.

**Custom Thank You Page for WooCommerce** lets you replace the default WooCommerce Thank You page with a page you fully control — so you can deliver a branded order confirmation experience that matches your store and your audience.

= Attribution =

This plugin was originally created and maintained by [Nicola Mustone](https://profiles.wordpress.org/nicolamustone/). You should definitely check out his blog ["But. Honestly"](https://buthonestly.io/).

= Why you’ll love it =

* **Choose any WordPress page** as your custom WooCommerce Thank You page.
* **Automatic redirect after checkout** (customers land on your custom order confirmation page).
* **Block-based order confirmation**: add the included **Order Confirmation** block (Gutenberg / Site Editor / FSE) to show complete order details.
* **Shortcode support**: use `[wc_custom_thankyou]` if you prefer explicit placement (classic editor, templates, builders, etc).
* **Compatible by design**: uses WooCommerce core logic and the default `checkout/thankyou.php` template when needed, so it stays aligned with WooCommerce updates.
* **Modern WooCommerce compatibility**: declared compatible with **HPOS** (High-Performance Order Storage) and **Cart & Checkout blocks**.

= Use cases (what you can build) =

Turn your custom thank you page into a conversion, support, and retention asset:

* Add a personalized thank you message and “what happens next”
* Upsell/cross-sell related products after purchase
* Display delivery timeframes, FAQ, support links, and onboarding steps
* Add download instructions, community invites, or account setup links
* Add custom tracking scripts or conversion snippets (where appropriate)

= How it works =

1. Customer completes checkout.
2. WooCommerce redirects to its standard Order Received page.
3. This plugin safely redirects them to your configured custom Thank You page (keeping the required order context).
4. Your page can display the order confirmation details automatically, via shortcode, or via the block.

= Configuration (2 minutes) =

1. Go to **WooCommerce → Settings → Advanced**
2. Find **Custom Thank You**
3. Select your **Thank You Page** and save

= Block + Shortcode options =

* **Block (recommended for block themes / Site Editor):** On your custom Thank You page, add the **Order Confirmation** block.
* **Shortcode:** Add `[wc_custom_thankyou]` where you want the order confirmation details to appear.

If you do nothing else, the plugin will append the standard WooCommerce Thank You template output to your page content (for backwards compatibility).

= Support =

Support is best-effort. If you run into an issue, please include:

* Your WordPress + WooCommerce versions
* Your theme name (and whether it’s a block theme)
* The exact steps to reproduce

= Get involved =

If you want to help, consider [translating the plugin into your language](https://translate.wordpress.org/projects/wp-plugins/wc-custom-thank-you/).

== Screenshots ==

1. Custom thank you (order confirmation) page setup
2. Thank You page block setup
3. Front-end thank you page (classic display)
4. Front-end thank you page (block display)

== Installation ==

= Minimum Requirements =

* WordPress 6.5 or greater
* PHP 7.4 or greater
* MySQL 5.6 or greater (or MariaDB equivalent)
* WooCommerce 8.0 or greater

= Automatic installation =

1. Log in to your WordPress dashboard
2. Go to **Plugins → Add New**
3. Search for **“Custom Thank You Page for WooCommerce”**
4. Click **Install Now**, then **Activate**

= Manual installation =

Download the plugin and upload it to your server. The WordPress documentation contains [instructions on how to do this here](https://wordpress.org/documentation/article/managing-plugins/#manual-plugin-installation).

== Frequently Asked Questions ==

= I receive a PHP error on my custom Thank You page. Why? =

Make sure you are running:

* WordPress 6.5 or newer
* WooCommerce 8.0 or newer
* PHP 7.4 or newer

Also ensure:

* The **Thank You Page** option in **WooCommerce → Settings → Advanced** is set to an existing published page
* You are viewing the page after a real checkout, so the URL includes the required order context

= My custom page shows, but the order confirmation details do not. =

Order details only appear when the request contains valid order data (the `order` and `key` parameters). The plugin adds these automatically after checkout.

To test: place a real order, then you’ll be redirected to your custom order confirmation page with the correct URL parameters.

= Should I use the block, the shortcode, or “automatic” output? =

* **Use the block** if you’re building a modern block-based thank you page (Gutenberg / FSE) and you want a dedicated order confirmation layout.
* **Use the shortcode** if you want explicit placement inside page builders, templates, or classic content.
* **Automatic output** is best for simple setups: your content stays, and WooCommerce’s default thank you template is appended.

= Will payment instructions from gateways still work? =

Yes. When the Order Confirmation block is used, the plugin avoids duplicating WooCommerce’s legacy order details markup, but still runs WooCommerce “thankyou” hooks so payment gateways and extensions can output instructions as expected.

= Can I customize the templates? =

When using automatic output / shortcode, this plugin uses WooCommerce’s default `checkout/thankyou.php` template. You can override and customize it in your theme or child theme. Learn how here: https://woo.com/document/template-structure/

= I have a custom language file. Where do I save it? =

This plugin loads language files from:

* `wp-content/languages/wc-custom-thank-you/wc-custom-thank-you-{YOURLOCALE}.mo`
* `wp-content/languages/plugins/wc-custom-thank-you-{YOURLOCALE}.mo`
* `wp-content/plugins/wc-custom-thank-you/languages/`

Put your custom language files in one of these locations (**the first one is recommended**). If you save the files in the last location you will lose them when updating the plugin.

== Changelog ==

= 2.1.0 - 2026-03-24 =
* New - WC Version compatibility
* Fix - Readme fixes

= 2.0.0 - 2026-01-01 =
* New - Completely rewritten codebase
* New - WooCommerce compatibility
* New - Tested with WordPress 6.9
* New - Added an Order Confirmation block for Gutenberg / Site Editor (FSE) to display complete order details
* New - Declared compatibility with WooCommerce High-Performance Order Storage (HPOS)
* New - Declared compatibility with WooCommerce Cart & Checkout blocks
* New - Added a Settings link on the Plugins screen for faster access
* New - Localization for popular languages (Arabic, Danish, German, Greek, Spanish, Finnish, French, Hebrew, Indonesian, Italian, Japanese, Korean, Dutch, Portuguese, Russian, Swedish, Turkish, and Chinese)
* Update - Shortcode support: `[wc_custom_thankyou]`
* Update - Refactored codebase for modern WooCommerce versions and APIs
* Update - Limited `the_content` filter to the configured Thank You page only, to reduce overhead on the rest of the site
* Update - text domain to `wc-custom-thank-you` and language file paths

= 1.2.1 - 2018-07-19 =
* Tested with WooCommerce 3.4.3
* Tested with WordPress 4.9.7
* Moved the page option in WooCommerce > Settings > Advanced

= 1.2.0 - 2017-04-05 =
* Compatibility test for WordPress 4.7.3
* Compatibility with WooCommerce 3.0
* Compatibility with WPML
* Dropped support for WooCommerce < 3.0 - Update WooCommerce to