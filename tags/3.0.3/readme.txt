=== Dynamic Metal Price Calculator ===
Contributors: aolo
Donate link: https://tresifylab.com/
Tags: woocommerce, jewellery, jewelry, gold, silver, calculator
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 3.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free WooCommerce jewellery pricing calculator for gold, silver, karat, weight, making charge, wastage, markup, GST, shipping, sale prices, and variable products.

== Description ==

Dynamic Metal Price Calculator helps WooCommerce jewellery stores calculate product prices from metal rate, weight, karat, and extra charges.

This is the free version. It keeps the useful pricing tools available without locking current features.

= What the plugin does =

Use it when your product price depends on metal value instead of a fixed catalog price.

Basic example:

* Gold Rate / gram: 7,000
* Weight: 2g
* Metal value: 14,000

The plugin can then add base price, making charge, wastage, markup, GST, and shipping if you set them.

= Free version features =

* Manual Gold Rate / gram
* Manual Silver Rate / gram
* 24K gold base rate
* 22K, 20K, and 18K purity percentages
* Silver product pricing
* Custom metals and simple formulas
* Product-level metal type
* Product-level metal weight
* Product-level base price
* Making charge
* Wastage percentage
* Markup percentage
* GST / tax value
* Shipping value
* Variable product support
* Variation-level metal pricing
* Product page price calculation
* Cart price calculation
* Checkout price calculation
* Optional product page breakdown
* WooCommerce regular/sale price support
* HPOS compatibility declaration
* Basic WooCommerce Blocks / Store API compatibility declaration
* Translation-ready text

= Simple price flow =

1. Enter gold and silver rates per gram.
2. Open a WooCommerce product.
3. Select metal type.
4. Enter product weight in grams.
5. Add charges if needed.
6. Save the product.
7. Check product, cart, and checkout prices.

= Regular price and sale price behavior =

The plugin now respects normal WooCommerce pricing fields.

Use this checklist to understand what happens:

* [x] Regular price and Sale price are both set: WooCommerce manual regular/sale prices win.
* [x] Regular price is set and Sale price is blank: calculated metal price can show as the sale price.
* [x] Regular price is set and no metal details are added: regular price shows normally.
* [x] Regular price and Sale price are blank: plugin calculated price shows normally.
* [x] Manual Sale price is set: manual sale price always wins.
* [x] Calculated metal price is higher than Regular price: no fake sale discount is shown.

Example:

* Regular price: 20,000
* Sale price: blank
* Metal calculated price: 16,500

Frontend result:

* 20,000 appears as the crossed-out regular price.
* 16,500 appears as the active sale price.

If you manually enter a Sale price, the plugin will not replace it.

= Product fields explained =

* Metal Type: choose gold, silver, or custom metal.
* Weight: enter product metal weight in grams.
* Base Price: add fixed cost like stone, design, or packaging.
* Making Charge: add labour or production cost.
* Wastage: add percentage for metal loss.
* Markup: add your profit margin.
* GST / Tax: add or override tax value.
* Shipping: add or override flat shipping value.

= Variable products =

Variable products are supported.

Use variation pricing when each variation has a different weight, size, design, or charge.

Example:

* Ring Size 6: 2.1g
* Ring Size 7: 2.3g
* Ring Size 8: 2.5g

Each variation can use its own metal type, weight, base price, making charge, wastage, markup, GST, and shipping.

= Custom metals and simple formulas =

You can create a custom metal rate from gold or silver.

Example:

`gold_rate * 1.25`

This creates a rate 25% higher than the gold rate.

Use simple formulas only. Advanced conditional formulas are not included in this free version.

= Frontend price breakdown =

The plugin can show extra charge details on product pages.

The display can include:

* Making charge
* Wastage
* GST / tax
* Shipping

You can turn this display on or off from the admin settings.

CSS class for styling:

`.dmmp-extra-charges`

= Admin panel =

Version 3.0.3 includes a cleaner admin panel with:

* Setup checklist
* Collapsible settings sections
* Shorter field descriptions
* Clear per-gram metal rate labels
* Simple setup examples
* Compatibility status

== Features ==

= Metal rates =

* Gold Rate / gram
* Silver Rate / gram
* Custom metal formulas
* 22K, 20K, and 18K purity percentages

= Product pricing =

* Metal type
* Weight in grams
* Base price
* Making charge
* Wastage
* Markup
* GST / tax
* Shipping

= WooCommerce price display =

* Normal regular price support
* Normal sale price support
* Automatic calculated sale price when sale price is blank
* Manual sale price priority
* Regular price strikethrough when calculated price is used as sale price

= Variable products =

* Variation-level metal type
* Variation-level weight
* Variation-level charges
* Variation sale price display support

= Cart and checkout =

* Product page calculation
* Cart calculation
* Checkout calculation
* Correct final line item price

= Admin experience =

* Beginner-friendly settings page
* Collapsible sections
* Short explanations
* Simple examples
* Optional frontend breakdown display

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/dynamic-metal-price-calculator` directory, or install it from the WordPress plugins screen.
2. Activate the plugin.
3. Open the plugin settings page.
4. Enter Gold Rate / gram and Silver Rate / gram.
5. Open a WooCommerce product.
6. Select metal type and enter weight.
7. Add making charge, wastage, markup, GST, or shipping if needed.
8. Save the product and check the product page, cart, and checkout.

== Frequently Asked Questions ==

= Is this plugin free? =

Yes. The listed features are available in the free version.

= Does it use live metal rates? =

No. The free version uses manual rates entered by the store owner.

Enter today's Gold Rate / gram and Silver Rate / gram in the plugin settings.

= What does Gold Rate / gram mean? =

It is the 24K gold price per gram used by your store.

Example:

Gold Rate / gram: 7,000
Weight: 2g
Metal value: 14,000

= What does Silver Rate / gram mean? =

It is the silver price per gram used by your store.

Example:

Silver Rate / gram: 100
Weight: 10g
Metal value: 1,000

= How does the basic calculation work? =

The plugin starts with:

Metal rate x weight

Then it can add:

* Base price
* Making charge
* Wastage
* Markup
* GST / tax
* Shipping

= Does it support 22K, 20K, and 18K gold? =

Yes. You can set purity percentages for 22K, 20K, and 18K based on the 24K gold rate.

Example:

If 22K is set to 93, the plugin uses 93% of the 24K gold rate.

= Does it support silver products? =

Yes. Select Silver as the product metal type and enter the product weight.

= Does it support custom metals? =

Yes. You can add a custom metal using a simple formula.

Example:

`gold_rate * 1.25`

= Does it support variable products? =

Yes. Each variation can have its own metal type, weight, and charges.

Use this when ring size, chain length, or design changes the metal weight.

= Does it support WooCommerce Regular price and Sale price? =

Yes. The plugin keeps WooCommerce price behavior clear.

* [x] Manual Regular price + manual Sale price: manual prices win.
* [x] Regular price + blank Sale price + lower calculated metal price: calculated price shows as sale price.
* [x] Regular price only + no metal data: regular price shows normally.
* [x] Blank Regular and Sale price + metal data: calculated price shows normally.
* [x] Manual Sale price: always takes priority.

= Can the calculated metal price show as a sale price? =

Yes, when all of these are true:

* Regular price is set.
* Sale price is blank.
* Metal pricing details are added.
* Calculated metal price is lower than Regular price.

Then WooCommerce shows:

Regular price crossed out, calculated price active.

= What happens if the calculated price is higher than the Regular price? =

The plugin does not show a fake sale.

It will not display a discount when the calculated price is higher than the Regular price.

= Does manual Sale price override the calculated price? =

Yes. If you enter a WooCommerce Sale price, the manual Sale price wins.

= Will cart and checkout use the correct price? =

Yes. Cart and checkout use the active price based on the same priority rules.

= Can customers see the calculation breakdown? =

Yes, if you enable the product page breakdown setting.

You can show or hide extra charge details such as making charge, wastage, GST, and shipping.

= What is .dmmp-extra-charges? =

It is the CSS class used for the product page extra charge display.

Theme developers can style it if needed.

= Does this plugin change WooCommerce tax or shipping settings? =

No. The plugin has its own GST and flat shipping fields for calculation. WooCommerce tax and shipping settings remain separate.

= Is it HPOS compatible? =

The plugin declares compatibility with WooCommerce High-Performance Order Storage.

= Does the plugin send data outside the site? =

Only if the site admin submits the optional welcome popup. The popup can be skipped.

== Screenshots ==

1. Product edit screen with metal pricing fields
2. Frontend product price calculation
3. Global settings page

== Changelog ==

= 3.0.3 =
* Improved admin panel layout
* Added collapsible settings sections
* Added clearer field explanations
* Added setup guidance for new users
* Added better product pricing instructions
* Improved variable product setup guidance
* Improved frontend display setting descriptions
* Improved cart and checkout setting descriptions
* Improved order calculation snapshot guidance
* Improved overall admin user experience
* Kept all existing free features available
* Fixed WooCommerce regular/sale price display for dynamic metal pricing products
* Cleaned admin panel wording
* Shortened field descriptions
* Improved metal rate labels
* Added clearer per gram wording
* Simplified examples
* Reduced popup text
* Improved settings page readability
* Fixed WooCommerce regular and sale price display
* Added automatic calculated sale price behavior
* Manual sale price now takes priority over calculated price
* Regular price now shows with strikethrough when calculated metal price is used as sale price
* Fixed product edit price field layout inside WooCommerce General product data

= 3.0.2 =
* Added an opt-in activation welcome popup to collect setup/support email leads for Tresify Lab
* Added a frontend extra charges display setting so stores can hide GST, shipping, making charge, and wastage details by default

= 3.0.1 =
* Updated ownership links to Tresify Lab
* Confirmed compatibility with WordPress 7.0

= 3.0.0 =
* Added variable product support - now you can set metal pricing for each variation
* Added variation metal pricing fields in admin
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

= 3.0.3 =
Cleaner admin page, clearer per-gram labels, better regular/sale price behavior, and the same free feature set.

= 3.0.2 =
Adds an optional activation welcome popup and a frontend extra charges visibility setting.

= 3.0.1 =
Updated ownership links and WordPress 7.0 compatibility metadata.

= 3.0.0 =
Major update: Added variable product support.

---

For support, bug reports, or feature requests, contact **Tresify Lab**
https://tresifylab.com/
info@tresifylab.com

(c) Tresify Lab. Licensed under GPLv2 or later.
