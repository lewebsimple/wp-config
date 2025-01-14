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

	/**
	 * Constructor to initialize the path
	 *
	 * @param string $path Path where .env files are located.
	 */
	public function __construct( $path ) {
		$this->path = rtrim( $path, DIRECTORY_SEPARATOR );
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
		$dotenv     = Dotenv::create( $repository, $this->path );
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
		$this->define( 'WP_ENV', getenv( 'WP_ENV' ) ?: 'production' );

		// Database settings
		$this->define( 'DB_NAME', getenv( 'DB_NAME' ) );
		$this->define( 'DB_USER', getenv( 'DB_USER' ) );
		$this->define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
		$this->define( 'DB_HOST', getenv( 'DB_HOST' ) ?: 'localhost' );
		$this->define( 'DB_CHARSET', getenv( 'DB_CHARSET' ) ?: 'utf8' );
		$this->define( 'DB_COLLATE', getenv( 'DB_COLLATE' ) ?: '' );
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
}
