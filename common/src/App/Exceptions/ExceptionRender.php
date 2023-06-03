<?php

namespace LaravelCommon\App\Exceptions;

use ArgumentCountError;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionRender
{
    /**
     * @param Throwable $e
     * @return JsonResponse
     * @throws Err
     */
    public static function Render(Throwable $e): JsonResponse
    {
        $class = get_class($e);
        if ($class != Err::class)
            switch ($class) {
                case AuthenticationException::class:
                    Err::Throw(__("User not login"), 10000);
//                case NotFoundHttpException::class:
//                case ValidationException::class:
//                case ArgumentCountError::class:
//                default:
//                    Err::Throw($e->getMessage(), 999);
            }

        $request = request();
        $isDebug = config('app.debug');

        $debugInfo = [
            'request' => [
                'client' => $request->getClientIps(),
                'method' => $request->getMethod(),
                'uri' => $request->getPathInfo(),
                'params' => $request->all(),
            ],
            'exception' => [
                'class' => $class,
                'trace' => self::getTrace($e)
            ]
        ];

        $code = $e->getCode() == 0 ? 999 : $e->getCode();
        $message = $e->getMessage();

        $skipLog = self::getSkipLog($request->getPathInfo());
        if (!$skipLog)
            Log::error($message, $debugInfo);

        return response()->json([
            'code' => $code,
            'message' => $message,
            'debug' => $isDebug ? $debugInfo : null
        ]);
    }

    /**
     * @param Throwable $e
     * @return array
     */
    private static function getTrace(Throwable $e): array
    {
        $arr = $e->getTrace();
        $file = array_column($arr, 'file');
        $line = array_column($arr, 'line');
        $trace = [];
        for ($i = 0; $i < count($file); $i++) {
            if (!strpos($file[$i], '/vendor/'))
                $trace[] = [
                    $i => "$file[$i]($line[$i])"
                ];
        }
        return $trace;
    }

    /**
     * @param string $pathInfo
     * @return bool
     */
    private static function getSkipLog(string $pathInfo): bool
    {
        $skipLogPathInfo = config('common.skipLogPathInfo');
        if (!$skipLogPathInfo)
            return false;

        return in_array($pathInfo, $skipLogPathInfo);
    }
}
