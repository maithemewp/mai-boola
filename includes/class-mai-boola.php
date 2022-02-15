<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mai_Boola {
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

	/**
	 * Runs hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'wp_head',                                    [ $this, 'header' ] );
		add_filter( 'genesis_attr_entry-content',                 [ $this, 'entry_content' ], 10, 3 );
		add_filter( 'mai_template-parts_config',                  [ $this, 'add_content_area' ] );
		add_action( 'mai_before_mai-boola_template_part_content', [ $this, 'do_before' ] );
		add_action( 'mai_after_mai-boola_template_part_content',  [ $this, 'do_after' ] );
		add_action( 'mai_before_mai-boola_template_part',         [ $this, 'show_ads' ] );
	}

	/**
	 * Adds header code.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function header() {
		if ( ! $this->should_run() ) {
			return;
		}

		$head = maiboola_get_field( 'maiboola_ad_head' );

		if ( ! $head ) {
			return;
		}

		echo $head;
	}

	/**
	 * Adds attributes to post entry content.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function entry_content( $attributes, $context, $args ) {
		if ( ! $this->should_run() ) {
			return $attributes;
		}

		if ( ! isset( $args['params']['args']['context'] ) || 'single' !== $args['params']['args']['context'] ) {
			return $attributes;
		}

		$conceal = $this->get_conceal();

		if ( $conceal ) {
			$attributes['class'] .= ' maiboola-content-hidden';

			if ( in_array( 'mobile', $conceal ) ) {
				$attributes['class'] .= ' maiboola-content-mobile';
			}

			if ( in_array( 'tablet', $conceal ) ) {
				$attributes['class'] .= ' maiboola-content-tablet';
			}

			if ( in_array( 'desktop', $conceal ) ) {
				$attributes['class'] .= ' maiboola-content-desktop';
			}


			$attributes['style']  = isset( $attributes['style'] ) ? $attributes['style'] : '';
			$attributes['style'] .= sprintf( ' --maiboola-content-max-height:%spx;', $this->get_excerpt_height() );
		}

		return $attributes;
	}

	/**
	 * Adds Content Area.
	 *
	 * @since 0.1.0
	 *
	 * @param array $config The existing content area data.
	 *
	 * @return array
	 */
	function add_content_area( $config ) {
		$config['mai-boola'] = [
			'hook'       => 'mai_after_entry_content',
			'priority'   => 12,
			'menu_order' => 120,
			'condition'  => function() {
				return $this->should_run();
			},
		];

		return $config;
	}

	/**
	 * Renders opening HTML.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function do_before() {
		$class   = 'maiboola';
		$style   = '';
		$conceal = $this->get_conceal();

		if ( $conceal ) {
			$class .= ' maiboola-conceal';

			if ( in_array( 'mobile', $conceal ) ) {
				$class .= ' maiboola-conceal-mobile';
			}

			if ( in_array( 'tablet', $conceal ) ) {
				$class .= ' maiboola-conceal-tablet';
			}

			if ( in_array( 'desktop', $conceal ) ) {
				$class .= ' maiboola-conceal-desktop';
			}

			$style .= sprintf( ' style="--maiboola-conceal-top:%spx;"', $this->get_excerpt_height() );
		}

		$html = sprintf( '<div id="maiboola" class="%s"%s>', $class, $style );

		if ( $conceal ) {
			// CSS.
			$href  = MAI_BOOLA_PLUGIN_URL . 'assets/css/mai-boola.css';
			$html .= sprintf( '<link rel="stylesheet" href="%s" />', $href );

			// Toggle button.
			$text  = maiboola_get_field( 'maiboola_toggle_text' );
			$text  = $text ?: __( 'Continue Reading', 'mai-boola' );
			$html .= sprintf( '<p class="maiboola-toggle-wrap"><button id="maiboola-toggle">%s</button></p>', $text );
		}

		echo $html;
	}

	/**
	 * Renders closing HTML.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function do_after() {
		$html = '';

		if ( $this->get_conceal() ) {
			// JS.
			$src   = MAI_BOOLA_PLUGIN_URL . 'assets/js/mai-boola.js';
			$html .= sprintf( '<script src="%s"></script>', $src );
		}

		$html .= '</div>';

		echo $html;
	}

	/**
	 * Runs ad hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
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

	/**
	 * Displays an ad before the opening markup.
	 *
	 * @since 0.1.0
	 *
	 * @param string $open The opening markup.
	 * @param array  $args The markup args.
	 *
	 * @return string
	 */
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

	/**
	 * Displays an ad after the opening markup.
	 *
	 * @since 0.1.0
	 *
	 * @param string $open The opening markup.
	 * @param array  $args The markup args.
	 *
	 * @return string
	 */
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

	/**
	 * Gets processed ad content.
	 *
	 * @since 0.1.0
	 *
	 * @param string $ad The ad HTML.
	 *
	 * @return string
	 */
	function get_ad( $ad ) {
		$ad = $this->get_processed_content( $ad );
		return sprintf( '<div class="entry maiboola-ad" style="justify-content:center;align-items:center;text-align:center;">%s</div>', $ad );
	}

	/**
	 * Gets processed content.
	 *
	 * @since 0.1.0
	 *
	 * @param string $content The content.
	 *
	 * @return string
	 */
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

	/**
	 * Gets the conceal breakpoints.
	 *
	 * @since 0.1.0
	 *
	 * @return int
	 */
	function get_conceal() {
		static $conceal = null;

		if ( ! is_null( $conceal ) ) {
			return $conceal;
		}

		$conceal = (array) maiboola_get_field( 'maiboola_conceal' );

		return $conceal;
	}

	/**
	 * Gets the conceal amount.
	 *
	 * @since 0.1.0
	 *
	 * @return int
	 */
	function get_excerpt_height() {
		static $amount = null;

		if ( ! is_null( $amount ) ) {
			return $amount;
		}

		$amount = maiboola_get_field( 'maiboola_excerpt_height' );
		$amount = $amount ?: 800;
		$amount = filter_var( $amount, FILTER_VALIDATE_INT );

		return $amount;
	}

	/**
	 * If the code should run on this page.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	function should_run() {
		static $should_run = null;

		if ( ! is_null( $should_run ) ) {
			return $should_run;
		}

		$post_types = maiboola_get_field( 'maiboola_post_types' );
		$should_run = $post_types ? is_singular( $post_types ) : false;

		return $should_run;
	}
}
