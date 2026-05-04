<?php
/**
 * The Template for displaying the Zakeke designer
 *
 * This template can be overridden by copying it to yourtheme/zakeke.php.
 *
 * HOWEVER, on occasion Zakeke will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );

while ( have_posts() ) {
	the_post();
	zakeke_render_designer_content();
}

get_footer( 'shop' );
