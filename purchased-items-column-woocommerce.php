<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Purchased Items Column WooCommerce
Plugin URI: https://wordpress.org/plugins/purchased-items-column-woocommerce/
Description: Display a "Purchased Items" column on the WooCommerce orders page.
Author: pipdig
Author URI: https://www.pipdig.co/
Version: 1.9.2
Requires Plugins: woocommerce
Text Domain: purchased-items-column-woocommerce
License: GPLv2 or later
*/

add_action('before_woocommerce_init', function() {
	if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
	}
});

// HPOS
add_filter('woocommerce_shop_order_list_table_columns', function ($columns) {
	
	$new_array = [];
	
	foreach ($columns as $key => $title) {
		if ($key == 'billing_address') {
			$new_array['order_items'] = __('Purchased', 'purchased-items-column-woocommerce').' <span>[<a href="#" id="pd_show_all_order_items">Show All</a>]</span>';
		}
		$new_array[$key] = $title;
	}
	
	return $new_array;
	
});

// HPOS
add_action('woocommerce_shop_order_list_table_custom_column', function ($column, $order) {
	
	if ($column == 'order_items') {
		
		$order_id = (int) $order->get_id();
		
		echo '<a href="#" class="show_order_items" data-wc-order="'.$order_id.'">'.__('Show items', 'purchased-items-column-woocommerce').'</a><div id="show_order_items_'.$order_id.'"></div>';
		
	}
	
}, 10, 2);

// CPT
add_filter('manage_edit-shop_order_columns', function($columns) {
	
	$new_array = [];
	
	foreach ($columns as $key => $title) {
		if ($key == 'billing_address') {
			$new_array['order_items'] = __('Purchased', 'purchased-items-column-woocommerce').' <span>[<a href="#" id="pd_show_all_order_items">'.__('Show all', 'purchased-items-column-woocommerce').'</a>]</span>';
		}
		$new_array[$key] = $title;
	}
	
	return $new_array;
	
});

// CPT
add_action('manage_shop_order_posts_custom_column', function($column) {
	
	if ($column == 'order_items') {
		
		$order_id = (int) get_the_ID();
		
		echo '<a href="#" class="show_order_items" data-wc-order="'.$order_id.'">'.__('Show items', 'purchased-items-column-woocommerce').'</a><div id="show_order_items_'.$order_id.'"></div>';
	}
	
}, 10, 2);


add_action('admin_footer', function() {
	
	if ( (isset($_GET['page']) && $_GET['page'] == 'wc-orders') || (isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order')) {
		
		?>
		<script>
		jQuery(document).ready(function($) {
			
			$('.show_order_items').click(function(e) {
				
				e.preventDefault();
				
				let thisBtn = $(this);
				
				let order_id = thisBtn.data('wc-order');
				
				thisBtn.hide();
				
				$('#show_order_items_'+order_id).html('<?php echo sanitize_text_field(__('Loading items...', 'purchased-items-column-woocommerce')); ?>');
				
				let data = {
					'action': 'pipdig_wc_find_products_ajax',
					'sec': <?php echo "'".wp_create_nonce('pipdig_wc_find_products_nonce')."'"; ?>,
					'order_id': order_id
				};
				
				$.post(ajaxurl, data, function(response) {
					
					$('#show_order_items_'+order_id).html(response);
					
				});
				
			});
			
			$('#pd_show_all_order_items').click(function(e) {
				
				e.preventDefault();
				
				let thisBtn = $(this);
				
				thisBtn.closest('span').hide();
				
				let delay = 0;

				$('.show_order_items').each(function(index, item) {
					
					setTimeout(function() {
						item.click();
					}, delay);
					
					delay += 250;
					
				});
				
			});
			
		});
		</script>
		<?php
	}
	
}, 999999);


add_action('wp_ajax_pipdig_wc_find_products_ajax', function() {
	
	check_ajax_referer('pipdig_wc_find_products_nonce', 'sec');
	
	if (!function_exists('wc_get_order')) {
		return;
	}
	
	$output = '';
	
	$order_id = (int) $_POST['order_id'];
	
	$order = wc_get_order($order_id);
	
	if (!$order) {
		wp_die();
	}
	
	// https://github.com/woocommerce/woocommerce/blob/18067472549797a0bcc698ffe96371028eaf9bca/plugins/woocommerce/includes/admin/list-tables/class-wc-admin-list-table-orders.php#L237
	/*
	$hidden_order_itemmeta = apply_filters(
		'woocommerce_hidden_order_itemmeta',
		array(
			'_qty',
			'_tax_class',
			'_product_id',
			'_variation_id',
			'_line_subtotal',
			'_line_subtotal_tax',
			'_line_total',
			'_line_tax',
			'method_id',
			'cost',
			'_reduced_stock',
			'_restock_refunded_items',
			'stock_status', // added
		)
	);
	*/
	
	foreach ($order->get_items() as $item) {
		
		$product = $item->get_product();
		
		$sku_info = '';
		$meta_markup = '';
		
		if ($product) {
			
			$sku = $product->get_sku();
			
			if ($sku) {
				$sku_info = ' ('.esc_html($sku).')';
			}
			
			foreach ($item->get_meta_data() as $meta_data) {
				
				$meta_data_as_array = $meta_data->get_data();
				
				/*
				if (in_array($meta_data_as_array['key'], $hidden_order_itemmeta, true)) {
					continue;
				}
				*/
				
				// attributes with "pa_" prefix are manually created by user in Products > Attributes page
				if (substr($meta_data_as_array['key'], 0, 3) !== 'pa_') {
					continue;
				}
				
				$value = $meta_data_as_array['value'];
				$attribute = $meta_data_as_array['key'];
				
				$attribute_name = wc_attribute_label($attribute, $product);
				
				$name = $value;
				
				$term = get_term_by('slug', $value, $attribute);
				
				if ($term) {
					$name = $term->name;
				}
				
				$meta_markup = '<br>'.esc_html($attribute_name).': '.esc_html($name);
				
			}
			
		}
		
		$quantity = (int) $item['quantity'];
		$product_name = esc_html($item['name']);
		
		$output .= $quantity.' &times; '.$product_name.$sku_info.$meta_markup.'<br /><br />';
		
	}
	
	echo $output;
	die;
	
});