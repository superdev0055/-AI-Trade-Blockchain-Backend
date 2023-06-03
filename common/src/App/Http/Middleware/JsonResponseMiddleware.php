<?php

namespace LaravelCommon\App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonResponseMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
        $response = $next($request);

        $base = [
            'code' => 0,
            'message' => 'ok',
        ];

        // responseDoNotWrap
        $responseDoNotWrap = config('common.responseDoNotWrap', []);
        if (count($responseDoNotWrap)) {
            $pathInfo = $request->getPathInfo();
            if (in_array($pathInfo, $responseDoNotWrap)) {
                return $response;
            }
        }

        // if is json response
        if ($response instanceof JsonResponse) {

            $data = $response->getData();
            $type = gettype($data);
            if ($type == 'object') {
                // exception
                if (property_exists($data, 'code') && property_exists($data, 'message') && $data->code !== 0)
                    return $response->setData($data);

                // additions
                if (property_exists($data, 'additions')) {
                    $base['additions'] = $data->additions;
                    unset($data->additions);
                }

                // pagination
                if (property_exists($data, 'data') && property_exists($data, 'current_page')) {
                    $base['data'] = $data->data;
                    $base['meta'] = [
                        'total' => $data->total ?? 0,
                        'per_page' => (int)$data->per_page ?? 0,
                        'current_page' => $data->current_page ?? 0,
                        'last_page' => $data->last_page ?? 0
                    ];
                } else {
                    $base['data'] = $data;
                }
            } else {
                if ($data != '' && $data != null) {
                    $base['data'] = $data;
                }
            }
            return $response->setData($base);
        } elseif ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        } elseif ($response->getContent() == "") {
            return response()->json($base);
        } else {
            return $response;
        }
    }
}
