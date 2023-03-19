<?php
/**
 * The main Container class!
 *
 * This class is a basic implementation of a Container. It is inspired by PSR-11
 * Container Interface but with only the things stripped down with a bare
 * minimum feature for maximum compatibility within the WordPress ecosystem!
 *
 * This Container is written as a stand-alone "dwtfyw" class for WordPress.
 *
 * If you're a plugin developer, just copy and paste this class and include it
 * in your bootstrap file or whatever. Just remember to rename the
 * namespace to avoid conflicts or name collisions with other code.
 *
 * Inspired from various PSR-11 implementations.
 *
 * @package DIC\WP
 */

namespace DIC\WP;

/**
 * Feel free to modify this file especially the namespace above ☝☝☝ for your own needs.
 *
 * @version 0.0.1
 */
class Container {

	/**
	 * The fully qualified class names entries.
	 *
	 * @var array $entries
	 **/
	private $entries = array();

	/**
	 * Retrieve a specific instance of the class from the Container's entries.
	 *
	 * @param string $class_name The fully qualified class name of the an object.
	 *
	 * @return mixed The specific instance of the class found from the entries.
	 */
	public function get( $class_name = '' ) {

		$cache_entries = wp_cache_get( self::class, self::class . '_group' );

		if ( ! empty( $cache_entries[ $class_name ] ) ) {
			return $cache_entries[ $class_name ]( $this );
		}

		if ( $this->has( $class_name ) ) {
			return $this->entries[ $class_name ]( $this );
		}

		return $this->resolve( $class_name );

	}

	/**
	 * Determine if the container entries contains the specific class name.
	 *
	 * @param string $class_name The fully qualified class name of the an object.
	 *
	 * @return bool True if the class already exists. Returns false, otherwise.
	 */
	public function has( $class_name = '' ) {

		return isset( $this->entries[ $class_name ] );

	}

	/**
	 * Sets a specific class dependency from the return value of the second callable parameter.
	 *
	 * @param string   $class_name The class name to resolve.
	 * @param callable $func The return function to call when resolving the class.
	 *
	 * @throws \Exception When an invalid parameter is passed.
	 *
	 * @return self
	 */
	public function set( $class_name = '', callable $func = null ) {

		if ( empty( $class_name ) ) {
			throw new \Exception( 'Class name cannot be empty.' );
		}

		if ( empty( $func ) || ! is_callable( $func ) ) {
			throw new \Exception( 'The second parameter $func must be callable type' );
		}

		$this->entries[ $class_name ] = $func;

		wp_cache_set( self::class, $this->entries, self::class . '_group' );

		return $this;

	}

	/**
	 * Sets specific class dependencies using array.
	 *
	 * @param array $definitions E.g. [ MyClass::class => fn(), ... ].
	 *
	 * @return self;
	 */
	public function set_definitions( $definitions = array() ) {

		foreach ( $definitions as $class => $callback ) {
			$this->set( $class, $callback );
		}

		return $this;

	}

	/**
	 * Recursively resolves constructor arguments from dependencies.
	 *
	 * @throws \Exception When the container could not resolve any depedency.
	 *
	 * @param string $class_name The fully qualified class name.
	 */
	public function resolve( $class_name = '' ) {

		$reflection_class = new \ReflectionClass( $class_name );

		$constructor = $reflection_class->getConstructor();

		if ( ! $constructor ) {
			return new $class_name();
		}

		if ( ! $constructor->isPublic() ) {
			throw new \Exception( 'Unsupported non-public constructor method for the class ' . $class_name . ' ', 500 );
		}

		$parameters = $constructor->getParameters();

		if ( ! $parameters ) {
			return new $class_name();
		}

		$dependencies = array_map( array( $this, $this->get_resolver() ), $parameters );

		return $reflection_class->newInstanceArgs( $dependencies );

	}

	/**
	 * Retrieves the resolver based on the version of the PHP installed.
	 *
	 * @return string The resolver method.
	 */
	protected function get_resolver() {

		if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
			return 'php_7_resolver';
		}

		return 'php_5_resolver';

	}


	/**
	 * PHP 5 resolver.
	 *
	 * @param object $param The parameters, as a ReflectionParameter objects.
	 *
	 * @throws \Exception When an error has occured.

	 * @return mixed The instance of the class from the entries.
	 */
	protected function php_5_resolver( $param ) {

		if ( empty( $param->getClass() ) ) {
			throw new \Exception( 'Cannot resolve constructor parameter with no type hint $' . $param->getName(), 500 );
		}

		return $this->get( $param->getClass()->getName() );

	}

	/**
	 * PHP 7 resolver.
	 *
	 * @param object $param — The parameters, as a ReflectionParameter objects.
	 * 
	 * @throws \Exception - When an error has occured.
	 * 
	 * @return mixed The instance of the class from the entries.
	 */
	protected function php_7_resolver( $param ) {

		$type = $param->getType();

		if ( ! $type ) {
			throw new \Exception( 'Cannot resolve constructor dependencies from ' . $param->getClass()->getName(), 500 );
		}

		if ( class_exists( '\ReflectionUnionType' ) && $type instanceof \ReflectionUnionType ) {
			throw new \Exception( 'Cannot resolve constructor dependencies of union types from ' . $param->getClass()->getName(), 500 );
		}

		if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
			return $this->get( $type->getName() );
		}

		throw new \Exception( 'Cannot resolve dependencies. Unknown error has occured.', 500 );

	}
}
