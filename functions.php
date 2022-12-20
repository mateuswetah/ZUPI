<?php
/**
 * Theme Name: ZUPI
 * Description: Tema do Acervo Zupi
 * Author: wetah
 * Template: blocksy
 * Text Domain: zupi
 */

if (! defined('WP_DEBUG')) {
	die( 'Direct access forbidden.' );
}

/** Child Theme version */
const ZUPI_VERSION = '0.10.6';

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'blocksy-child-style', get_stylesheet_directory_uri() . '/style.min.css', ZUPI_VERSION );
});