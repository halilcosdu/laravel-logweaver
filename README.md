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
```

```bash
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
\HalilCosdu\LogWeaver\Facades\LogWeaver::description(string $description): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::logResource(string $logResource): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::level(string $level): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::content(array $content): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::disk(string $disk): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::directory(string $directory): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::relation(?array $relation): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::log(?string $path = null, bool $wait = false): array
\HalilCosdu\LogWeaver\Facades\LogWeaver::download(string $path, $name = null, array $headers = []): StreamedResponse
\HalilCosdu\LogWeaver\Facades\LogWeaver::delete(string|array $paths): bool
\HalilCosdu\LogWeaver\Facades\LogWeaver::validation(?bool $validation): static
\HalilCosdu\LogWeaver\Facades\LogWeaver::get(string $path): string
\HalilCosdu\LogWeaver\Facades\LogWeaver::toArray(): array
\HalilCosdu\LogWeaver\Facades\LogWeaver::toJson($options = 0): false|string
```

```php
$log = LogWeaver::description('User logged in')
    ->logResource('event')
    ->content(['email' => 'test@example.com'])
    ->level('info')
    ->relation(['user_id' => 1])
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
If you want to disable built-in validation, you can use the following methods:

Built-in validations are:

```php
$rules = [
    'level' => ['required', 'in:info,warning,error,critical'],
    'log_resource' => ['required', 'in:system,event'],
    'description' => ['required', 'string'],
    'directory' => ['string'],
    'disk' => ['string', 'in:s3,local,ftp,sftp,public'],
    'content' => ['required', 'array'],
];
```

```php
$log = LogWeaver::description('User registered')
    ->validation(false)
    ->logResource('custom_input')
    ->content(['user_id' => 2, 'email' => 'newuser@example.com'])
    ->level('custom_input')
    ->disk('custom_input')
    ->directory('custom_logs')
    ->log();
```


```php
$response = LogWeaver::download(string $path, $name = null, array $headers = []): StreamedResponse;
$response = LogWeaver::delete(string|array $paths): bool;
$response = LogWeaver::get(string $path): string;
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
