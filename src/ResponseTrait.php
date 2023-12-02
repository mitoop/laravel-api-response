<?php

namespace Mitoop\Http;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait ResponseTrait
{
    protected function success($data = null, $message = 'success'): JsonResponse
    {
        if ($data instanceof Paginator) {
            return $this->pagingSuccess($data, $message);
        }

        return $this->sendResponse($data, $message, ResponseCode::$success);
    }

    protected function error($message = 'error', $data = null): JsonResponse
    {
        return $this->sendResponse($data, $message, ResponseCode::$error);
    }

    protected function unauthenticated($message = 'Unauthenticated.', $data = null): JsonResponse
    {
        return $this->sendResponse($data, $message, ResponseCode::$unauthenticated);
    }

    private function pagingSuccess(Paginator $paginator, $message): JsonResponse
    {
        $meta = [
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
        ];

        if (method_exists($paginator, 'total')) {
            $meta['total'] = (int) $paginator->total();
        }

        return $this->sendResponse($paginator->getCollection(), $message, ResponseCode::$success, $meta);
    }

    private function sendResponse($data, $message, $code, $meta = null): JsonResponse
    {
        $data = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta) {
            $data['meta'] = $meta;
        }

        return Response::json(data: $data, options: JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }
}
