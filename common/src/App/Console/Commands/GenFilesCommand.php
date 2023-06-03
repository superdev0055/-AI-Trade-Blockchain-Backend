<?php

namespace LaravelCommon\App\Console\Commands;

use Doctrine\DBAL\Schema\Table;
use Exception;
use LaravelCommon\App\Helpers\DbalHelper;
use LaravelCommon\App\Helpers\GenHelper;
use LaravelCommon\App\Helpers\ReflectHelper;
use LaravelCommon\App\Helpers\StubHelper;
use LaravelCommon\App\Helpers\TableHelper;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Str;
use ReflectionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenFilesCommand extends BaseCommand
{
    protected $name = 'gf';
    protected $description = 'Generate files of the table';

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['key', InputArgument::REQUIRED, 'table name'],
        ];
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['migration', 'm', InputOption::VALUE_NONE, 'gen migration file'],
            ['model', 'd', InputOption::VALUE_NONE, 'gen model file'],
            ['controller', 'c', InputOption::VALUE_NONE, 'gen controller file'],
            ['test', 't', InputOption::VALUE_NONE, 'gen test file'],
            ['force', 'f', InputOption::VALUE_NONE, 'force overwrite'],
        ];
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function handle(): void
    {
        DbalHelper::register();
        $options = $this->options();
        $key = $this->argument('key');
        $key = str_replace('/', '\\', $key);
        $prefix = explode('\\', $key);
        $table = end($prefix);
        array_pop($prefix);
        $prefix = array_map(function ($item) {
            return Str::of($item)->studly();
        }, $prefix);

        $tableName = (string)Str::of($table)->snake();
        $modelName = (string)Str::of($tableName)->studly();

        $table = TableHelper::GetTable($tableName);
        $columns = TableHelper::GetTableColumns($table);

        if ($options['migration'])
            $this->makeMigration($tableName);

        if ($options['model'])
            $this->makeModel($table, $columns, $modelName, $options);

        if ($options['controller'])
            $this->makeController($table, $columns, $modelName, $prefix, $options);

        if ($options['test'])
            $this->makeTest($modelName, $prefix, $options);
    }

    /**
     * @param string $tableName
     */
    private function makeMigration(string $tableName)
    {
        $this->call('make:migration', [
            'name' => "create_{$tableName}_table",
            '--create' => $tableName,
            '--table' => $tableName,
        ]);
    }

    /**
     * @param Table $table
     * @param array $columns
     * @param string $modelName
     * @param array $options
     */
    private function makeModel(Table $table, array $columns, string $modelName, array $options)
    {
        $hasSoftDelete = TableHelper::GetColumnsHasSoftDelete($table->getColumns());
        // BaseModel
        $stubName = $hasSoftDelete ? 'BaseModelWithSoftDelete.stub' : 'BaseModel.stub';
        $stubContent = StubHelper::GetStub($stubName);
        $stubContent = StubHelper::Replace([
            '{{ModelProperties}}' => GenHelper::GenColumnsPropertiesString($table),
            '{{ModelMethods}}' => GenHelper::GenTableMethodsString(),
            '{{ModelName}}' => $modelName,
            '{{TableString}}' => GenHelper::GenTableString($table),
            '{{TableCommentString}}' => GenHelper::GenTableCommentString($table),
            '{{TableFillableString}}' => GenHelper::GenTableFillableString($columns),
            '{{ModelRelations}}' => GenHelper::GenTableRelations($table),
        ], $stubContent);
        $filePath = $this->laravel['path'] . '/Models/Base/Base' . $modelName . '.php';
        $result = StubHelper::Save($filePath, $stubContent, $options['force']);
        $this->line($result);
        // Model
        $stubContent = StubHelper::GetStub('Model.stub');
        $stubContent = StubHelper::Replace([
            '{{ModelName}}' => $modelName,
        ], $stubContent);
        $filePath = $this->laravel['path'] . '/Models/' . $modelName . '.php';
        $result = StubHelper::Save($filePath, $stubContent);
        $this->line($result);
    }

    /**
     * @param Table|null $table
     * @param array|null $columns
     * @param string $modelName
     * @param array $prefix
     * @param array $options
     * @throws Exception
     */
    private function makeController(?Table $table, ?array $columns, string $modelName, array $prefix, array $options)
    {

        if (count($prefix) == 0)
            throw new Exception('need module name, eg：admin/admin');

        $hasSoftDelete = TableHelper::GetColumnsHasSoftDelete($table ? $table->getColumns() : []);
        $stubName = $hasSoftDelete ? "controllerWithSoftDelete.stub" : "controller.stub";

        $stubContent = StubHelper::GetStub($stubName);
        $stubContent = StubHelper::Replace([
            '{{ModelName}}' => $modelName,
            '{{TableComment}}' => $table ? $table->getComment() : '',
            '{{ModuleName}}' => implode('\\', $prefix),
            '{{InsertString}}' => GenHelper::GenColumnsRequestValidateString($columns, "\t\t\t"),
        ], $stubContent);

        $moduleName = implode('/', $prefix);
        $filePath = $this->laravel['path'] . "/Modules/$moduleName/{$modelName}Controller.php";
        $result = StubHelper::Save($filePath, $stubContent, $options['force']);
        $this->line($result);
    }

    /**
     * @param string $modelName
     * @param array $prefix
     * @param array $options
     * @throws ReflectionException
     * @throws Exception
     */
    private function makeTest(string $modelName, array $prefix, array $options)
    {
        if (count($prefix) == 0)
            throw new Exception('need module name. eg：admin/admin');

        $nameSpace = 'App\\Modules\\' . implode('\\', $prefix) . '\\' . $modelName . 'Controller';
        $controllerFilePath = $this->laravel['path'] . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . implode('/', $prefix) . '/' . $modelName . 'Controller.php';
        $content = GenHelper::GenTestContent($nameSpace, $controllerFilePath);

        $stubContent = StubHelper::GetStub('test.stub');
        $stubContent = StubHelper::Replace([
            '{{controllerIntro}}' => ReflectHelper::GetControllerAnnotation($nameSpace),
            '{{ModelName}}' => $modelName,
            '{{ModuleName}}' => implode('\\', $prefix),
            '{{content}}' => $content,
        ], $stubContent);
        $moduleName = implode('/', $prefix);
        $filePath = $this->laravel['path'] . "/../tests/Modules/$moduleName/{$modelName}ControllerTest.php";
        $result = StubHelper::Save($filePath, $stubContent, $options['force']);
        $this->line($result);
    }
}
