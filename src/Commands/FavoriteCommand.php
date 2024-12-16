<?php

namespace Ofaws\Favorite\Commands;

use Illuminate\Console\Command;

class FavoriteCommand extends Command
{
    public $signature = 'favorite';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
