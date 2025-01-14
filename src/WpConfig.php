<?php

namespace LeWebSimple;

use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\PutenvAdapter;

/**
 * Load wp-config from .env file
 */
class WpConfig {

	/** @var string Path where .env files are located */
	private $path;

	/** @var string Current environment (development / production) */
	private $env;

	/**
	 * Constructor to initialize the path
	 *
	 * @param string $path Path where .env files are located.
	 */
	public function __construct( $path ) {
		$this->path = rtrim( $path, DIRECTORY_SEPARATOR );
		$this->env  = getenv( 'WP_ENV' ) ?: 'production';
	}

	/**
	 * Check if currently in production environment
	 *
	 * @return bool
	 */
	public function is_production() {
		return $this->env === 'production';
	}

	/**
	 * Load the .env file
	 *
	 * @return void
	 */
	public function load() {
		$repository = RepositoryBuilder::createWithNoAdapters()
			->addAdapter( PutenvAdapter::class )
			->immutable()
			->make();
		$dotenv     = Dotenv::create( $repository, $this->path, array( '.env', ".env.{$this->env}" ), false );
		$dotenv->load();
		$this->define_constants();
	}

	/**
	 * Define constants from .env file
	 *
	 * @return void
	 */
	private function define_constants() {
		// Environment settings
		$this->define( 'WP_ENV', $this->env );

		// Database settings
		$this->define( 'DB_NAME', getenv( 'DB_NAME' ) );
		$this->define( 'DB_USER', getenv( 'DB_USER' ) );
		$this->define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
		$this->define( 'DB_HOST', getenv( 'DB_HOST' ) ?: 'localhost' );
		$this->define( 'DB_CHARSET', getenv( 'DB_CHARSET' ) ?: 'utf8' );
		$this->define( 'DB_COLLATE', getenv( 'DB_COLLATE' ) ?: '' );

		// Disable CRON
		$this->define( 'DISABLE_WP_CRON', $this->boolean( getenv( 'DISABLE_WP_CRON' ) ?: false ) );

		// Allow WordPress to detect HTTPS when behind a reverse proxy or load balancer
		if ( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' === 'https' ) {
			$_SERVER['HTTPS'] = 'on';
		}

		// Site settings
		if ( $site_url = getenv( 'WP_SITEURL' ) ?: $_SERVER['HTTP_ORIGIN'] ?? false ) {
			$this->define( 'WP_SITEURL', $site_url );
		}
		getenv( 'WP_HOME' ) && $this->define( 'WP_HOME', getenv( 'WP_HOME' ) );

		// WP Offload SES
		$this->define( 'WPOSES_AWS_ACCESS_KEY_ID', getenv( 'WPOSES_AWS_ACCESS_KEY_ID' ) ?: '' );
		$this->define( 'WPOSES_AWS_SECRET_ACCESS_KEY', getenv( 'WPOSES_AWS_SECRET_ACCESS_KEY' ) ?: '' );
	}

	/**
	 * Safely define a constant if it isn't already defined.
	 *
	 * @param string $name  The name of the constant.
	 * @param mixed  $value The value of the constant.
	 */
	private function define( string $name, $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Convert value to a boolean.
	 *
	 * @param mixed $value The value to convert.
	 * @return bool
	 */
	private function boolean( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get the WordPress table prefix
	 *
	 * @return string
	 */
	public function get_table_prefix() {
		return getenv( 'TABLE_PREFIX' ) ?: 'wp_';
	}
}
