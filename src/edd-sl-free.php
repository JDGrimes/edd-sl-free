<?php

/**
 * Plugin Name: EDD SL Free
 * Plugin URI:  https://github.com/JDGrimes/edd-sl-free
 * Author:      J.D. Grimes
 * Author URI:  http://codesymphony.co/
 * Version:     1.0.0
 * Description: Easily provide free downloads through the Easy Digital Downloads plugin while still using the Software Licensing extension.
 *
 * @package EDD_SL_Free
 * @version 1.0.0
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
 *
 * @param int $download_id The ID of the download.
 *
 * @return string The download package URL.
 */
function edd_sl_free_get_download_package_url( $download_id ) {

	$file_key  = get_post_meta( $download_id, '_edd_sl_upgrade_file_key', true );
	$all_files = get_post_meta( $download_id, 'edd_download_files', true );

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

// EOF
