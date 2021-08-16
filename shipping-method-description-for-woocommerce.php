<?php
/**
 * Plugin Name: Shipping Method Description for WooCommerce
 * Plugin URI: https://github.com/thomascharbit/woocommerce-shipping-method-description
 * Description: Add a description to all WooCommerce shipping methods on cart and checkout pages.
 * Author: Thomas Charbit
 * Author URI: https://thomascharbit.fr
 * Version: 1.0.0
 * License: GPLv3 or later License
 * Requires at least: 4.4
 * Tested up to: 5.8
 * WC requires at least: 2.6
 * WC tested up to: 5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
function wcsmd_is_woocommerce_activated() {
	return class_exists( 'woocommerce' );
}

/**
 * Display admin notice if WooCommerce is not active
 */
add_action( 'admin_notices', 'wcsmd_requirement_notice' );
function wcsmd_requirement_notice() {
	if ( ! wcsmd_is_woocommerce_activated() ) {
		/* translators: %1$s: open link, %2$s: close link */
		$error   = sprintf( __( 'WooCommerce Shipping Method Description requires %1$sWooCommerce%2$s to be installed & activated.', 'woocommerce-shipping-method-description' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
		$message = '<div class="error"><p>' . $error . '</p></div>';
		echo $message;
	}
}

/**
 * Init plugin
 */
add_action( 'woocommerce_loaded', 'wcsmd_init' );
function wcsmd_init() {
	$shipping_methods = WC()->shipping->get_shipping_methods();

	foreach ( $shipping_methods as $id => $shipping_method ) {
		add_filter( "woocommerce_shipping_instance_form_fields_$id", 'wcsmd_add_form_fields' );
	}
}

/**
 * Add description field to shipping method form
 */
function wcsmd_add_form_fields( $fields ) {
	// Create description field
	$new_fields = array(
		'description' => array(
			'title' => __( 'Description', 'woocommerce-shipping-method-description' ),
			'type'  => 'textarea',
		),
	);
	// Insert it after title field
	$keys  = array_keys( $fields );
	$index = array_search( 'title', $keys, true );
	$pos   = false === $index ? count( $array ) : $index + 1;
	return array_merge( array_slice( $fields, 0, $pos ), $new_fields, array_slice( $fields, $pos ) );
}

/**
 * Load description as metadata
 */
add_filter( 'woocommerce_shipping_method_add_rate_args', 'wcsmd_add_rate_description_arg', 10, 2 );
function wcsmd_add_rate_description_arg( $args, $method ) {
	$args['meta_data']['description'] = $method->get_option( 'description' );
	return $args;
}

/**
 * Display description field after method label
 */
add_action( 'woocommerce_after_shipping_rate', 'wcsmd_output_shipping_rate_description', 10 );
function wcsmd_output_shipping_rate_description( $method ) {
	$meta_data = $method->get_meta_data();
	if ( array_key_exists( 'description', $meta_data ) ) {
		$html = '<div><small class="woocommerce-shipping-method-description">' . esc_html( $meta_data['description'] ) . '</small></div>';
		echo apply_filters( 'woocommerce_shipping_method_description_output_html', $html, $meta_data['description'] );
	}
}
