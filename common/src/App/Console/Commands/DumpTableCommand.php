<?php

namespace LaravelCommon\App\Console\Commands;

use LaravelCommon\App\Helpers\DbalHelper;
use LaravelCommon\App\Helpers\GenHelper;
use LaravelCommon\App\Helpers\TableHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DumpTableCommand extends Command
{
    protected $signature = 'dump {table}';
    protected $description = 'dump the fields of the table';

    /**
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function handle(): void
    {
        DbalHelper::register();

        $tableName = (string)Str::of($this->argument('table'))->snake();
        $table = TableHelper::GetTable($tableName);
        $columns = TableHelper::GetTableColumns($table);

        $this->warn('Gen Table template');
        $this->line(GenHelper::GenTableString($table));
        $this->line(GenHelper::GenTableCommentString($table));
        $this->line(GenHelper::GenTableFillableString($columns));

        $this->warn('gen Validate template');
        $this->line(GenHelper::GenColumnsRequestValidateString($columns));

        $this->warn('gen Insert template');
        $this->line(GenHelper::GenColumnsInsertString($columns));
    }
}
