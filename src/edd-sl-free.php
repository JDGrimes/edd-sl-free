<?php

/**
 * Plugin Name: EDD SL Free
 * Plugin URI:  https://github.com/JDGrimes/edd-sl-free
 * Author:      J.D. Grimes
 * Author URI:  https://codesymphony.co/
 * Version:     1.1.0
 * Description: Easily provide free downloads through the Easy Digital Downloads plugin while still using the Software Licensing extension.
 *
 * @package EDD_SL_Free
 * @version 1.1.0
 * @author  J.D. Grimes <jdg@codesymphony.co>
 */

if ( ! is_admin() ) {

	/**
	 * Front-end only code.
	 *
	 * @since 1.0.0
	 */
	include_once( dirname( __FILE__ ) . '/includes/front.php' );
}

/**
 * Get the package URL for a download.
 *
 * @since 1.0.0
 * @since 1.1.0 The $download_beta arg was added.
 *
 * @param int  $download_id   The ID of the download.
 * @param bool $download_beta Whether to get the URL for the beta version.
 *
 * @return string The download package URL.
 */
function edd_sl_free_get_download_package_url( $download_id, $download_beta = false ) {

	if ( $download_beta ) {
		$file_key  = get_post_meta( $download_id, '_edd_sl_beta_upgrade_file_key', true );
		$all_files = get_post_meta( $download_id, '_edd_sl_beta_files', true );
	} else {
		$file_key  = get_post_meta( $download_id, '_edd_sl_upgrade_file_key', true );
		$all_files = get_post_meta( $download_id, 'edd_download_files', true );
	}

	if ( $all_files && is_array( $all_files ) ) {
		$file_url = $all_files[ $file_key ]['file'];
	} else {
		$file_url = '';
	}

	/**
	 * Filter the requested file.
	 *
	 * @since 1.0.0
	 */
	$file_url = apply_filters( 'edd_requested_file', $file_url, $all_files, $file_key );

	/**
	 * Filter the package URL.
	 *
	 * @since 1.0.0
	 */
	return apply_filters( 'edd_sl_download_package_url', $file_url, $download_id );
}

/**
 * Supply the list of HTML tags allowed in a module changelog.
 *
 * @since 1.0.1
 *
 * @WordPress\filter wp_kses_allowed_html
 */
function edd_sl_free_changelog_allowed_html( $allowed_tags, $context ) {

	if ( 'edd_sl_changelog' !== $context ) {
		return $allowed_tags;
	}

	return array(
		'a'       => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
		),
		'abbr'    => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code'    => array(),
		'pre'     => array(),
		'em'      => array(),
		'strong'  => array(),
		'div'     => array( 'class' => array() ),
		'span'    => array( 'class' => array() ),
		'p'       => array(),
		'ul'      => array(),
		'ol'      => array(),
		'li'      => array(),
		'h1'      => array(),
		'h2'      => array(),
		'h3'      => array(),
		'h4'      => array(),
		'h5'      => array(),
		'h6'      => array(),
		'img'     => array(
			'src'   => array(),
			'class' => array(),
			'alt'   => array(),
		),
	);
}
add_filter( 'wp_kses_allowed_html', 'edd_sl_free_changelog_allowed_html', 10, 2 );

// EOF
