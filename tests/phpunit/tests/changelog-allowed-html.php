<?php

/**
 * Testcase for the edd_sl_free_changelog_allowed_html() function.
 *
 * @package EDD_SL_Free\Tests
 * @since 1.0.1
 */

/**
 * Tests for edd_sl_free_changelog_allowed_html().
 *
 * @since 1.0.1
 *
 * @covers ::edd_sl_free_changelog_allowed_html
 */
class EDD_SL_Free_Changelog_Allowed_HTML_Test extends WP_UnitTestCase {

	/**
	 * Test that the function returns an array of allowed tags.
	 *
	 * @since 1.0.1
	 */
	public function test_returns_array() {

		$this->assertInternalType(
			'array'
			, edd_sl_free_changelog_allowed_html(
				false
				, 'edd_sl_changelog'
			)
		);
	}

	/**
	 * Test that the function returns the first parameter if context isn't correct.
	 *
	 * @since 1.0.1
	 */
	public function test_returns_first_param() {

		$this->assertEquals(
			__METHOD__
			, edd_sl_free_changelog_allowed_html( __METHOD__, 'other' )
		);
	}

	/**
	 * Test that the function is hooked to the correct KSES filter.
	 *
	 * @since 1.0.1
	 */
	public function test_hooked_to_filter() {

		$message = '<p>Hello world!</p><script>alert("ha!");</script>';

		// Normally, the paragraph tags would be stripped.
		$this->assertEquals(
			'<p>Hello world!</p>alert("ha!");'
			, wp_kses( $message, 'edd_sl_changelog' )
		);
	}
}

// EOF
