<?php

namespace HalilCosdu\LogWeaver\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HalilCosdu\LogWeaver\LogWeaver
 *
 * @method static \HalilCosdu\LogWeaver\LogWeaver description(string $description)
 * @method static \HalilCosdu\LogWeaver\LogWeaver logResource(string $logResource)
 * @method static \HalilCosdu\LogWeaver\LogWeaver level(string $level)
 * @method static \HalilCosdu\LogWeaver\LogWeaver content(array $content)
 * @method static \HalilCosdu\LogWeaver\LogWeaver disk(string $disk)
 * @method static \HalilCosdu\LogWeaver\LogWeaver log(string $path = null)
 * @method array toArray()
 * @method string toJson()
 */
class LogWeaver extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \HalilCosdu\LogWeaver\LogWeaver::class;
    }
}
