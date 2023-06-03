<?php

namespace LaravelCommon\App\Helpers;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Exception;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class GenHelper
{
    /**
     * @param Table|null $table
     * @return string
     */
    public static function GenTableString(?Table $table): string
    {
        return "protected \$table = '{$table->getName()}';";
    }

    /**
     * @param Table|null $table
     * @return string
     */
    public static function GenTableCommentString(?Table $table): string
    {
        return "protected string \$comment = '{$table->getComment()}';";
    }

    /**
     * @param array $columns
     * @return string
     */
    public static function GenTableFillableString(array $columns): string
    {
        $columns = array_keys($columns);
        $fillable = implode("', '", $columns);
        return "protected \$fillable = ['$fillable'];" . PHP_EOL;
    }

    /**
     * @param Column[] $columns
     * @param string $tab
     * @return string
     */
    public static function GenColumnsRequestValidateString(?array $columns, string $tab = ''): string
    {
        $t1 = '';
        if (empty($columns)) return $t1;

        foreach ($columns as $item) {
            $name = $item->getName();
            $required = TableHelper::GetColumnRequired($item);
            $type = TableHelper::GetColumnType($item);
            $comment = $item->getComment();
            $t1 .= "$tab'$name' => '$required|$type', # $comment";
            if ($item != end($columns))
                $t1 .= PHP_EOL;
        }
        return $t1;
    }

    /**
     * @param array $columns
     * @return string
     */
    public static function GenColumnsInsertString(array $columns): string
    {
        $t1 = '';
        foreach ($columns as $item) {
            $name = $item->getName();
            $comment = $item->getComment();
            $t1 .= "'$name' => '', # $comment" . PHP_EOL;
        }
        return $t1;
    }

    /**
     * @param Table $table
     * @return string
     */
    public static function GenColumnsPropertiesString(Table $table): string
    {
        $t1 = '';
        foreach ($table->getColumns() as $item) {
            $name = $item->getName();
            $type = TableHelper::GetColumnType($item);
            $t1 .= " * @property $type \$$name" . PHP_EOL;
        }
        return $t1;
    }

    /**
     * @return string
     */
    public static function GenTableMethodsString(): string
    {
        return ' * @method static ifWhere(array $params, string $string)
 * @method static ifWhereLike(array $params, string $string)
 * @method static ifWhereIn(array $params, string $string)
 * @method static ifRange(array $params, string $string)
 * @method static create(array $array)
 * @method static unique(array $params, array $array, string $string)
 * @method static idp(array $params)
 * @method static findOrFail(int $id)
 * @method static selectRaw(string $string)
 * @method static withTrashed()
 ';
    }

    /**
     * @param Table $table
     * @return string
     */
    public static function GenTableRelations(Table $table): string
    {
        $t = '';
        // BelongsTo
        foreach ($table->getColumns() as $column) {
            $columnName = $column->getName();
            if (Str::endsWith($columnName, 's_id')) {
                $t1 = str_replace('_id', '', $columnName);
                $name = Str::singular($t1);

                $comment = $column->getComment();
                if ($comment && strpos($comment, 'ef[')) {
                    $comment = substr($comment, strpos($comment, 'ef[') + 3);
                    $t1 = substr($comment, 0, strpos($comment, ']'));
                }
                $modelName = Str::studly($t1);

                $t .= <<<t
    public function $name(): Relations\BelongsTo
    {
        return \$this->belongsTo(Models\\$modelName::class, '$columnName', 'id');
    }

t;
            }
        }

        // HasMany
        $tables = TableHelper::GetTables();
        $foreignKey = $table->getName() . '_id';
        foreach ($tables as $table) {
            foreach ($table->getColumns() as $column) {
                if ($column->getName() == $foreignKey) {
                    $name = $table->getName();
                    $modelName = Str::studly($name);
                    $t .= <<<t
    public function $name(): Relations\HasMany
    {
        return \$this->hasMany(Models\\$modelName::class, '$foreignKey', 'id');
    }

t;
                }
            }
        }
        return $t;
    }

    /**
     * @param string $nameSpace
     * @param string $controllerFilePath
     * @return string
     * @throws ReflectionException
     * @throws Exception
     */
    public static function GenTestContent(string $nameSpace, string $controllerFilePath): string
    {
        $ref = new ReflectionClass($nameSpace);
        $content = '';
        foreach ($ref->getMethods() as $method) {
            if ($method->class != $nameSpace)
                continue;
            if ($method->getModifiers() != 1)
                continue;

            $methodName = $method->getName();
            $methodName1 = Str::studly($methodName);
            $data = ReflectHelper::GetMethodAnnotation($nameSpace, $methodName);
            $intro = $data['intro'] ?? '';

            $params = ReflectHelper::GetMethodParamsArray($controllerFilePath, $nameSpace, $methodName);
            $paramsContent = '';
            foreach ($params as $item) {
                $paramsContent .= "            '$item[0]' => '', # $item[3]" . PHP_EOL;
            }

            $content .= <<<content
    /**
     * @intro $intro
     */
    public function test$methodName1()
    {
        \$this->go(__METHOD__, [
$paramsContent
        ]);
    }

content;

        }
        return $content;
    }

    /**
     * @param $route
     * @param string $filePath
     * @param $className
     * @param $methodName
     * @return string
     * @throws Exception
     */
    public static function GenApiMD($route, string $filePath, $className, $methodName): string
    {

        $data = ReflectHelper::GetMethodAnnotation($className, $methodName);
        $data['title'] = $data['title'] ?? $methodName;
        $data['intro'] = isset($data['intro']) ? ' > ' . $data['intro'] : '';
        $data['url'] = $route->uri;
        $data['method'] = $route->methods[0];
        $data['params'] = self::getParams($filePath, $className, $methodName);
        $data['response'] = isset($data['response']) ?
            json_encode($data['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) :
            json_encode([
                'code' => 0,
                'message' => 'ok'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $data['responseParams'] = self::getResponseParams($data);
        $stubContent = StubHelper::GetStub('api.md');
        return StubHelper::Replace([
            '{{title}}' => $data['title'],
            '{{intro}}' => $data['intro'],
            '{{url}}' => $data['url'],
            '{{method}}' => $data['method'],
            '{{params}}' => $data['params'],
            '{{response}}' => $data['response'],
            '{{responseParams}}' => $data['responseParams'],
        ], $stubContent);
    }

    /**
     * @param string $filePath
     * @param $className
     * @param $methodName
     * @return string
     * @throws ReflectionException
     */
    private static function getParams(string $filePath, $className, $methodName): string
    {
        $arr = ReflectHelper::GetMethodParamsArray($filePath, $className, $methodName);
        if (count($arr)) {
            $before = '|Params|Require|Type|Comment|' . PHP_EOL . '|----|----|----|----|' . PHP_EOL;
        } else {
            $before = '- NULL' . PHP_EOL;
        }
        $arr1 = [];
        foreach ($arr as $item) {
            $arr1[] = implode('|', $item);
        }
        return $before . implode(PHP_EOL, $arr1);
    }

    /**
     * @param array $data
     * @return string
     */
    private static function getResponseParams(array $data): string
    {
        $t1 = '';
        if (!isset($data['responseParams']))
            return $t1;
        $before = '### Response Params' . PHP_EOL . '|Params|Type|Comment|' . PHP_EOL . '|----|----|----|' . PHP_EOL;
        if (!is_array($data['responseParams'])) {
            $item = $data['responseParams'];
            $item = str_replace('nullable|', '- |', $item);
            $item = str_replace('required|', 'Y |', $item);
            $t1 .= '|' . str_replace(',', '|', $item) . '|' . PHP_EOL;
            return $before . $t1;
        }
        foreach ($data['responseParams'] as $item) {
            $item = str_replace('nullable|', '- |', $item);
            $item = str_replace('required|', 'Y |', $item);
            $t1 .= '|' . str_replace(',', '|', $item) . '|' . PHP_EOL;
        }
        return $t1;
    }

    /**
     * @param Table $table
     * @return string
     */
    public static function GenDatabaseMD(Table $table): string
    {

        $data['tableName'] = $table->getName();
        $data['tableComment'] = $table->getComment() ? '> ' . $table->getComment() : '';
        $data['columns'] = '';

        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $data['columns'] .= '|' . implode('|', [
                    $column->getName(),
                    $column->getType()->getName(),
                    $column->getPrecision(),
                    $column->getScale(),
                    $column->getNotNull() ? 'Y' : ' ',
                    $column->getDefault() ?: ' ',
                    $column->getComment() ?: ' ',
                ]) . '|' . PHP_EOL;
        }
        $stubContent = StubHelper::GetStub('db.md');
        return StubHelper::Replace([
            '{{tableName}}' => $data['tableName'],
            '{{tableComment}}' => $data['tableComment'],
            '{{columns}}' => $data['columns'],
        ], $stubContent);
    }
}
