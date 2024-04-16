# Laravel S3-compatible log system.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/halilcosdu/laravel-logweaver.svg?style=flat-square)](https://packagist.org/packages/halilcosdu/laravel-logweaver)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/halilcosdu/laravel-logweaver/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/halilcosdu/laravel-logweaver/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/halilcosdu/laravel-logweaver/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/halilcosdu/laravel-logweaver/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/halilcosdu/laravel-logweaver.svg?style=flat-square)](https://packagist.org/packages/halilcosdu/laravel-logweaver)


## Installation

You can install the package via composer:

```bash
composer require halilcosdu/laravel-logweaver
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-logweaver-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-logweaver-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-logweaver-views"
```

## Usage

```php
$logWeaver = new HalilCosdu\LogWeaver();
echo $logWeaver->echoPhrase('Hello, HalilCosdu!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Halil Cosdu](https://github.com/halilcosdu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
