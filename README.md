
# WordPress Dependency Injection Container

This class is a basic implementation of a Container. It is inspired by PSR-11 Container Interface but with stripped down with a bare minimum feature for maximum compatibility within the WordPress ecosystem!

This Container contains only one class than can easily be copied, modified, and adjusted to your needs. This class is designed to be a drop-in class for any plugin or theme.

Supports PHP 5.6 or above and Autowiring.




## Installation
- Rename the class by modifying the namespace and/or the class name.
- Use it! See usage below.
## Usage

```php
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
}

class Bar {
    protected $zig;
    public function __construct( Zig $zig ) {
        $this->zig = $zig;
    }
}

class Zig {
    // ...
}

class Zag {
    // ...
}

$foo = new Foo( new Bar( new Baz() ), new Zag() );
```
    
