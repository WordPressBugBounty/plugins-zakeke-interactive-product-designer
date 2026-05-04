<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zakeke_Designer Class.
 */
class Zakeke_Designer {

	/**
	 * Setup class.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 20 );
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_shortcode( 'zakeke', __CLASS__ . '::output' );
		if ( ! self::should_show_designer() ) {
			return;
		}

		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 20 );
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ), 1100001 );
	}

	private static function should_show_designer() {
		return (
			( ( ! empty( $_REQUEST['zdesign'] ) && 'new' === $_REQUEST['zdesign'] )
			  || ( ! empty( $_REQUEST['zdesign_edit'] ) ) )
			&& ! isset( $_REQUEST['tc_cart_edit_key'] )
		);
	}

	public static function register_scripts() {
		wp_register_style( 'zakeke-designer', get_zakeke()->plugin_url() . '/assets/css/frontend/designer.css',
			array(), ZAKEKE_VERSION );
		wp_register_style( 'zakeke-designer-accessibility', get_zakeke()->plugin_url() . '/assets/css/frontend/designer-accessibility.css',
			array(), ZAKEKE_VERSION );
		wp_register_style( 'zakeke-designer-from-shortcode', get_zakeke()->plugin_url() . '/assets/css/frontend/designer-from-shortcode.css',
			array(), ZAKEKE_VERSION );

		wp_register_script(
			'zakeke-designer',
			apply_filters( 'zakeke_javascript_designer', get_zakeke()->plugin_url() . '/assets/js/frontend/designer.js' ),
			array( 'jquery' ),
			ZAKEKE_VERSION
		);
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( class_exists( 'WP_Block_Type_Registry' ) && WP_Block_Type_Registry::get_instance()->is_registered( 'zakeke/designer' ) ) {
			return;
		}

		register_block_type( 'zakeke/designer', array(
			'render_callback' => array( __CLASS__, 'render_block' ),
		) );
	}

	public static function render_block( $attributes = array(), $content = '', $block = null ) {
		if ( ! self::should_show_designer() ) {
			return '';
		}

		ob_start();
		zakeke_render_designer_content();

		return ob_get_clean();
	}

	/**
	 * Enqueue Zakeke designer css and js code
	 *
	 * @param bool $from_shortcode
	 *
	 * @return void
	 */
	public static function enqueue_scripts($from_shortcode = false) {
		if ($from_shortcode) {
			wp_enqueue_style( 'zakeke-designer-from-shortcode' );
		} else {
			$integration = new Zakeke_Integration();
			if ( 'yes' === $integration->accessibility_mode ) {
				wp_enqueue_style( 'zakeke-designer-accessibility' );
			} else {
				wp_enqueue_style( 'zakeke-designer' );
			}
		}
		wp_enqueue_script( 'zakeke-designer' );
	}

	/**
	 * Load the Zakeke designer template.
	 *
	 * @param mixed $template
	 *
	 * @return string
	 */
	public static function template_loader( $template ) {
		return zakeke_template_loader( 'zakeke.php' );
	}

	/**
	 * Load the Zakeke configurator template.
	 *
	 * @return string
	 */
	private static function template_loader_shortcode() {
		$file     = 'zakeke-designer.php';
		$template = locate_template( $file );
		if ( ! $template ) {
			$template = get_zakeke()->plugin_path() . '/templates/' . $file;
		}

		return $template;
	}

	public static function output( $atts = array() ) {
		if ( '' == $atts ) {
			$atts = array();
		}

		if ( isset( $atts['product_id'] ) ) {
			$product = wc_get_product( intval( $atts['product_id'] ) );
		} else {
			$product = wc_get_product();
			if ($product !== false) {
				$atts['product_id'] = $product->get_id();
			}
		}

		if ( ! $product ) {
			return '<-- Zakeke: product not found --!>';
		}

		$template = null;
		if ( isset( $atts['template'] ) ) {
			$template = $atts['template'];
		}

		if ( isset( $atts['quantity'] ) ) {
			$_REQUEST['quantity'] = $atts['quantity'];
		}

		foreach ( $atts as $key => $value ) {
			if ( 'attribute_' === substr( $key, 0, 10 ) ) {
				$_REQUEST[ $key ] = sanitize_text_field( $value );
			}
		}

		self::enqueue_scripts(true);

		$final_atts             = $atts;
		$final_atts['product']  = $product;
		$final_atts['template'] = $template;

		ob_start();
		include self::template_loader_shortcode();

		return ob_get_clean();
	}
}

Zakeke_Designer::init();
