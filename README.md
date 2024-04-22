# Laravel S3-compatible log system.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/halilcosdu/laravel-logweaver.svg?style=flat-square)](https://packagist.org/packages/halilcosdu/laravel-logweaver)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/halilcosdu/laravel-logweaver/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/halilcosdu/laravel-logweaver/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/halilcosdu/laravel-logweaver/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/halilcosdu/laravel-logweaver/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/halilcosdu/laravel-logweaver.svg?style=flat-square)](https://packagist.org/packages/halilcosdu/laravel-logweaver)

# Laravel LogWeaver

Laravel LogWeaver is a PHP package designed to provide a simple and flexible way to log events in your Laravel application. It offers a fluent interface for creating logs, allowing you to easily specify the log level, resource, content, and storage disk.

## Features

- **Fluent Interface**: Easily create logs with a fluent, chainable interface.
- **Multiple Log Levels**: Supports different log levels including 'info', 'warning', 'error', and 'critical'.
- **Resource Specification**: Specify the resource of the log, such as 'system' or 'event'.
- **Customizable Storage**: Choose your storage disk and directory.
- **Parameter Validation**: Ensures the validity of log parameters before logging.
- **Array and JSON Conversion**: Convert your logs to array or JSON format.
- **Asynchronous Logging**: Log events asynchronously with optional waiting for the log to be written to the disk.

## Installation

You can install the package via composer:

```bash
composer require halilcosdu/laravel-logweaver
```

```bash
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
composer require league/flysystem-ftp "^3.0"
composer require league/flysystem-sftp-v3 "^3.0"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="logweaver-config"
```

This is the contents of the published config file:

```php
return [
    'sleep' => env('LOG_WEAVER_SLEEP', 0.5),
];
```

## Usage

```php
$log = LogWeaver::description('User logged in')
    ->logResource('event')
    ->content(['user_id' => 1, 'email' => 'test@example.com'])
    ->level('info')
    ->toArray();
```

```php
$log = LogWeaver::description('System error occurred')
    ->logResource('system')
    ->content(['error' => 'Database connection failed'])
    ->level('error')
    ->log($path, $wait);
```

```php
$log = LogWeaver::description('Payment gateway down')
    ->logResource('event')
    ->content(['gateway' => 'Stripe', 'status' => 'down'])
    ->level('critical')
    ->log();
```

```php
$log = LogWeaver::description('Disk space running low')
    ->logResource('system')
    ->content(['disk_space' => '10% remaining'])
    ->level('warning')
    ->toJson();
```

```php
$log = LogWeaver::description('User registered')
    ->logResource('event')
    ->content(['user_id' => 2, 'email' => 'newuser@example.com'])
    ->level('info')
    ->disk('local')
    ->directory('custom_logs')
    ->log();
```

```php
$response = LogWeaver::download(string $path, $name = null, array $headers = []): StreamedResponse;
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
