<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zakeke support class for WooCommerce Product Bundles
 *
 * @package Zakeke/support
 */
class Zakeke_Support_Woocommerce_Product_Bundles {

	/**
	 * Hook in Zakeke support handlers.
	 */
	public static function init() {
		add_filter( 'zakeke_should_add_cart_item_data', array( __CLASS__, 'should_add_cart_item_data' ), 10, 2 );
	}

	/**
	 * Prevent Product Bundles child rows from receiving parent Zakeke request data.
	 *
	 * @param bool  $should_add Whether Zakeke should add cart item data.
	 * @param array $cart_item_meta Cart item data.
	 *
	 * @return bool
	 */
	public static function should_add_cart_item_data( $should_add, $cart_item_meta ) {
		if ( ! $should_add || ! function_exists( 'wc_pb_maybe_is_bundled_cart_item' ) ) {
			return $should_add;
		}

		try {
			if ( isset( $cart_item_meta['bundle_sell_id'] ) ) {
				return false;
			}

			return ! wc_pb_maybe_is_bundled_cart_item( $cart_item_meta );
		} catch ( Throwable $e ) {
			return true;
		}
	}
}

Zakeke_Support_Woocommerce_Product_Bundles::init();
