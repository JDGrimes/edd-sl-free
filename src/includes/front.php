<?php

/**
 * Front-end only code.
 *
 * @package EDD_SL_Free
 * @since 1.0.0
 */

/**
 * Filter the download forms for free downloads.
 *
 * @since 1.0.0
 */
function edd_sl_free_download_form_filter( $form, $args ) {

	if ( edd_is_free_download( $args['download_id'] ) ) {

		ob_start();

		?>

		<div class="edd_download_purchase_form">
			<a href="<?php echo esc_attr( esc_url( edd_sl_free_get_download_package_url( $args['download_id'] ) ) ); ?>" class="edd-sl-free-download <?php echo esc_attr( implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ) ); ?>">
				<span class="edd-add-to-cart-label">
					<?php esc_html_e( 'Download', 'edd-sl-free' ); ?>
				</span>
			</a>
		</div>

		<?php

		$form = ob_get_clean();
	}

	return $form;
}
add_filter( 'edd_purchase_download_form', 'edd_sl_free_download_form_filter', 10, 2 );

/**
 * API endpoint to get the latest version of a download.
 *
 * This API is very simple and closely matches the API provided by the Software
 * Licensing extension of the Easy Digital Downloads plugin. The difference is that
 * this API does not require that a license key be provided to validate the request.
 * That allows you to offer free software in much the same way that plugins are free
 * to download from WordPress.org—with absolutely no strings attached. This saves
 * people the extra work of having to obtain and maintain an API key.
 *
 * @since 1.0.0
 */
function edd_sl_free_get_latest_version( $data ) {

	if ( ! isset( $data['item_id'] ) ) {
		return;
	}

	$item_id  = (int) $data['item_id'];
	$download = edd_get_download( $item_id );

	// If this is a free item, we shortcircuit. This allows the requests for non-free
	// items to continue to work normally.
	if ( ! $download || ! edd_is_free_download( $item_id ) ) {
		return;
	}

	$slug        = ! empty( $data['slug'] ) ? sanitize_key( urldecode( $data['slug'] ) ) : $download->post_name;
	$description = ! empty( $download->post_excerpt ) ? $download->post_excerpt : $download->post_content;
	$changelog   = get_post_meta( $item_id, '_edd_sl_changelog', true );
	$beta        = ! empty( $data['beta'] );

	$stable_version = edd_software_licensing()->get_latest_version( $download->ID );

	$version = $stable_version;

	$download_beta = false;

	if (
		$beta
		&& (bool) get_post_meta( $item_id, '_edd_sl_beta_enabled', true )
	) {

		$version_beta = edd_software_licensing()->get_beta_download_version( $item_id );

		if ( version_compare( $version_beta, $stable_version, '>' ) ) {
			$changelog     = get_post_meta( $item_id, '_edd_sl_beta_changelog', true );
			$version       = $version_beta;
			$download_beta = true;
		}
	}

	$package_url = edd_sl_free_get_download_package_url( $item_id, $download_beta );

	$response = array(
		'new_version'    => $version,
		'stable_version' => $stable_version,
		'name'           => $download->post_title,
		'slug'           => $slug,
		'url'            => add_query_arg( 'changelog', '1', get_permalink( $item_id ) ),
		'last_updated'   => $download->post_modified,
		'homepage'       => get_permalink( $item_id ),
		'package'        => $package_url,
		'download_link'  => $package_url,
		'sections'       => serialize(
			array(
				'description' => wpautop( wp_kses( $description, 'edd_sl_changelog' ) ),
				'changelog'   => wpautop( wp_kses( stripslashes( $changelog ), 'edd_sl_changelog' ) ),
			)
		),
		'banners'        => serialize(
			array(
				'high' => get_post_meta( $item_id, '_edd_readme_plugin_banner_high', true ),
				'low'  => get_post_meta( $item_id, '_edd_readme_plugin_banner_low', true ),
			)
		),
		'is_free'        => true,
	);

	/**
	 * Filter the response.
	 *
	 * @since 1.0.0
	 */
	$response = apply_filters( 'edd_sl_license_response', $response, $download, $download_beta );

	wp_send_json( $response );
}
add_action( 'edd_get_version', 'edd_sl_free_get_latest_version', 5 );

// EOF
