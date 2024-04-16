# Changelog

All notable changes to `laravel-logweaver` will be documented in this file.

## v1.0.0 - 2024-04-16

### Release v1.0.0

This is the first major release of the Laravel LogWeaver package. This package provides a simple and flexible way to log events in your Laravel application, with support for multiple storage disks.

#### Features

- Fluent interface for creating logs.
- Support for different log levels: info, warning, error, and critical.
- Ability to specify the resource of the log: system or event.
- Customizable storage disk and directory.
- Validation of log parameters.
- Conversion of log to array or JSON.
- Asynchronous logging with optional waiting for the log to be written to the disk.

#### Usage Examples

Logging a user login event:

```php
$log = LogWeaver::description('User logged in')
    ->logResource('event')
    ->content(['user_id' => 1, 'email' => 'test@example.com'])
    ->level('info')
    ->toArray();

```
Logging a system error:

```php
$log = LogWeaver::description('System error occurred')
    ->logResource('system')
    ->content(['error' => 'Database connection failed'])
    ->level('error')
    ->toArray();

```
Logging a critical event:

```php
$log = LogWeaver::description('Payment gateway down')
    ->logResource('event')
    ->content(['gateway' => 'Stripe', 'status' => 'down'])
    ->level('critical')
    ->toArray();

```
Logging a warning:

```php
$log = LogWeaver::description('Disk space running low')
    ->logResource('system')
    ->content(['disk_space' => '10% remaining'])
    ->level('warning')
    ->toArray();

```
Logging an event with custom disk and directory:

```php
$log = LogWeaver::description('User registered')
    ->logResource('event')
    ->content(['user_id' => 2, 'email' => 'newuser@example.com'])
    ->level('info')
    ->disk('local')
    ->directory('custom_logs')
    ->toArray();

```
#### Installation

You can install the package via composer:

```bash
composer require halilcosdu/laravel-logweaver

```
You can publish the config file with:

```bash
php artisan vendor:publish --tag="logweaver-config"

```
#### Testing

You can run the tests with:

```bash
composer test

```
#### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

#### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

#### Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

#### Credits

- [Halil Cosdu](https://github.com/halilcosdu)
- [All Contributors](../../contributors)

#### License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
