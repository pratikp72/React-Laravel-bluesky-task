<?php

namespace App\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class BlueskyException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?Response $response = null,
        ?\Throwable $previous = null,
        int $code = 0,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function context(): array
    {
        return [
            'status' => $this->response?->status(),
            'body' => $this->response?->json(),
        ];
    }
}
