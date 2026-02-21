<?php

namespace Mawuva\LaravelSerialSequence\Commands;

use Illuminate\Console\Command;

class LaravelSerialSequenceCommand extends Command
{
    public $signature = 'laravel-serial-sequence';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
