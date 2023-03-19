
# WordPress Dependency Injection Container

This class is a basic implementation of a Container. It is inspired by PSR-11 Container Interface but with stripped down with a bare minimum feature for maximum compatibility within the WordPress ecosystem!

This Container contains only one class than can easily be copied, modified, and adjusted to your needs.

Supports PHP 5.6 or above and Autowiring.

## Usage

```php
    // Require the Container class file.
    require_once __DIR__ . '/class-container.php';

    // Instantiate.
    $container = new Container();
```

    
