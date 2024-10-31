=== Purchased Items Column for WooCommerce Orders ===
Contributors: pipdig
Tags: woocommerce, orders, products, shop, admin
Requires at least: 4.8
Tested up to: 6.6
Requires PHP: 7.0
WC requires at least: 3.0
WC tested up to: 9.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display a "Purchased Items" column on the WooCommerce orders page.

== Description ==

This plugin will re-add the "Purchased Items" column which was removed in WooCommerce 3.0. Order information is loaded via Ajax when the button is clicked. Saving resources on each page load.

After updating WooCommerce to version 3.0+, you may notice that the "Purchased Items" column is no longer in the orders list. The reason for removing this column was to save resources (each time the orders page is loaded, it was loading every order to create the query). To fix this issue, we created this plugin which queries the order data only when you click the "View Products" button.

The quantity and product name will be listed for an order when the button is clicked.

== Installation ==

1. Install the plugin.
2. You should now see a "Purchased Items" column in your WooCommerce orders list.

== Screenshots ==

1. "Purchased" Column in orders list

== Changelog ==

= 1.9.1 =
* Add [Show All](https://imgur.com/a/KnLRIH7) button to column.

= 1.8.2 =
* Display product attributes.

= 1.8.0 =
* Display extra info for variable products.

= 1.7.1 =
* Check if product exists before displaying.

= 1.6 =
* Remove "Show items" button after clicked.
* General code cleanup/optimise.
* Min require PHP 7.0.
* Compatibility with WooCommerce HPOS.

= 1.5 =
* Show product SKU when hovering over title.

= 1.4 =
* Add Nonce check to Ajax function.

= 1.3 =
* Move JS to footer.

= 1.2 =
* Use wc_get_order to get $order.
* Make "Show items" text translatable.

= 1.1 =
* Display quantity for each product.

= 1.0 = 
* initial Release