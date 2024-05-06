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
 * @method static \HalilCosdu\LogWeaver\LogWeaver relation(?array $relation): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver log(?string $path = null, bool $wait = false): array
 * @method static \HalilCosdu\LogWeaver\LogWeaver download(string $path, $name = null, array $headers = []): StreamedResponse
 * @method static \HalilCosdu\LogWeaver\LogWeaver delete(string|array $paths): bool
 * @method static \HalilCosdu\LogWeaver\LogWeaver validation(?bool $validation): static
 * @method static \HalilCosdu\LogWeaver\LogWeaver get(string $path): string
 * @method static \HalilCosdu\LogWeaver\LogWeaver toArray(): array
 * @method static \HalilCosdu\LogWeaver\LogWeaver toJson($options = 0): false|string
 */
class LogWeaver extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \HalilCosdu\LogWeaver\LogWeaver::class;
    }
}
