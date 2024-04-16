<?php

namespace HalilCosdu\LogWeaver\Commands;

use Illuminate\Console\Command;

class LogWeaverCommand extends Command
{
    public $signature = 'laravel-logweaver';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
