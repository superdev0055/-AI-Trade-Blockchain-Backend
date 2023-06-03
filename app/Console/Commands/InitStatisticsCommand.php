<?php

namespace App\Console\Commands;

use App\NewServices\ReportServices;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class InitStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'InitStatisticsCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $now = Carbon::parse('2023-04-04');
        while ($now->isPast()) {
            $this->info($now->format('Y-m-d'));
            ReportServices::compute($now);
            $now->addDay();
        }
        return CommandAlias::SUCCESS;
    }
}
