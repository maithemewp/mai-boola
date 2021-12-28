<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mai_Boola_Register {
	/**
	 * Mai_Taboola_Register constructor.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function __construct() {
		$this->hooks();
	}

	function hooks() {
		add_filter( 'mai_template-parts_config', [ $this, 'add_content_area' ] );
	}

	/**
	 *
	 * @param array $config The existing content area data.
	 *
	 * @return array
	 */
	function add_content_area( $config ) {
		$config['mai-boola'] = [
			'hook'       => 'mai_after_entry_content_inner',
			'priority'   => 10,
			'menu_order' => 120,
			'before'     => $this->get_before(),
			'after'      => '</div>',
			'condition'  => function() {
				// TODO: Add post_type setting.
				$post_types = [ 'post' ];
				return is_singular( $post_types );
			},
		];

		return $config;
	}

	function get_before() {
		$html = sprintf( '<div class="maiboola%s">', maiboola_get_field( 'maiboola_excerpt' ) ? ' maiboola-excerpt' : '' );

		if ( maiboola_is_excerpt() ) {
			// CSS.
			$href  = MAI_BOOLA_PLUGIN_URL . 'assets/css/mai-boola.css';
			$html .= sprintf( '<link rel="stylesheet" href="%s" />', $href );

			// Toggle button.
			// $html .= sprintf( '<label for="maiboola-toggle">'<input type="checkbox" name="maiboola-toggle" />%s</label>', __( 'Continue Reading', 'mai-boola' ) );
			$html .= sprintf( '<input type="checkbox" name="maiboola-toggle" data-label="%s" />', __( 'Continue Reading', 'mai-boola' ) );
			// $html .= sprintf( '<label for="maiboola-toggle">%s</label>', __( 'Continue Reading', 'mai-boola' ) );
		}

		return $html;
	}
}
