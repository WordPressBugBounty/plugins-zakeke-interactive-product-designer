<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zakeke support class for tier-pricing-table-premium
 *
 * @package Zakeke/support
 */
class Zakeke_Support_tier_pricing_table_premium {
	/**
	 * Hook in Zakeke support handlers.
	 */
	public static function init() {
		add_action( 'tiered_pricing_table/cart/product_cart_price', array( __CLASS__, 'price' ), 10, 2 );
		add_action( 'tiered_pricing_table/cart/product_cart_price/item', array( __CLASS__, 'price' ), 10, 2 );

		add_filter( 'tiered_pricing_table/cart/product_cart_regular_price/item', array( __CLASS__, 'original_price' ), 20, 2 );
		add_filter( 'tiered_pricing_table/cart/product_cart_old_price', array( __CLASS__, 'original_price' ), 20, 2 );
	}

	/**
	 * Add the Zakeke pricing rules to the price
	 */
	public static function price( $price, $cart_item ) {
		if ( ! isset( $cart_item['zakeke_data'] ) ) {
			return $price;
		}

		$zakeke_data = $cart_item['zakeke_data'];
		$qty         = $cart_item['quantity'];
		$price       = self::get_price( $price, $cart_item, $zakeke_data );

		if ( false === $price ) {
			return $price;
		}

		$zakeke_price = zakeke_calculate_price(
			$zakeke_data['original_final_price'],
			$zakeke_data['pricing'],
			$qty
		);

		return $price + $zakeke_price;
	}

	/**
	 * Add the Zakeke pricing rules to the original product price.
	 *
	 * @param mixed $price Incoming price.
	 * @param array $cart_item Cart item.
	 *
	 * @return mixed
	 */
	public static function original_price( $price, $cart_item ) {
		if ( ! isset( $cart_item['zakeke_data'] ) ) {
			return $price;
		}

		$zakeke_data    = $cart_item['zakeke_data'];
		$qty            = $cart_item['quantity'];
		$original_price = self::get_original_product_price( $cart_item, $zakeke_data );

		if ( false === $original_price ) {
			return $price;
		}

		$zakeke_price = zakeke_calculate_price(
			$original_price,
			$zakeke_data['pricing'],
			$qty
		);

		return $original_price + $zakeke_price;
	}

	/**
	 * Get the base price to use before adding Zakeke pricing rules.
	 *
	 * @param mixed $price Incoming price.
	 * @param array $cart_item Cart item.
	 * @param array $zakeke_data Zakeke cart item data.
	 *
	 * @return mixed
	 */
	private static function get_price( $price, $cart_item, $zakeke_data ) {
		if ( false !== $price ) {
			return $price;
		}

		return self::get_original_product_price( $cart_item, $zakeke_data );
	}

	/**
	 * Get the original product price before tier or Zakeke pricing rules.
	 *
	 * @param array $cart_item Cart item.
	 * @param array $zakeke_data Zakeke cart item data.
	 *
	 * @return mixed
	 */
	private static function get_original_product_price( $cart_item, $zakeke_data ) {
		if ( isset( $zakeke_data['original_final_price'] ) ) {
			return $zakeke_data['original_final_price'];
		}

		$product_id = isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] > 0
			? $cart_item['variation_id']
			: ( isset( $cart_item['product_id'] ) ? $cart_item['product_id'] : 0 );

		if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
			return false;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;
		}

		return $product->get_price();
	}

}

Zakeke_Support_tier_pricing_table_premium::init();
