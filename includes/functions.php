<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets the content area post ID.
 *
 * @since 0.1.0
 *
 * @return int
 */
function maiboola_get_id() {
	$id = null;

	if ( ! is_null( $id ) ) {
		return $id;
	}

	if ( ! function_exists( 'mai_get_template_part_id' ) ) {
		$id = false;
		return $id;
	}

	$id = mai_get_template_part_id( 'mai-boola' );

	return $id;
}

function maiboola_is_excerpt() {
	$bool = null;

	if ( ! is_null( $bool ) ) {
		return $bool;
	}

	$id = maiboola_get_id();

	if ( ! $id ) {
		return false;
	}

	return (bool) maiboola_get_field( 'maiboola_excerpt' );
}

function maiboola_get_field( $key ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$id = maiboola_get_id();

	if ( ! $id ) {
		return null;
	}

	return get_field( $key, $id );
}
