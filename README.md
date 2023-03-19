
# WordPress Dependency Injection Container

This class is a basic implementation of a Container. It is inspired by PSR-11 Container Interface but stripped down with bare minimum features for maximum compatibility within the WordPress ecosystem!

This Container contains only one class than can easily be copied, modified, and adjusted to your needs. This class is designed to be a drop-in class (copy and paste, then include) for any plugin or theme.

Supports PHP 5.6 or above and Autowiring.

## Installation
- Rename the class by modifying the namespace and/or the class name.
- Use it! See usage below.
## Usage

```php
// Your autoloader class. Otherwise, you'll have to manually load the files.
require_once __DIR__ . '/vendor/autoload.php';

// Require the Container class file.
require_once __DIR__ . '/class-container.php';

// Instantiate. Replace the class name with your own.
$container = new DIC\WP\Container();
```

## Example Usage

Without this class we would have to wire our dependencies like the following:

```php
// Say we have 4 classes, 
// Foo which has Bar and Zag dependencies,
// Bar with Zig dependency,
// Zig, and Zag without any dependencies.

class Foo {
    protected $bar;
    protected $zag;
    public function __construct( Bar $bar, Zag $zag ) {
        $this->bar = $bar;
        $this->zag = $zag;
    }
    public function foo_bar_zig_zag() {
        return 'Foo Bar Zig Zag!';
    }
}

class Bar {
    protected $zig;
    public function __construct( Zig $zig ) {
        $this->zig = $zig;
    }
}

class Zig {} // ...

class Zag {} // ...

$foo = new Foo( new Bar( new Zig() ), new Zag() );

// Prints 'Foo Bar Zig Zag!'.
echo $foo->foo_bar_zig_zag();
```

With Container, we only need to:

```php
// Assuming, you're using autoloading (which is a must).
$foo = $container->get(Foo::class);

// Prints 'Foo Bar Zig Zag!'.
echo $foo->foo_bar_zig_zag();
```
Behind the scenes it uses the concept called 'Autowiring' using ReflectionClass to resolve all of the dependencies.

## Custom Definitions
By default, the Container autowiring only supports parameters that are type hinted. 

For example, the following will not work:
```php
class MyClass {
    public function __construct( $param1 = '', $param2 = array(), Dependency $param3 ){
        // Some business logic.
    }
}
// Fatal error.
$myClass = $container->get(MyClass::class);
```
Set a custom definition to resolve:
```php
// Set custom definition.
$container->set(MyClass::class, function( $container ){
     $param1 = 'myStringValue';
     $param2 = array();
     $param3 = new Dependency();
     return new MyClass( $param1, $param2, $param3 );
});

// Then, you may get it.
$myClass = $container->get('MyClass');
// or
$myClass = $container->get(MyClass::class);

// You may also use the method set_definitions and pass an array variable to define multiple definitions.
// Somewhere in your config file loaded in your plugin's bootstrap file:
$container->set_definitions(
    array(
        MyClass::class => function(){
            return new MyClass();
        },
        'wpdb' => function() {
            global $wpdb;
            return $wpdb;
        },
        'MySingletonClass' => function() {
            return MySingletonClass::getInstance();
        }
    )
);

// Then,
var_dump( $container->get('wpdb'));
```
## Limitations
This class is a simple implementation that doesn't include all the features available in PHP-DI or symfony/dependency-injection package. However, it does have the essential features like autowiring to help you begin.

## Contributing
All PRs are welcome!

## Additional Links:
- [PHP-DI](https://php-di.org/)
- [PHP Container Interface](https://www.php-fig.org/psr/psr-11/)
