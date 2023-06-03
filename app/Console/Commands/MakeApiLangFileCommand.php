<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Console\Command\Command as CommandAlias;

class MakeApiLangFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MakeApiLangFileCommand';

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
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function handle(): int
    {
        $content = json_decode(File::get(base_path('lang/en.json')), true);
        $keys = [];
        foreach ($content as $key => $value) {
            $keys[] = $key;
        }

        (new FastExcel(collect($keys)))->export('lang/en.xlsx', function ($item) {
            return [
                'keys' => $item,
                'translates' => '',
            ];
        });

        return CommandAlias::SUCCESS;
    }
}
