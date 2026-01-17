<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when there is insufficient stock to fulfill a request.
 */
class InsufficientStockException extends Exception
{
    protected int $requestedQuantity;
    protected int $availableQuantity;
    protected ?int $productId;

    public function __construct(
        int $requestedQuantity,
        int $availableQuantity,
        ?int $productId = null,
        string $message = ''
    ) {
        $this->requestedQuantity = $requestedQuantity;
        $this->availableQuantity = $availableQuantity;
        $this->productId = $productId;

        if (empty($message)) {
            $message = "Insufficient stock: requested {$requestedQuantity}, available {$availableQuantity}";
        }

        parent::__construct($message);
    }

    public function getRequestedQuantity(): int
    {
        return $this->requestedQuantity;
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }
}
