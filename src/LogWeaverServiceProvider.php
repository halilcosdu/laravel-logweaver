<?php

namespace HalilCosdu\LogWeaver;

use HalilCosdu\LogWeaver\Commands\LogWeaverCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LogWeaverServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-logweaver')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-logweaver_table')
            ->hasCommand(LogWeaverCommand::class);
    }
}
