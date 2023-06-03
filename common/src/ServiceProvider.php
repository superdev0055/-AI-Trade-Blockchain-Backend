<?php


namespace LaravelCommon;


use LaravelCommon\App\Console\Commands\DbBackupCommand;
use LaravelCommon\App\Console\Commands\DbCacheCommand;
use LaravelCommon\App\Console\Commands\DumpTableCommand;
use LaravelCommon\App\Console\Commands\GenEnumsToJSCommand;
use LaravelCommon\App\Console\Commands\GenFilesCommand;
use LaravelCommon\App\Console\Commands\RenameMigrationFilesCommand;
use LaravelCommon\App\Console\Commands\UpdateModelsCommand;
use LaravelCommon\App\Helpers\MySQLHelper;
use Illuminate\Database\Schema\Blueprint;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        // commands
        $this->commands([
            DbBackupCommand::class,
            DbCacheCommand::class,
            DumpTableCommand::class,
            GenEnumsToJSCommand::class,
            GenFilesCommand::class,
            RenameMigrationFilesCommand::class,
            UpdateModelsCommand::class
        ]);

        // blueprint macros
        Blueprint::macro('amount', function (?string $fieldName = 'amount', string $comment = '', int $default = 0, bool $nullable = false) {
            $this->decimal($fieldName, 30, 6)->comment($comment)->default($default)->nullable($nullable);
        });
        Blueprint::macro('address', function (?string $fieldName = 'address', string $comment = '', string $default = null, bool $nullable = false) {
            $this->string($fieldName, 92)->comment($comment)->default($default)->nullable($nullable);
        });
        Blueprint::macro('float8', function (string $fieldName, string $comment = '', int $default = 0, bool $nullable = false) {
            $this->double($fieldName, 20, 6)->comment($comment)->default($default)->nullable($nullable);
        });
        Blueprint::macro('myEnum', function (string $fieldName, mixed $enum, string $comment = '', string $default = null, bool $nullable = false) {
            $this->enum($fieldName, $enum::columns())->comment($enum::comment($comment))->default($default)->nullable($nullable);
        });
    }

    public function boot()
    {
        // Schema::defaultStringLength(191);
        MySQLHelper::Schema();

        $this->publishes([
            __DIR__ . '/resources/dist' => public_path('docs'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
    }
}
