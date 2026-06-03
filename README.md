# Dynamic Metal Price Calculator

Dynamic Metal Price Calculator is a WooCommerce plugin for jewellery stores that need product prices based on metal rate, weight, purity, and extra charges.

It helps stores calculate gold, silver, and custom metal product prices without manually editing every product price again and again.

The plugin is built for jewellery, gold shops, silver shops, handmade jewellery sellers, and WooCommerce stores where the final product price depends on live business costs instead of a fixed catalog price.

---

## What This Plugin Does

Most jewellery products do not have a simple fixed price.

The final price usually depends on:

- Current gold or silver rate
- Product weight
- Gold purity
- Making charge
- Wastage
- Markup
- Tax or GST
- Shipping or extra cost

Dynamic Metal Price Calculator lets you add these values inside WooCommerce and calculate the final product price automatically.

Example:

Gold rate per gram: 7,000  
Product weight: 2g  
Metal value: 14,000  

Then the plugin can add base price, making charge, wastage, markup, GST, and shipping based on your settings.

---

## Main Features

### Metal Rate Settings

- Manual Gold Rate per gram
- Manual Silver Rate per gram
- 24K gold base rate
- 22K gold purity percentage
- 20K gold purity percentage
- 18K gold purity percentage
- Silver product pricing
- Custom metal formula support
- Simple formula-based custom metal rates

Example custom formula:

gold_rate * 1.25

This creates a custom metal rate 25% higher than the current gold rate.

---

### Product-Level Metal Pricing

Each WooCommerce product can have its own metal pricing details.

Supported product fields:

- Metal type
- Metal weight in grams
- Base price
- Making charge
- Wastage percentage
- Markup percentage
- GST or tax value
- Shipping value

This gives store owners better control over each jewellery product.

---

### Gold Pricing Support

The plugin uses 24K gold as the base gold rate.

You can set purity percentages for:

- 24K gold
- 22K gold
- 20K gold
- 18K gold

Example:

If your 24K gold rate is 7,000 per gram and 22K is set to 93%, the plugin calculates the 22K rate based on 93% of the 24K rate.

---

### Silver Pricing Support

The plugin also supports silver products.

Set the Silver Rate per gram from the plugin settings, then choose Silver as the metal type inside the WooCommerce product.

Example:

Silver rate per gram: 100  
Product weight: 10g  
Metal value: 1,000  

---

### Custom Metal Support

You can create custom metal rates using simple formulas.

Example:

gold_rate * 1.25

You can use this when your product uses a special alloy, mixed metal, premium gold type, or custom shop pricing rule.

Advanced conditional formulas are not included in the free version.

---

## WooCommerce Regular Price and Sale Price Support

The plugin respects normal WooCommerce pricing behavior.

This is useful when you want to show the regular price crossed out and the calculated metal price as the sale price.

### Pricing Rules

- If Regular price and Sale price are both set, WooCommerce manual prices win.
- If Regular price is set and Sale price is blank, the calculated metal price can show as the sale price.
- If Regular price is set and no metal details are added, the regular price shows normally.
- If Regular price and Sale price are blank, the plugin calculated price shows normally.
- If manual Sale price is set, manual Sale price always wins.
- If calculated metal price is higher than Regular price, the plugin does not show a fake discount.

### Example

Regular price: 20,000  
Sale price: blank  
Metal calculated price: 16,500  

Frontend result:

- 20,000 shows as crossed-out regular price
- 16,500 shows as the active sale price

If you manually enter a Sale price, the plugin will not replace it.

---

## Variable Product Support

Dynamic Metal Price Calculator supports WooCommerce variable products.

Each variation can have its own metal pricing data.

This is useful when different product options have different weights or costs.

Example:

Ring Size 6: 2.1g  
Ring Size 7: 2.3g  
Ring Size 8: 2.5g  

Each variation can have its own:

- Metal type
- Weight
- Base price
- Making charge
- Wastage
- Markup
- GST or tax
- Shipping

---

## Frontend Price Calculation

The plugin calculates prices on:

- Product page
- Cart page
- Checkout page

The final cart and checkout price follows the same pricing priority rules used on the product page.

---

## Product Page Price Breakdown

The plugin can show extra charge details on the product page.

The breakdown can include:

- Making charge
- Wastage
- GST or tax
- Shipping

You can turn this display on or off from the plugin settings.

CSS class for styling:

.dmmp-extra-charges

Theme developers can style this class to match the store design.

---

## Admin Panel

Version 3.0.3 includes a cleaner admin panel with better setup guidance.

Admin improvements include:

- Setup checklist
- Collapsible settings sections
- Shorter field descriptions
- Clear per-gram metal rate labels
- Simple setup examples
- Compatibility status
- Cleaner wording
- Better product pricing instructions
- Better variable product setup guidance

---

## Compatibility

The plugin is built for WooCommerce stores.

Current compatibility details:

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce support
- WooCommerce variable product support
- WooCommerce regular and sale price support
- WooCommerce cart price support
- WooCommerce checkout price support
- HPOS compatibility declaration
- Basic WooCommerce Blocks / Store API compatibility declaration
- Translation-ready text

---

## Installation

1. Download the plugin.
2. Upload the plugin folder to:

/wp-content/plugins/dynamic-metal-price-calculator

3. Activate the plugin from the WordPress admin dashboard.
4. Open the plugin settings page.
5. Enter Gold Rate per gram and Silver Rate per gram.
6. Open a WooCommerce product.
7. Select the metal type.
8. Enter the product weight in grams.
9. Add base price, making charge, wastage, markup, GST, or shipping if needed.
10. Save the product.
11. Check the product page, cart, and checkout.

---

## Basic Setup Flow

1. Set your gold and silver rates.
2. Configure gold purity percentages.
3. Open a WooCommerce product.
4. Choose the product metal type.
5. Enter the product weight.
6. Add charges if needed.
7. Save the product.
8. Test the frontend product price.
9. Add the product to cart.
10. Check the checkout price.

---

## Example Calculation Flow

Metal value:

Metal rate x product weight

Then the plugin can add:

- Base price
- Making charge
- Wastage
- Markup
- GST or tax
- Shipping

Example:

Gold rate per gram: 7,000  
Weight: 2g  
Metal value: 14,000  
Making charge: 1,000  
Markup: 10%  

The plugin calculates the product price based on the values added by the store owner.

---

## FAQ

### Is this plugin free?

Yes. The listed features are available in the free version.

---

### Does the plugin use live metal rates?

No. The free version uses manual metal rates.

You need to enter the Gold Rate per gram and Silver Rate per gram from the plugin settings.

---

### What does Gold Rate per gram mean?

It means the 24K gold price per gram used by your store.

Example:

Gold Rate per gram: 7,000  
Weight: 2g  
Metal value: 14,000  

---

### What does Silver Rate per gram mean?

It means the silver price per gram used by your store.

Example:

Silver Rate per gram: 100  
Weight: 10g  
Metal value: 1,000  

---

### Does it support 22K, 20K, and 18K gold?

Yes.

You can set purity percentages for 22K, 20K, and 18K gold based on your 24K gold rate.

Example:

If 22K is set to 93%, the plugin uses 93% of the 24K gold rate.

---

### Does it support silver products?

Yes.

Select Silver as the product metal type and enter the product weight.

---

### Does it support custom metals?

Yes.

You can create custom metal rates using simple formulas.

Example:

gold_rate * 1.25

---

### Does it support variable products?

Yes.

Each variation can have its own metal type, weight, and charges.

This is useful for products where size, length, design, or material changes the weight.

---

### Does it support WooCommerce Regular price and Sale price?

Yes.

The plugin supports normal WooCommerce Regular price and Sale price behavior.

Manual Regular price and manual Sale price take priority when both are set.

---

### Can the calculated metal price show as a sale price?

Yes.

This happens when:

- Regular price is set
- Sale price is blank
- Metal pricing details are added
- Calculated metal price is lower than Regular price

Then WooCommerce shows the Regular price crossed out and the calculated metal price as the active price.

---

### What happens if the calculated price is higher than the Regular price?

The plugin does not show a fake sale.

If the calculated metal price is higher than the Regular price, it will not display a discount.

---

### Does manual Sale price override the calculated price?

Yes.

If you enter a WooCommerce Sale price manually, the manual Sale price wins.

---

### Will cart and checkout use the correct price?

Yes.

Cart and checkout use the active product price based on the same priority rules.

---

### Can customers see the calculation breakdown?

Yes, if you enable the product page breakdown setting.

You can show or hide details like:

- Making charge
- Wastage
- GST or tax
- Shipping

---

### What is .dmmp-extra-charges?

.dmmp-extra-charges is the CSS class used for the product page extra charge display.

Theme developers can style this class if needed.

---

### Does this plugin change WooCommerce tax or shipping settings?

No.

The plugin has its own GST and flat shipping fields for calculation.

WooCommerce tax and shipping settings remain separate.

---

### Is it HPOS compatible?

Yes.

The plugin declares compatibility with WooCommerce High-Performance Order Storage.

---

### Does the plugin send data outside the site?

Only if the site admin submits the optional welcome popup.

The popup can be skipped.

---

## Changelog

### 3.0.3

- Improved admin panel layout
- Added collapsible settings sections
- Added clearer field explanations
- Added setup guidance for new users
- Added better product pricing instructions
- Improved variable product setup guidance
- Improved frontend display setting descriptions
- Improved cart and checkout setting descriptions
- Improved order calculation snapshot guidance
- Improved overall admin experience
- Kept all existing free features available
- Fixed WooCommerce regular and sale price display for dynamic metal pricing products
- Cleaned admin panel wording
- Shortened field descriptions
- Improved metal rate labels
- Added clearer per-gram wording
- Simplified examples
- Reduced popup text
- Improved settings page readability
- Added automatic calculated sale price behavior
- Manual sale price now takes priority over calculated price
- Regular price now shows with strikethrough when calculated metal price is used as sale price
- Fixed product edit price field layout inside WooCommerce General product data

### 3.0.2

- Added optional activation welcome popup
- Added frontend extra charges display setting
- Store owners can hide GST, shipping, making charge, and wastage details by default

### 3.0.1

- Updated ownership links to Tresify Lab
- Confirmed compatibility with newer WordPress versions

### 3.0.0

- Added variable product support
- Added variation-level metal pricing fields
- Added dynamic price calculation for variable products

### 2.2.1

- Added per-product shipping override
- Improved sanitization of input fields
- Updated tested WordPress version

### 2.2.0

- Added markup percentage option
- Added bug fixes and performance improvements

### 2.1.0

- Initial public release

---

## Best Use Cases

This plugin is useful for:

- Gold jewellery stores
- Silver jewellery stores
- WooCommerce jewellery shops
- Handmade jewellery brands
- Custom jewellery sellers
- Stores with product prices based on weight
- Stores that need regular price and calculated sale price display
- Stores that sell variable jewellery products like rings, chains, bangles, and bracelets

---

## Why Use Dynamic Metal Price Calculator?

Use this plugin if your WooCommerce product price changes based on metal value.

Instead of manually calculating each jewellery product price, you can set the metal rate once and let the plugin calculate prices from product weight and charges.

This saves time, reduces pricing mistakes, and keeps WooCommerce product pricing cleaner.

---

## Author

Dynamic Metal Price Calculator is developed by Sheikh Abir Ali.

---

## License

This plugin is open-source software.

Use it, modify it, and improve it based on your WooCommerce store needs.
