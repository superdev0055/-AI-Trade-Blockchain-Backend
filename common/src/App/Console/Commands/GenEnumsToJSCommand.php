<?php

namespace LaravelCommon\App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenEnumsToJSCommand extends Command
{
    protected $signature = 'GenEnumsToJSCommand';
    protected $description = 'Command description';

    /**
     * @return int
     * @throws ReflectionException
     */
    public function handle(): int
    {
        $str = '';
        foreach (File::files(app_path("Enums")) as $item) {
            $fileName = str_replace('.php', '', $item->getFilename());
            $className = '\\App\\Enums\\' . $fileName;
            $this->genFile($className, $fileName, $str);
        }
        Storage::put('index.ts', $str);
        return CommandAlias::SUCCESS;
    }

    /**
     * @param string $className
     * @param string $fileName
     * @param string $str
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    private function genFile(string $className, string $fileName, string &$str): void
    {
        $str .= "export const $fileName = [" . PHP_EOL;
        $enumRef = new ReflectionClass($className);
//        $anno = ReflectHelper::GetEnumAnnotation($className);
        foreach ($enumRef->getConstants() as $enum) {
            $regex = '/@color\s+(\w+)/';
            preg_match($regex, $enumRef->getReflectionConstant($enum->name)->getDocComment(), $matches);
            $color = $matches[1] ?? 'gray';
            $str .= "\t" . json_encode([
                    'label' => $enum->name,
                    'value' => $enum->value,
                    'color' => $color,
                ]) . "," . PHP_EOL;
        }
        $str .= "];" . PHP_EOL;
    }
}
