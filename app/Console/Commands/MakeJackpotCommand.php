<?php

namespace App\Console\Commands;

use App\NewServices\JackpotsServices;
use Illuminate\Console\Command;

class MakeJackpotCommand extends Command
{
    protected $signature = 'MakeJackpotCommand';
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle(): int
    {
        JackpotsServices::CreateNewJackpot();
        return 0;
    }
}
