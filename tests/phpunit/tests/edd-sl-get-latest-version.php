<?php

/**
 * A test case for edd_sl_free_get_latest_version().
 *
 * @package EDD_SL_Free\Tests
 * @since 1.0.0
 */

/**
 * Test edd_sl_free_get_latest_version().
 *
 * @since 1.0.0
 *
 * @covers ::edd_sl_free_get_latest_version
 */
class EDD_SL_Get_Latest_Version_Test extends WP_UnitTestCase {

	/**
	 * The response from a simulated request.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $response;

	/**
	 * Tear down after each test.
	 *
	 * @since 1.0.0
	 */
	public function tearDown() {

		remove_filter( 'wp_die_handler', array( $this, 'get_die_handler' ), 1 );

		parent::tearDown();
	}

	/**
	 * Create a download.
	 *
	 * @since 1.0.0
	 *
	 * @param array $post_args The values for the download post's attributes.
	 *
	 * @return int The ID of the download.
	 */
	protected function create_download( array $post_args = array() ) {

		$post_args['post_type'] = 'download';

		$download_id = $this->factory->post->create( $post_args );

		add_post_meta( $download_id, '_edd_sl_upgrade_file_key', 0 );
		add_post_meta(
			$download_id
			, 'edd_download_files'
			, array( array( 'file' => '/test/file.zip' ) )
		);

		add_post_meta( $download_id, '_edd_sl_beta_upgrade_file_key', 0 );
		add_post_meta(
			$download_id
			, '_edd_sl_beta_files'
			, array( array( 'file' => '/test/beta.zip' ) )
		);

		return $download_id;
	}

	/**
	 * Create a download and simulate a request for it.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data      The request POST data.
	 * @param array $post_args The values for the download post's attributes.
	 *
	 * @return array|string The "response".
	 */
	protected function create_download_request( array $data, array $post_args = array() ) {

		$data['item_id'] = $this->create_download( $post_args );

		return $this->simulate_request( $data );
	}

	/**
	 * Simulate a get version request.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The request POST data.
	 *
	 * @return array|string The "response".
	 */
	protected function simulate_request( array $data ) {

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		add_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ), 1, 1 );

		ini_set( 'implicit_flush', false );
		ob_start();

		edd_sl_free_get_latest_version( $data );

		if ( ! empty( $this->response ) ) {
			return json_decode( $this->response, true );
		}

		return $this->response;
	}

	/**
	 * Get the wp_die() handler.
	 *
	 * @since 1.0.0
	 */
	public function get_die_handler() {

		return array( $this, 'die_handler' );
	}

	/**
	 * Handle wp_die() calls.
	 *
	 * @since 1.0.0
	 */
	public function die_handler() {

		$this->response = ob_get_clean();
	}

	//
	// Tests.
	//

	/**
	 * Test that it does nothing if the item_id isn't set.
	 *
	 * @since 1.0.0
	 */
	public function test_no_item_id() {

		$this->assertNull( $this->simulate_request( array() ) );
	}

	/**
	 * Test that it does nothing if given an invalid item_id.
	 *
	 * @since 1.0.0
	 */
	public function test_invalid_item_id() {

		$this->assertNull( $this->simulate_request( array( 'item_id' => 34 ) ) );
	}

	/**
	 * Test that it does nothing if it isn't a free download.
	 *
	 * @since 1.0.0
	 */
	public function test_non_free_download() {

		$item_id = $this->create_download();
		add_post_meta( $item_id, 'edd_price', '5.00' );

		$this->assertNull(
			$this->simulate_request( array( 'item_id' => $item_id ) )
		);
	}

	/**
	 * Test that the slug defaults to the post name.
	 *
	 * @since 1.0.0
	 */
	public function test_slug_defaults_to_post_name() {

		$response = $this->create_download_request(
			array()
			, array( 'post_name' => 'test-post-name' )
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'slug', $response );
		$this->assertEquals( 'test-post-name', $response['slug'] );
	}

	/**
	 * Test that if a slug is passed in it is used.
	 *
	 * @since 1.0.0
	 */
	public function test_predefined_slug() {

		$response = $this->create_download_request(
			array( 'slug' => 'predefined-slug' )
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'slug', $response );
		$this->assertEquals( 'predefined-slug', $response['slug'] );
	}

	/**
	 * Test that the response contains a new version number.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_new_version() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'new_version', $response );
		$this->assertEquals( '2.0.0', $response['new_version'] );
	}

	/**
	 * Test that the response contains the stable version number.
	 *
	 * @since 1.1.0
	 */
	public function test_response_includes_stable_version() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'stable_version', $response );
		$this->assertEquals( '2.0.0', $response['stable_version'] );
	}

	/**
	 * Test that the response contains a new version number.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_new_version_beta() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );
		add_post_meta( $item_id, '_edd_sl_beta_version', '2.1.0-beta' );
		add_post_meta( $item_id, '_edd_sl_beta_enabled', true );

		$response = $this->simulate_request(
			array(
				'item_id' => $item_id,
				'beta'    => 1,
			)
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'new_version', $response );
		$this->assertEquals( '2.1.0-beta', $response['new_version'] );
	}

	/**
	 * Test that the response contains a new version number.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_new_version_beta_disabled() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );
		add_post_meta( $item_id, '_edd_sl_beta_version', '2.1.0-beta' );
		add_post_meta( $item_id, '_edd_sl_beta_enabled', false );

		$response = $this->simulate_request(
			array(
				'item_id' => $item_id,
				'beta'    => 1,
			)
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'new_version', $response );
		$this->assertEquals( '2.0.0', $response['new_version'] );
	}

	/**
	 * Test that the response contains a new version number.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_new_version_beta_not_requested() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );
		add_post_meta( $item_id, '_edd_sl_beta_version', '2.1.0-beta' );
		add_post_meta( $item_id, '_edd_sl_beta_enabled', true );

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'new_version', $response );
		$this->assertEquals( '2.0.0', $response['new_version'] );
	}

	/**
	 * Test that the response contains a new version number.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_new_version_beta_older() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );
		add_post_meta( $item_id, '_edd_sl_beta_version', '2.0.0-beta' );
		add_post_meta( $item_id, '_edd_sl_beta_enabled', true );

		$response = $this->simulate_request(
			array(
				'item_id' => $item_id,
				'beta'    => 1,
			)
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'new_version', $response );
		$this->assertEquals( '2.0.0', $response['new_version'] );
	}

	/**
	 * Test that the response contains the stable version number.
	 *
	 * @since 1.1.0
	 */
	public function test_response_includes_stable_version_beta() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );
		add_post_meta( $item_id, '_edd_sl_beta_version', '2.1.0-beta' );
		add_post_meta( $item_id, '_edd_sl_beta_enabled', true );

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'stable_version', $response );
		$this->assertEquals( '2.0.0', $response['stable_version'] );
	}

	/**
	 * Test that the response includes the download name.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_download_name() {

		$response = $this->create_download_request(
			array()
			, array( 'post_title' => 'Download Name' )
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'name', $response );
		$this->assertEquals( 'Download Name', $response['name'] );
	}

	/**
	 * Test that the response contains the changelog URL.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_changelog_url() {

		$item_id = $this->create_download(
			array( 'post_title' => 'Download Name' )
		);

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'url', $response );
		$this->assertEquals(
			add_query_arg( 'changelog', '1', get_permalink( $item_id ) )
			, $response['url']
		);
	}

	/**
	 * Test that the response contains the last updated time.
	 *
	 * @since 1.1.0
	 */
	public function test_response_includes_last_updated_time() {

		$item_id  = $this->create_download();
		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'last_updated', $response );
		$this->assertEquals(
			get_post( $item_id )->post_modified
			, $response['last_updated']
		);
	}

	/**
	 * Test that the response contains the homepage URL.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_homepage_url() {

		$item_id  = $this->create_download();
		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'homepage', $response );
		$this->assertEquals( get_permalink( $item_id ), $response['homepage'] );
	}

	/**
	 * Test that the response contains the download URL.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_download_url() {

		$item_id  = $this->create_download();
		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$package_url = edd_sl_free_get_download_package_url( $item_id );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'package', $response );
		$this->assertEquals( $package_url, $response['package'] );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'download_link', $response );
		$this->assertEquals( $package_url, $response['download_link'] );
	}

	/**
	 * Test that the response contains the download URL.
	 *
	 * @since 1.1.0
	 */
	public function test_response_includes_download_url_beta() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );
		add_post_meta( $item_id, '_edd_sl_beta_version', '2.1.0-beta' );
		add_post_meta( $item_id, '_edd_sl_beta_enabled', true );

		$response = $this->simulate_request(
			array(
				'item_id' => $item_id,
				'beta'    => 1,
			)
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'package', $response );
		$this->assertEquals( '/test/beta.zip', $response['package'] );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'download_link', $response );
		$this->assertEquals( '/test/beta.zip', $response['download_link'] );
	}

	/**
	 * Test that the response contains the description and changelog sections.
	 *
	 * @since 1.0.0
	 */
	public function test_response_includes_sections() {

		$item_id = $this->create_download(
			array(
				'post_content' => 'Download description',
				'post_excerpt' => '',
			)
		);

		add_post_meta( $item_id, '_edd_sl_changelog', 'Download changelog' );

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'sections', $response );

		$sections = unserialize( $response['sections'] );

		$this->assertInternalType( 'array', $sections );
		$this->assertArrayHasKey( 'changelog', $sections );
		$this->assertEquals( "<p>Download changelog</p>\n", $sections['changelog'] );

		$this->assertInternalType( 'array', $sections );
		$this->assertArrayHasKey( 'description', $sections );
		$this->assertEquals(
			"<p>Download description</p>\n"
			, $sections['description']
		);
	}

	/**
	 * Test that the response contains the description and changelog sections.
	 *
	 * @since 1.1.0
	 */
	public function test_response_includes_sections_changelog_beta() {

		$item_id = $this->create_download(
			array(
				'post_content' => 'Download description',
				'post_excerpt' => '',
			)
		);

		add_post_meta( $item_id, '_edd_sl_version', '2.0.0' );
		add_post_meta( $item_id, '_edd_sl_beta_version', '2.1.0-beta' );
		add_post_meta( $item_id, '_edd_sl_beta_enabled', true );
		add_post_meta( $item_id, '_edd_sl_changelog', 'Download changelog' );
		add_post_meta( $item_id, '_edd_sl_beta_changelog', 'Download beta changelog' );

		$response = $this->simulate_request(
			array(
				'item_id' => $item_id,
				'beta'    => 1,
			)
		);

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'sections', $response );

		$sections = unserialize( $response['sections'] );

		$this->assertInternalType( 'array', $sections );
		$this->assertArrayHasKey( 'changelog', $sections );
		$this->assertEquals( "<p>Download beta changelog</p>\n", $sections['changelog'] );
	}

	/**
	 * Test that the description section uses the excerpt if available.
	 *
	 * @since 1.0.0
	 */
	public function test_description_uses_excerpt_if_available() {

		$item_id = $this->create_download(
			array( 'post_excerpt' => 'Download excerpt' )
		);

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'sections', $response );

		$sections = unserialize( $response['sections'] );

		$this->assertInternalType( 'array', $sections );
		$this->assertArrayHasKey( 'description', $sections );
		$this->assertEquals( "<p>Download excerpt</p>\n", $sections['description'] );
	}

	/**
	 * Test that the response contains the description and changelog sections.
	 *
	 * @since 1.1.0
	 */
	public function test_response_includes_banners() {

		$item_id = $this->create_download();

		add_post_meta( $item_id, '_edd_readme_plugin_banner_high', 'high.png' );
		add_post_meta( $item_id, '_edd_readme_plugin_banner_low', 'low.png' );

		$response = $this->simulate_request( array( 'item_id' => $item_id ) );

		$this->assertInternalType( 'array', $response );
		$this->assertArrayHasKey( 'banners', $response );

		$banners = unserialize( $response['banners'] );

		$this->assertInternalType( 'array', $banners );
		$this->assertArrayHasKey( 'high', $banners );
		$this->assertEquals( 'high.png', $banners['high'] );

		$this->assertInternalType( 'array', $banners );
		$this->assertArrayHasKey( 'low', $banners );
		$this->assertEquals( 'low.png', $banners['low'] );
	}
}

// EOF
