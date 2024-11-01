<?php
/*
 * Plugin Name: WooCommerce Price From in Variable Products
 * Plugin URI: https://plugins.webdig.pt
 * Description: Show only the lowest prices in WooCommerce variable products and lowest sale price
 * Author: WebDig
 * Version: 1.0.2
 * Author URI: https://webdig.pt
 * Text Domain: woocommerce-price-from-in-variable-products
 * Domain Path: /languages
 * WC requires at least: 5.2
 * WC tested up to: 6.3.1
 * License: GPLv2+
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
function woocommerce_price_from_in_variable_products_load_plugin_textdomain() {
    load_plugin_textdomain( 'woocommerce-price-from-in-variable-products', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'woocommerce_price_from_in_variable_products_load_plugin_textdomain' );
//Simple products
function wc_variation_price_format( $price, $product ) {
    // Main prices
    $prices = array( $product->get_variation_price( 'min', true ), $product->get_variation_price( 'max', true ) );
    $price = $prices[0] !== $prices[1] ? sprintf( __( '<span class="pricefrom">From </span>%1$s', 'woocommerce-price-from-in-variable-products' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );
    // Sale price
    $prices = array( $product->get_variation_regular_price( 'min', true ), $product->get_variation_regular_price( 'max', true ) );
    sort( $prices );
    $saleprice = $prices[0] !== $prices[1] ? sprintf( __( '<span class="pricefrom">From </span>%1$s', 'woocommerce-price-from-in-variable-products' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );
    if ( $price !== $saleprice ) {
        $price = '<del>' . $saleprice . '</del> <ins>' . $price . '</ins>';
    }
    return $price;
}
add_filter( 'woocommerce_variable_sale_price_html', 'wc_variation_price_format', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'wc_variation_price_format', 10, 2 );

//Grouped products
// Show product prices in WooCommerce 2.0 format
add_filter( 'woocommerce_grouped_price_html', 'wc_grouped_price_format', 10, 2 );
function wc_grouped_price_format( $price, $product ) {
	$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
	$child_prices     = array();
	foreach ( $product->get_children() as $child_id ) {
		$child_prices[] = get_post_meta( $child_id, '_price', true );
	}
	$child_prices     = array_unique( $child_prices );
	$get_price_method = 'get_price_' . $tax_display_mode . 'uding_tax';
	if ( ! empty( $child_prices ) ) {
		$min_price = min( $child_prices );
		$max_price = max( $child_prices );
	} else {
		$min_price = '';
		$max_price = '';
	}
	if ( $min_price == $max_price ) {
		$display_price = wc_price( $product->$get_price_method( 1, $min_price ) );
	} else {
		$from          = wc_price( $product->$get_price_method( 1, $min_price ) );
		$display_price = sprintf( __( 'From %1$s', 'woocommerce-price-from-in-variable-products' ), $from );
	}
	return $display_price;
}