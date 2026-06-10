<?php
declare(strict_types=1);
namespace Core\Exceptions;

class HttpException extends \Exception {
    private ?array $payload;
    public function __construct(int $status = 500, string $message = 'Error', ?array $payload = null) {
        parent::__construct($message, $status);
        $this->payload = $payload;
    }
    public function getStatus(): int { return $this->getCode(); }
    public function getPayload(): ?array { return $this->payload; }
}