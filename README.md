# PHP_UML bundle

That's another way to install PHP_UML without the PEAR

## Git 

Just clone and run

	./bin/phpuml

## Composer

Add ["zerkalica/phpuml"](http://packagist.org/packages/zerkalica/phpuml) package to your composer.json file

    {
        "require": {
            "php":          ">=5.3.2",
            "zerkalica/phpuml": ">=1.0"
        }
    }

After install/update vendors with Composer, you can simply run

    php vendor/zerkalica/phpuml/bin/phpuml

