<?php
declare(strict_types=1);
namespace Core\Exceptions;

class NotFoundException extends \Exception {
    private ?array $payload;
    public function __construct(int $status = 404, string $message = 'Model not found', ?array $payload = null) {
        parent::__construct($message, $status);
        $this->payload = $payload;
    }
    public function getStatus(): int { return $this->getCode(); }
    public function getPayload(): ?array { return $this->payload; }
}