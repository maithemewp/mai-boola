<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mai_Boola_Display {
	var $ads = null;

	/**
	 * Mai_Taboola_Display constructor.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function __construct() {
		$this->hooks();
	}

	function hooks() {
		add_action( 'wp_head', [ $this, 'header' ] );
		add_action( 'mai_before_mai-boola_template_part', [ $this, 'show_ads' ] );
	}

	function header() {
		$head = maiboola_get_field( 'maiboola_ad_head' );

		if ( ! $head ) {
			return;
		}

		echo $head;
	}

	function show_ads() {
		$this->ads = maiboola_get_field( 'maiboola_ads' );

		if ( ! $this->ads ) {
			return;
		}

		// Display ad.
		add_filter( 'genesis_markup_entry_open', [ $this, 'show_ad_before' ], 10, 2 );
		add_filter( 'genesis_markup_entry_close', [ $this, 'show_ad_after' ], 10, 2 );

		// Remove action so it doesn't apply to blocks after this content area.
		add_action( 'mai_after_mai-boola_template_part', function() {
			remove_filter( 'genesis_markup_entry_open', [ $this, 'show_ad_before' ], 10, 2 );
			remove_filter( 'genesis_markup_entry_close', [ $this, 'show_ad_after' ], 10, 2 );
		});
	}

	function show_ad_before( $open, $args ) {
		if ( ! $open ) {
			return $open;
		}

		static $count = 0;

		if ( 0 !== $count ) {
			return $open;
		}

		foreach ( $this->ads as $ad ) {
			if ( (int) $count !== (int) $ad['skip'] ) {
				continue;
			}

			$open = $this->get_ad( $ad['ad'] ) . $open;
		}

		$count++;

		return $open;
	}

	function show_ad_after( $close, $args ) {
		if ( ! $close ) {
			return $close;
		}

		static $count = 1;

		foreach ( $this->ads as $ad ) {
			if ( (int) $count !== (int) $ad['skip'] ) {
				continue;
			}

			$close .= $this->get_ad( $ad['ad'] );
		}

		$count++;

		return $close;
	}

	function get_ad( $ad ) {
		$ad = $this->get_processed_content( $ad );
		return sprintf( '<div class="entry maiboola-ad" style="justify-content:center;align-items:center;text-align:center;">%s</div>', $ad );
	}

	function get_processed_content( $content ) {
		if ( function_exists( 'mai_get_processed_content' ) ) {
			return mai_get_processed_content( $content );
		}

		/**
		 * Embed.
		 *
		 * @var WP_Embed $wp_embed Embed object.
		 */
		global $wp_embed;

		$blocks  = has_blocks( $content );
		$content = $wp_embed->autoembed( $content );           // WP runs priority 8.
		$content = $wp_embed->run_shortcode( $content );       // WP runs priority 8.
		$content = $blocks ? do_blocks( $content ) : $content; // WP runs priority 9.
		$content = wptexturize( $content );                    // WP runs priority 10.
		$content = ! $blocks ? wpautop( $content ) : $content; // WP runs priority 10.
		$content = shortcode_unautop( $content );              // WP runs priority 10.
		$content = function_exists( 'wp_filter_content_tags' ) ? wp_filter_content_tags( $content ) : wp_make_content_images_responsive( $content ); // WP runs priority 10. WP 5.5 with fallback.
		$content = do_shortcode( $content );                   // WP runs priority 11.
		$content = convert_smilies( $content );                // WP runs priority 20.
	}
}
