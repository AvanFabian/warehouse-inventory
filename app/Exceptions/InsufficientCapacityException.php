<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when there is insufficient bin capacity to store items.
 */
class InsufficientCapacityException extends Exception
{
    protected int $requestedQuantity;
    protected int $availableCapacity;
    protected ?int $warehouseId;

    public function __construct(
        int $requestedQuantity,
        int $availableCapacity,
        ?int $warehouseId = null,
        string $message = ''
    ) {
        $this->requestedQuantity = $requestedQuantity;
        $this->availableCapacity = $availableCapacity;
        $this->warehouseId = $warehouseId;

        if (empty($message)) {
            $message = "Insufficient capacity: requested {$requestedQuantity}, available {$availableCapacity}";
        }

        parent::__construct($message);
    }

    public function getRequestedQuantity(): int
    {
        return $this->requestedQuantity;
    }

    public function getAvailableCapacity(): int
    {
        return $this->availableCapacity;
    }

    public function getWarehouseId(): ?int
    {
        return $this->warehouseId;
    }
}
