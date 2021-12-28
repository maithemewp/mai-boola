<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mai_Boola_Fields {
	/**
	 * Mai_Boola_Fields constructor.
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
		add_action( 'acf/init',                               [ $this, 'register_field_group' ] );
		add_filter( 'acf/load_field/key=maiboola_post_types', [ $this, 'load_post_types' ] );
	}

	/**
	 * Register field group.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_field_group() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			[
				'key'    => 'maiboola_field_group',
				'title'  => __( 'Mai Boola', 'mai-boola' ),
				'fields' => [
					[
						'key'           => 'maiboola_post_types',
						'label'         => __( 'Post types', 'mai-boola' ),
						'name'          => 'maiboola_post_types',
						'type'          => 'select',
						'instructions'  => '',
						'required'      => 1,
						'choices'       => [],
						'default_value' => [ 'post' ],
						'allow_null'    => 0,
						'multiple'      => 1,
						'ui'            => 1,
						'ajax'          => 1,
					],
					[
						'key'          => 'maiboola_conceal',
						'label'        => __( 'Conceal content', 'mai-boola' ),
						'name'         => 'maiboola_conceal',
						'type'         => 'checkbox',
						'instructions' => __( 'Conceal the full article on various screen sizes.', 'mai-boola' ),
						'choices'      => [
							'mobile'  => __( 'Mobile', 'mai-boola' ),
							'tablet'  => __( 'Tablet', 'mai-boola' ),
							'desktop' => __( 'Desktop', 'mai-boola' ),
						],
					],
					[
						'key'               => 'maiboola_excerpt_height',
						'label'             => __( 'Excerpt height', 'mai-boola' ),
						'name'              => 'maiboola_excerpt_height',
						'type'              => 'number',
						'instructions'      => __( 'Amount of post content to display (in pixels] before showing this content.', 'mai-boola' ),
						'placeholder'       => 800,
						'append'            => 'px',
						'conditional_logic' => [
							[
								[
									'field'    => 'maiboola_conceal',
									'operator' => '!=empty',
								],
							],
						],
					],
					[
						'key'               => 'maiboola_toggle_text',
						'label'             => __( 'Toggle text', 'mai-boola' ),
						'name'              => 'maiboola_toggle_text',
						'type'              => 'text',
						'placeholder'       => __( 'Continue Reading', 'mai-boola' ),
						'conditional_logic' => [
							[
								[
									'field'    => 'maiboola_conceal',
									'operator' => '!=empty',
								],
							],
						],
					],
					[
						'key'          => 'maiboola_ad_head',
						'label'        => __( 'Header', 'mai-boola' ),
						'name'         => 'maiboola_ad_head',
						'type'         => 'textarea',
						'instructions' => __( 'Any code to display in the post header.', 'mai-boola' ),
					],
					[
						'key'          => 'maiboola_ads',
						'label'        => __( 'Ads', 'mai-boola' ),
						'name'         => 'maiboola_ads',
						'type'         => 'repeater',
						'instructions' => __( 'Show ads between Mai Post Grid block. Will not display unless Mai Post Grid is added to the editor of this Content Area.', 'mai-boola' ),
						'collapsed'    => 'maiboola_skip',
						'layout'       => 'row',
						'button_label' => __( 'Create New Ad', 'mai-boola' ),
						'sub_fields'   => [
							[
								'key'          => 'maiboola_skip',
								'label'        => __( 'Skip', 'mai-boola' ),
								'name'         => 'skip',
								'type'         => 'number',
								'instructions' => __( 'The amount of Mai Post Grid entries to show before this ad.', 'mai-boola' ),
								'min'          => 0,
							],
							[
								'key'          => 'maiboola_ad',
								'label'        => __( 'Ad', 'mai-boola' ),
								'name'         => 'ad',
								'type'         => 'textarea',
								'rows'         => 4,
							],
						],
					],
				],
				'location' => $this->get_location(),
			]
		);
	}

	/**
	 * Gets location for field group.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function get_location() {
		$location = [];
		$post_id  = maiboola_get_id();

		if ( $post_id ) {
			$location = [
				[
					[
						'param'    => 'post',
						'operator' => '==',
						'value'    => $post_id,
					],
				],
			];
		}

		return $location;
	}

	/**
	 * Loads post types.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field args.
	 *
	 * @return array
	 */
	function load_post_types( $field ) {
		$field['choices'] = get_post_types( [ 'public' => true ] );

		return $field;
	}
}
