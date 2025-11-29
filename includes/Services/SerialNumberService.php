<?php
/**
 * SerialNumber Service
 *
 * Business logic layer for serial number operations including validation,
 * movement tracking, and integration with various transaction types.
 *
 * @package Services
 * @author FrontAccounting Refactoring Team
 * @license GPL-3.0
 */

namespace Services;

use Database\SerialNumberRepository;
use InvalidArgumentException;

/**
 * SerialNumber Service Class
 *
 * Handles all serial number business logic operations including:
 * - Serial number validation and generation
 * - Movement tracking across transactions
 * - Status management and updates
 * - Integration with inventory, sales, and purchasing
 */
class SerialNumberService
{
    /**
     * Serial number repository
     */
    private SerialNumberRepository $repository;

    /**
     * Constructor
     *
     * @param SerialNumberRepository $repository
     */
    public function __construct(SerialNumberRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validate serial numbers for a transaction
     *
     * @param object $cart Transaction cart
     * @param int $transType Transaction type
     * @return bool True if valid
     * @throws InvalidArgumentException If validation fails
     */
    public function validateTransactionSerials(object $cart, int $transType): bool
    {
        foreach ($cart->line_items as $lineItem) {
            if ($this->requiresSerialNumber($lineItem->stock_id)) {
                $this->validateLineItemSerials($lineItem, $transType);
            }
        }

        return true;
    }

    /**
     * Record serial number movements for a transaction
     *
     * @param object $cart Transaction cart
     * @param int $transType Transaction type
     * @return bool True if recorded successfully
     */
    public function recordTransactionMovements(object $cart, int $transType): bool
    {
        foreach ($cart->line_items as $lineItem) {
            if ($this->requiresSerialNumber($lineItem->stock_id)) {
                $this->recordLineItemMovements($lineItem, $cart, $transType);
            }
        }

        return true;
    }

    /**
     * Reverse serial number movements for a transaction
     *
     * @param int $transType Transaction type
     * @param int $transNo Transaction number
     * @return bool True if reversed successfully
     */
    public function reverseTransactionMovements(int $transType, int $transNo): bool
    {
        $movements = $this->repository->getMovementsByTransaction($transType, $transNo);

        foreach ($movements as $movement) {
            // Reverse the movement
            $this->repository->createMovement([
                'serial_item_id' => $movement['serial_item_id'],
                'trans_type' => $transType,
                'trans_no' => $transNo,
                'stock_id' => $movement['stock_id'],
                'serial_no' => $movement['serial_no'],
                'location_from' => $movement['location_to'],
                'location_to' => $movement['location_from'],
                'qty' => -$movement['qty'], // Negative quantity for reversal
                'reference' => 'Reversal of transaction ' . $transNo,
            ]);

            // Update serial item status if needed
            $this->updateSerialStatus($movement['serial_item_id']);
        }

        return true;
    }

    /**
     * Handle inventory adjustment
     *
     * @param array $eventData Event data
     */
    public function handleInventoryAdjustment(array $eventData): void
    {
        // Implementation for inventory adjustment handling
    }

    /**
     * Handle inventory transfer
     *
     * @param array $eventData Event data
     */
    public function handleInventoryTransfer(array $eventData): void
    {
        // Implementation for inventory transfer handling
    }

    /**
     * Handle sales delivery
     *
     * @param array $eventData Event data
     */
    public function handleSalesDelivery(array $eventData): void
    {
        // Implementation for sales delivery handling
    }

    /**
     * Handle sales invoice
     *
     * @param array $eventData Event data
     */
    public function handleSalesInvoice(array $eventData): void
    {
        // Implementation for sales invoice handling
    }

    /**
     * Handle purchasing receipt
     *
     * @param array $eventData Event data
     */
    public function handlePurchasingReceipt(array $eventData): void
    {
        // Implementation for purchasing receipt handling
    }

    /**
     * Handle purchasing invoice
     *
     * @param array $eventData Event data
     */
    public function handlePurchasingInvoice(array $eventData): void
    {
        // Implementation for purchasing invoice handling
    }

    /**
     * Check if an item requires serial number tracking
     *
     * @param string $stockId Stock item ID
     * @return bool True if serial tracking required
     */
    private function requiresSerialNumber(string $stockId): bool
    {
        // Check item preferences or category settings
        global $SysPrefs;
        return $SysPrefs->serial_tracking_enabled() &&
               get_item_pref('serial_tracking', $stockId);
    }

    /**
     * Validate serial numbers for a line item
     *
     * @param object $lineItem Line item
     * @param int $transType Transaction type
     * @throws InvalidArgumentException If validation fails
     */
    private function validateLineItemSerials(object $lineItem, int $transType): void
    {
        $serialNumbers = $lineItem->serial_numbers ?? [];

        if (empty($serialNumbers)) {
            throw new InvalidArgumentException(
                sprintf("Serial numbers required for item %s", $lineItem->stock_id)
            );
        }

        // Validate each serial number
        foreach ($serialNumbers as $serialNo) {
            $this->validateSerialNumber($serialNo, $lineItem->stock_id, $transType);
        }

        // Check quantity matches serial count
        if (count($serialNumbers) != $lineItem->quantity) {
            throw new InvalidArgumentException(
                sprintf("Serial number count (%d) must match quantity (%d) for item %s",
                    count($serialNumbers), $lineItem->quantity, $lineItem->stock_id)
            );
        }
    }

    /**
     * Validate a single serial number
     *
     * @param string $serialNo Serial number
     * @param string $stockId Stock item ID
     * @param int $transType Transaction type
     * @throws InvalidArgumentException If validation fails
     */
    private function validateSerialNumber(string $serialNo, string $stockId, int $transType): void
    {
        $serialItem = $this->repository->getSerialItem($stockId, $serialNo);

        if (!$serialItem) {
            throw new InvalidArgumentException(
                sprintf("Serial number %s not found for item %s", $serialNo, $stockId)
            );
        }

        // Check if serial is available for this transaction type
        if (!$this->isSerialAvailable($serialItem, $transType)) {
            throw new InvalidArgumentException(
                sprintf("Serial number %s is not available for this transaction", $serialNo)
            );
        }
    }

    /**
     * Check if serial number is available for transaction
     *
     * @param array $serialItem Serial item data
     * @param int $transType Transaction type
     * @return bool True if available
     */
    private function isSerialAvailable(array $serialItem, int $transType): bool
    {
        $status = $serialItem['status'];

        // Define availability based on transaction type and status
        switch ($transType) {
            case ST_SALESINVOICE:
            case ST_CUSTDELIVERY:
                return in_array($status, ['active', 'returned']);
            case ST_INVADJUST:
                return in_array($status, ['active', 'returned', 'loaned']);
            case ST_LOAN:
                return $status === 'active';
            default:
                return in_array($status, ['active', 'returned']);
        }
    }

    /**
     * Record movements for a line item
     *
     * @param object $lineItem Line item
     * @param object $cart Transaction cart
     * @param int $transType Transaction type
     */
    private function recordLineItemMovements(object $lineItem, object $cart, int $transType): void
    {
        $serialNumbers = $lineItem->serial_numbers ?? [];

        foreach ($serialNumbers as $serialNo) {
            $this->recordSerialMovement($serialNo, $lineItem, $cart, $transType);
        }
    }

    /**
     * Record movement for a single serial number
     *
     * @param string $serialNo Serial number
     * @param object $lineItem Line item
     * @param object $cart Transaction cart
     * @param int $transType Transaction type
     */
    private function recordSerialMovement(string $serialNo, object $lineItem, object $cart, int $transType): void
    {
        $serialItem = $this->repository->getSerialItem($lineItem->stock_id, $serialNo);

        if (!$serialItem) {
            return; // Should not happen if validation passed
        }

        $movementData = [
            'serial_item_id' => $serialItem['id'],
            'trans_type' => $transType,
            'trans_no' => $cart->trans_no ?? 0,
            'stock_id' => $lineItem->stock_id,
            'serial_no' => $serialNo,
            'location_from' => $this->getLocationFrom($transType, $cart),
            'location_to' => $this->getLocationTo($transType, $cart),
            'qty' => $lineItem->quantity,
            'reference' => $cart->reference ?? '',
        ];

        $this->repository->createMovement($movementData);

        // Update serial item status
        $this->updateSerialStatus($serialItem['id']);
    }

    /**
     * Get source location for transaction
     *
     * @param int $transType Transaction type
     * @param object $cart Transaction cart
     * @return string|null Location code
     */
    private function getLocationFrom(int $transType, object $cart): ?string
    {
        switch ($transType) {
            case ST_INVADJUST:
                return $cart->location ?? null;
            case ST_SALESINVOICE:
            case ST_CUSTDELIVERY:
                return $cart->location ?? null;
            default:
                return null;
        }
    }

    /**
     * Get destination location for transaction
     *
     * @param int $transType Transaction type
     * @param object $cart Transaction cart
     * @return string|null Location code
     */
    private function getLocationTo(int $transType, object $cart): ?string
    {
        switch ($transType) {
            case ST_INVADJUST:
                return $cart->location ?? null;
            case ST_SALESINVOICE:
            case ST_CUSTDELIVERY:
                return 'SOLD'; // Special location for sold items
            default:
                return null;
        }
    }

    /**
     * Update serial item status based on movements
     *
     * @param int $serialItemId Serial item ID
     */
    private function updateSerialStatus(int $serialItemId): void
    {
        // Determine status based on latest movement
        $latestMovement = $this->repository->getLatestMovement($serialItemId);

        if (!$latestMovement) {
            return;
        }

        $newStatus = $this->calculateSerialStatus($latestMovement);
        $this->repository->updateSerialStatus($serialItemId, $newStatus);
    }

    /**
     * Calculate serial status based on movement
     *
     * @param array $movement Movement data
     * @return string New status
     */
    private function calculateSerialStatus(array $movement): string
    {
        $transType = $movement['trans_type'];
        $locationTo = $movement['location_to'];

        switch ($transType) {
            case ST_SALESINVOICE:
            case ST_CUSTDELIVERY:
                return 'sold';
            case ST_INVADJUST:
                return $locationTo === 'SCRAP' ? 'scrapped' : 'active';
            case ST_LOAN:
                return 'loaned';
            case ST_RETURN:
                // Status will be set explicitly in the return handler
                return 'active'; // Default fallback
            case ST_DISPOSAL:
                return 'disposed';
            default:
                return 'active';
        }
    }

    /**
     * Generate a new serial number
     *
     * @param string $stockId Stock item ID
     * @param array $options Generation options
     * @return string Generated serial number
     */
    public function generateSerialNumber(string $stockId, array $options = []): string
    {
        $prefix = $options['prefix'] ?? strtoupper(substr($stockId, 0, 3));
        $timestamp = date('ymdHis');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));

        return $prefix . $timestamp . $random;
    }

    /**
     * Validate serial numbers hook
     *
     * @param array $data Validation data
     * @return array Validation result
     */
    public function validateSerialNumbers(array $data): array
    {
        try {
            $cart = $data['cart'];
            $transType = $data['trans_type'];
            $this->validateTransactionSerials($cart, $transType);
            return ['valid' => true, 'message' => ''];
        } catch (InvalidArgumentException $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Track movement hook
     *
     * @param array $data Movement data
     * @return bool Success status
     */
    public function trackMovement(array $data): bool
    {
        try {
            $cart = $data['cart'];
            $transType = $data['trans_type'];
            return $this->recordTransactionMovements($cart, $transType);
        } catch (\Exception $e) {
            error_log("Serial movement tracking failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle employee loan/issue event
     */
    public function handleEmployeeLoan(array $eventData): void
    {
        $serialNo = $eventData['serial_no'] ?? '';
        $employeeId = $eventData['employee_id'] ?? '';
        $loanDate = $eventData['loan_date'] ?? date('Y-m-d');
        $expectedReturn = $eventData['expected_return'] ?? null;
        $notes = $eventData['notes'] ?? '';

        if (empty($serialNo) || empty($employeeId)) {
            throw new InvalidArgumentException("Serial number and employee ID are required for loan");
        }

        // Update serial status to 'loaned'
        $serialItem = $this->repository->getSerialItemBySerialNo($serialNo);
        if (!$serialItem) {
            throw new InvalidArgumentException("Serial number not found: $serialNo");
        }

        $this->repository->updateSerialStatus($serialItem['id'], 'loaned');

        // Record loan movement
        $this->repository->createMovement([
            'serial_item_id' => $serialItem['id'],
            'trans_type' => ST_LOAN, // Custom transaction type for loans
            'trans_no' => $eventData['loan_id'] ?? 0,
            'stock_id' => $serialItem['stock_id'],
            'serial_no' => $serialNo,
            'location_from' => $serialItem['location'],
            'location_to' => 'EMPLOYEE_' . $employeeId,
            'qty' => 1,
            'reference' => "Loaned to employee $employeeId" . ($notes ? " - $notes" : ''),
        ]);

        // Store loan details in attributes
        $this->repository->createAttribute([
            'serial_item_id' => $serialItem['id'],
            'attribute_name' => 'employee_loan',
            'attribute_value' => json_encode([
                'employee_id' => $employeeId,
                'loan_date' => $loanDate,
                'expected_return' => $expectedReturn,
                'notes' => $notes
            ])
        ]);
    }

    /**
     * Handle employee return event
     */
    public function handleEmployeeReturn(array $eventData): void
    {
        $serialNo = $eventData['serial_no'] ?? '';
        $employeeId = $eventData['employee_id'] ?? '';
        $returnDate = $eventData['return_date'] ?? date('Y-m-d');
        $condition = $eventData['condition'] ?? 'good';
        $notes = $eventData['notes'] ?? '';

        if (empty($serialNo) || empty($employeeId)) {
            throw new InvalidArgumentException("Serial number and employee ID are required for return");
        }

        // Get serial item
        $serialItem = $this->repository->getSerialItemBySerialNo($serialNo);
        if (!$serialItem) {
            throw new InvalidArgumentException("Serial number not found: $serialNo");
        }

        // Update status based on condition
        $newStatus = $condition === 'scrapped' ? 'scrapped' : 'active';
        $this->repository->updateSerialStatus($serialItem['id'], $newStatus);

        // Record return movement
        $this->repository->createMovement([
            'serial_item_id' => $serialItem['id'],
            'trans_type' => ST_RETURN, // Custom transaction type for returns
            'trans_no' => $eventData['return_id'] ?? 0,
            'stock_id' => $serialItem['stock_id'],
            'serial_no' => $serialNo,
            'location_from' => 'EMPLOYEE_' . $employeeId,
            'location_to' => $eventData['return_location'] ?? $serialItem['location'],
            'qty' => 1,
            'reference' => "Returned by employee $employeeId - Condition: $condition" . ($notes ? " - $notes" : ''),
        ]);

        // Update loan attribute with return information
        $attributes = $this->repository->getAttributesBySerial($serialItem['id']);
        foreach ($attributes as $attribute) {
            if ($attribute['attribute_name'] === 'employee_loan') {
                $loanData = json_decode($attribute['attribute_value'], true);
                $loanData['return_date'] = $returnDate;
                $loanData['return_condition'] = $condition;
                $loanData['return_notes'] = $notes;

                $this->repository->updateAttribute($attribute['id'], json_encode($loanData));
                break;
            }
        }
    }

    /**
     * Handle asset maintenance event
     */
    public function handleAssetMaintenance(array $eventData): void
    {
        $serialNo = $eventData['serial_no'] ?? '';
        $maintenanceType = $eventData['maintenance_type'] ?? '';
        $maintenanceDate = $eventData['maintenance_date'] ?? date('Y-m-d');
        $nextDue = $eventData['next_due'] ?? null;
        $cost = $eventData['cost'] ?? 0;
        $notes = $eventData['notes'] ?? '';

        if (empty($serialNo) || empty($maintenanceType)) {
            throw new InvalidArgumentException("Serial number and maintenance type are required");
        }

        // Get serial item
        $serialItem = $this->repository->getSerialItemBySerialNo($serialNo);
        if (!$serialItem) {
            throw new InvalidArgumentException("Serial number not found: $serialNo");
        }

        // Record maintenance as a movement (special transaction type)
        $this->repository->createMovement([
            'serial_item_id' => $serialItem['id'],
            'trans_type' => ST_MAINTENANCE, // Custom transaction type for maintenance
            'trans_no' => $eventData['maintenance_id'] ?? 0,
            'stock_id' => $serialItem['stock_id'],
            'serial_no' => $serialNo,
            'location_from' => $serialItem['location'],
            'location_to' => $serialItem['location'], // Location doesn't change
            'qty' => 0, // Maintenance doesn't affect quantity
            'reference' => "Maintenance: $maintenanceType" . ($notes ? " - $notes" : ''),
        ]);

        // Store maintenance details in attributes
        $this->repository->createAttribute([
            'serial_item_id' => $serialItem['id'],
            'attribute_name' => 'maintenance_' . date('Y_m_d_H_i_s'),
            'attribute_value' => json_encode([
                'type' => $maintenanceType,
                'date' => $maintenanceDate,
                'next_due' => $nextDue,
                'cost' => $cost,
                'notes' => $notes
            ])
        ]);
    }

    /**
     * Handle asset disposal event
     */
    public function handleAssetDisposal(array $eventData): void
    {
        $serialNo = $eventData['serial_no'] ?? '';
        $disposalDate = $eventData['disposal_date'] ?? date('Y-m-d');
        $disposalMethod = $eventData['disposal_method'] ?? 'sold'; // sold, scrapped, donated, etc.
        $disposalValue = $eventData['disposal_value'] ?? 0;
        $notes = $eventData['notes'] ?? '';

        if (empty($serialNo)) {
            throw new InvalidArgumentException("Serial number is required for disposal");
        }

        // Get serial item
        $serialItem = $this->repository->getSerialItemBySerialNo($serialNo);
        if (!$serialItem) {
            throw new InvalidArgumentException("Serial number not found: $serialNo");
        }

        // Update status to disposed
        $this->repository->updateSerialStatus($serialItem['id'], 'disposed');

        // Record disposal movement
        $this->repository->createMovement([
            'serial_item_id' => $serialItem['id'],
            'trans_type' => ST_DISPOSAL, // Custom transaction type for disposal
            'trans_no' => $eventData['disposal_id'] ?? 0,
            'stock_id' => $serialItem['stock_id'],
            'serial_no' => $serialNo,
            'location_from' => $serialItem['location'],
            'location_to' => 'DISPOSED',
            'qty' => 1,
            'reference' => "Disposed via $disposalMethod" . ($notes ? " - $notes" : ''),
        ]);

        // Store disposal details in attributes
        $this->repository->createAttribute([
            'serial_item_id' => $serialItem['id'],
            'attribute_name' => 'disposal',
            'attribute_value' => json_encode([
                'date' => $disposalDate,
                'method' => $disposalMethod,
                'value' => $disposalValue,
                'notes' => $notes
            ])
        ]);
    }
}