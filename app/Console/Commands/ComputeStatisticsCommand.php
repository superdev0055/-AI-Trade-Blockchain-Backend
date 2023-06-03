<?php

namespace App\Console\Commands;

use App\NewServices\ReportServices;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ComputeStatisticsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ComputeStatisticsCommand  {day?}';

    /**
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle(): int
    {
        $day = $this->argument('day');
        if($day)
            $day = Carbon::parse($day);
        else
            $day = now();
        ReportServices::compute($day);
        return CommandAlias::SUCCESS;
    }
}
