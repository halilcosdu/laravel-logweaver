<?php

namespace HalilCosdu\LogWeaver\Facades;

use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @see \HalilCosdu\LogWeaver\LogWeaver
 *
 * @method static \HalilCosdu\LogWeaver\LogWeaver description(string $description): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver logResource(string $logResource): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver level(string $level): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver content(array $content): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver disk(string $disk): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver directory(string $directory): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver log(?string $path = null, bool $wait = false): array
 * @method static \HalilCosdu\LogWeaver\LogWeaver download(string $path, $name = null, array $headers = []): StreamedResponse
 * @method array toArray(): array
 * @method string toJson($options = 0): false|string
 */
class LogWeaver extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \HalilCosdu\LogWeaver\LogWeaver::class;
    }
}
