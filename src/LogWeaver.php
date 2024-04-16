<?php

namespace HalilCosdu\LogWeaver;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Dumpable;

class LogWeaver implements Arrayable, Jsonable
{
    use Dumpable;

    private string $description = '';

    private string $logResource = 'system';

    private array $content = [];

    private string $level = 'info';

    private string $disk = 's3';

    private string $directory = 'logs';

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function directory(string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function getLogResource(): string
    {
        return $this->logResource;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function logResource(string $logResource): static
    {
        $this->logResource = $logResource;

        return $this;
    }

    public function content(array $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function level(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    private function validateParameters(): void
    {
        $data = [
            'disk' => $this->getDisk(),
            'directory' => $this->getDirectory(),
            'level' => $this->getLevel(),
            'log_resource' => $this->getLogResource(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
        ];

        $rules = [
            'level' => ['required', 'in:info,warning,error,critical'],
            'log_resource' => ['required', 'in:system,event'],
            'description' => ['required', 'string'],
            'directory' => ['string'],
            'disk' => ['string'],
            'content' => ['required', 'array'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }

    /**
     * @throws Exception
     */
    private function getValidatedData(): array
    {
        $this->validateParameters();

        return [
            'disk' => $this->getDisk(),
            'directory' => $this->getDirectory(),
            'log_resource' => $this->getLogResource(),
            'level' => $this->getLevel(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
        ];
    }

    /**
     * @throws Exception
     */
    public function toArray(): array
    {
        return $this->getValidatedData();
    }

    /**
     * @throws Exception
     */
    public function toJson($options = 0): false|string
    {
        return json_encode($this->getValidatedData(), $options);
    }

    /**
     * @throws Exception
     */
    private function waitForContentFromDisk(string $path): void
    {
        $start = time();
        $timeout = 10 * 60;

        do {
            Sleep::sleep(config('logweaver.sleep'));

            $isFileVisible = Storage::disk($this->getDisk())->exists($path);

            if ((time() - $start) > $timeout) {
                throw new Exception('Timeout exceeded while trying to get content from Storage.');
            }
        } while ($isFileVisible !== true);
    }

    /**
     * @throws Exception
     */
    public function log(?string $path = null, bool $wait = false): array
    {
        if (is_null($path)) {
            $path = sprintf('%s/%s.json', $this->getDirectory(), Str::random(40));
        }

        $check = Storage::disk($this->getDisk())->put($path, json_encode($this->getValidatedData()));

        if ($wait) {
            $this->waitForContentFromDisk($path);
        }

        return [
            'status' => $check,
            'path' => $path,
            'url' => $check ? Storage::disk($this->getDisk())->url($path) : null,
        ];
    }
}
