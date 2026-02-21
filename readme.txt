=== Dynamic Metal Price Calculator ===
Contributors: aolo
Donate link: https://aolo.software/
Tags: woocommerce, jewellery, gold, silver, calculator
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Dynamic WooCommerce jewellery pricing using live metal rates with support for karat, weight, GST, markup, wastage, shipping, and variable products.

== Description ==
Dynamic Metal Price Calculator enables store owners to set gold, silver, and custom metal rates from a simple admin page. Add per-product metadata (metal type, weight, base price, wastage, making charge, markup, GST, shipping) and automatically compute final product prices. Supports variable products with per-variation pricing.

= Features =
* Set gold, silver, and custom metal rates from admin
* Support for 24K, 22K, 20K, and 18K gold
* Per-product weight, base price, wastage, making charge, markup
* Global and per-product GST support
* Global and per-product flat shipping
* Variable product support - set metal pricing for each variation
* Custom metal formulas
* Dynamic price calculation at cart and checkout

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/dynamic-metal-price-calculator` directory, or install via the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure global options under **WooCommerce > Settings > Metal Price Calculator**.
4. Edit products and set per-product metal type, weight, wastage, making charge, markup, GST, and shipping.
5. For variable products, set metal pricing in each variation.

== Frequently Asked Questions ==
= Does this plugin support multiple metals? =
Yes, you can define rates for gold, silver, platinum, or custom metals.

= How does GST work? =
You can set a global GST percentage, or override it per product.

= Does this plugin support variable products? =
Yes! You can set metal type, weight, base price, wastage, making charge, markup, GST, and shipping for each variation individually.

== Screenshots ==
1. Product edit screen with metal fields
2. Frontend product price calculation
3. Global settings page

== Changelog ==
= 3.0.0 =
* Added variable product support - now you can set metal pricing for each variation
* Added variation metal pricing fields in admin (metal type, weight, base price, wastage, making charge, markup, GST, shipping)
* Dynamic price calculation for variable products

= 2.2.1 =
* Added per-product shipping override
* Improved sanitization of input fields
* Updated tested up to WordPress 6.7

= 2.2.0 =
* Introduced markup percentage option
* Bug fixes and performance improvements

= 2.1.0 =
* Initial public release

== Upgrade Notice ==
= 3.0.0 =
Major update: Added variable product support. Update now to enable metal pricing for variable products.

---

For support, bug reports, or feature requests, contact **Aolo Software**  
https://aolo.software/  
info@aolo.software

© Aolo Software. Licensed under GPLv2 or later.
