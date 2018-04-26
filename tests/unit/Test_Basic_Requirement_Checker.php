<?php

class Test_Basic_Requirement_Checker extends PHPUnit\Framework\TestCase {
	const RANDOM_PLUGIN_FILE = 'file';

	const RANDOM_PLUGIN_NAME = 'name';

	const RANDOM_PLUGIN_TEXTDOMAIN = 'text';

	const ALWAYS_VALID_PHP_VERSION = '5.2';

	const ALWAYS_VALID_WP_VERSION = '4.0';

	public function setUp() {
		WP_Mock::setUp();

		WP_Mock::wpFunction( 'get_bloginfo' )
		       ->andReturn( self::ALWAYS_VALID_WP_VERSION );
	}

	public function tearDown() {
		WP_Mock::tearDown();
	}

	/**
	 * @param string $php
	 * @param string $wp
	 *
	 * @return WPDesk_Basic_Requirement_Checker
	 */
	public function create_requirements_for_php_wp( $php, $wp ) {
		return new WPDesk_Basic_Requirement_Checker( self::RANDOM_PLUGIN_FILE, self::RANDOM_PLUGIN_NAME,
			self::RANDOM_PLUGIN_TEXTDOMAIN, $php, $wp );
	}

	public function test_php_version_check() {
		$known_PHP_versions = [ '7.3', '7.2', '7.1', '7.0', '5.6', '5.5', '5.4', '5.3', '5.2' ];

		$requirements = $this->create_requirements_for_php_wp(
			self::ALWAYS_VALID_PHP_VERSION,
			self::ALWAYS_VALID_WP_VERSION );

		foreach ( $known_PHP_versions as $version ) {
			$requirements->set_min_php_require( $version );
			if ( version_compare( PHP_VERSION, $version, '>=' ) ) {
				$this->assertTrue( $requirements->are_requirements_met(),
					'Should be ok because WP is OK and PHP is OK' );
			} else {
				$this->assertFalse( $requirements->are_requirements_met(),
					'Should fail because required PHP should be at least  ' . $version );
			}

		}
	}

	public function test_wp_version_check() {
		$wp_version_fail = '4.1';

		$requirements = $this->create_requirements_for_php_wp(
			self::ALWAYS_VALID_PHP_VERSION,
			self::ALWAYS_VALID_WP_VERSION );

		$this->assertTrue( $requirements->are_requirements_met(), 'Should be ok because WP is OK and PHP is OK' );
		$requirements->set_min_wp_require( $wp_version_fail );
		$this->assertFalse( $requirements->are_requirements_met(),
			'Should fail because required WP should be at least ' . $wp_version_fail );
	}

	/**
	 * @requires extension curl
	 */
	public function test_module_check() {
		$requirements = $this->create_requirements_for_php_wp(
			self::ALWAYS_VALID_PHP_VERSION,
			self::ALWAYS_VALID_WP_VERSION );

		$requirements->add_php_module_require( 'curl' );
		$this->assertTrue( $requirements->are_requirements_met(), 'Curl should exists' );
	}

	public function test_plugin_check_with_multisite() {
		$multisite                     = true;
		$exising_plugin_name           = 'WooCommerce';
		$exising_multisite_plugin_name = 'Multisite';
		$not_existing_plugin_name      = 'Whatever';

		WP_Mock::wpFunction( 'get_option' )
		       ->withArgs( [ 'active_plugins', [] ] )
		       ->andReturn( [ $exising_plugin_name ] );

		WP_Mock::wpFunction( 'is_multisite' )
		       ->andReturn( $multisite );

		WP_Mock::wpFunction( 'get_site_option' )
		       ->withArgs( [ 'active_sitewide_plugins', [] ] )
		       ->andReturn( [ $exising_multisite_plugin_name ] );


		$requirements = $this->create_requirements_for_php_wp( self::ALWAYS_VALID_PHP_VERSION,
			self::ALWAYS_VALID_WP_VERSION );

		$requirements->add_plugin_require( $exising_plugin_name );
		$this->assertTrue( $requirements->are_requirements_met(), 'Plugin should exists' );

		$requirements->add_plugin_require( $exising_multisite_plugin_name );
		$this->assertTrue( $requirements->are_requirements_met(), 'Multisite plugin should exists' );

		$requirements->add_plugin_require( $not_existing_plugin_name );
		$this->assertFalse( $requirements->are_requirements_met(), 'Plugin should not exists' );
	}
}