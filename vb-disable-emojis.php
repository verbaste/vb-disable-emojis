<?php
/**
 * Plugin Name:       VB Disable Emojis
 * Plugin URI:        https://verbaste.com/
 * Description:       Completely disables WordPress emoji scripts and styles on frontend and backend. No settings, no UI, zero overhead.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            VerBaste
 * Author URI:        https://verbaste.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vb-disable-emojis
 *
 * VB Disable Emojis
 *
 * Short description:
 * Completely disables WordPress emoji scripts and styles on frontend and backend.
 *
 * Longer description:
 * Removes all emoji-related scripts, styles and editor integrations from WordPress.
 * No settings, no UI and no database writes. Activate to disable emojis, deactivate
 * to restore default behavior.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VB_Disable_Emojis' ) ) {

	/**
	 * Main plugin class.
	 */
	final class VB_Disable_Emojis {

		/**
		 * Boot.
		 *
		 * @return void
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'disable_emojis' ), 1 );
		}

		/**
		 * Disable all emoji-related functionality.
		 *
		 * @return void
		 */
		public static function disable_emojis() {
			// Frontend + backend scripts/styles.
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );

			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

			// Feeds and emails.
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

			// TinyMCE.
			add_filter( 'tiny_mce_plugins', array( __CLASS__, 'filter_tinymce_plugins' ) );

			// Remove from resource hints (DNS prefetch for s.w.org).
			add_filter( 'wp_resource_hints', array( __CLASS__, 'filter_resource_hints' ), 10, 2 );

			// Disable emoji CDN URL.
			add_filter( 'emoji_svg_url', '__return_false' );
		}

		/**
		 * Remove emoji plugin from TinyMCE plugins list.
		 *
		 * @param array $plugins TinyMCE plugins.
		 * @return array
		 */
		public static function filter_tinymce_plugins( $plugins ) {
			if ( ! is_array( $plugins ) ) {
				return array();
			}

			return array_diff( $plugins, array( 'wpemoji' ) );
		}

		/**
		 * Remove emoji CDN from prefetch hints.
		 *
		 * @param array  $urls          URLs to print for resource hints.
		 * @param string $relation_type Relation type (e.g. 'dns-prefetch').
		 * @return array
		 */
		public static function filter_resource_hints( $urls, $relation_type ) {
			if ( 'dns-prefetch' !== $relation_type ) {
				return $urls;
			}

			if ( ! is_array( $urls ) ) {
				return $urls;
			}

			return array_diff( $urls, array( 'https://s.w.org/images/core/emoji/' ) );
		}
	}

	VB_Disable_Emojis::init();
}
