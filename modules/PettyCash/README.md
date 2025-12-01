# PettyCash Module

## Overview

The PettyCash module provides comprehensive petty cash management capabilities for FrontAccounting. It enables organizations to track small cash expenses, manage reimbursement requests, and maintain proper documentation with receipt management. The module supports approval workflows, financial controls, and reporting for petty cash operations.

## Features

### Core Functionality
- **Petty Cash Transactions**: Track small cash expenses with approval workflows
- **Reimbursement Management**: Handle employee expense reimbursements with receipt validation
- **Receipt Management**: Attach and store digital receipts for all transactions
- **Approval Workflows**: Configurable approval processes for transactions and reimbursements
- **Financial Controls**: Balance tracking, spending limits, and budget monitoring
- **Reporting & Analytics**: Transaction summaries, reimbursement tracking, and expense analysis

### Advanced Features
- **Multi-level Approvals**: Different approval thresholds based on transaction amounts
- **Receipt Validation**: Automatic receipt requirement based on transaction type/amount
- **Payment Processing**: Multiple payment methods for reimbursements
- **Audit Trail**: Complete logging of all petty cash activities
- **Integration**: Seamless integration with employee management and financial modules

## Architecture

### Service-Oriented Design
The module follows SOLID principles with dependency injection and event-driven architecture:

- **PettyCashService**: Main service class handling all business logic
- **Entities**: PettyCashTransaction, PettyCashReimbursement, PettyCashReceipt with rich domain logic
- **Events**: PSR-14 compatible events for module integration
- **Exceptions**: Custom exception hierarchy for error handling

### Database Schema

#### petty_cash_transactions table
```sql
CREATE TABLE petty_cash_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_reference VARCHAR(20) UNIQUE NOT NULL,
    employee_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('supplies', 'meals', 'travel', 'utilities', 'maintenance', 'general') DEFAULT 'general',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    receipt_required BOOLEAN DEFAULT FALSE,
    receipt_attached BOOLEAN DEFAULT FALSE,
    approved_by INT,
    approved_at DATETIME,
    rejected_at DATETIME,
    rejection_reason TEXT,
    transaction_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_date (transaction_date),
    INDEX idx_category (category),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

#### petty_cash_reimbursements table
```sql
CREATE TABLE petty_cash_reimbursements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reimbursement_reference VARCHAR(20) UNIQUE NOT NULL,
    employee_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('supplies', 'meals', 'travel', 'utilities', 'maintenance', 'general') DEFAULT 'general',
    expense_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'paid', 'rejected') DEFAULT 'pending',
    receipts_attached BOOLEAN DEFAULT FALSE,
    approved_by INT,
    approved_at DATETIME,
    paid_at DATETIME,
    payment_method ENUM('cash', 'bank_transfer', 'check', 'credit_card') DEFAULT 'cash',
    payment_reference VARCHAR(50),
    processed_by INT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_expense_date (expense_date),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);
```

#### petty_cash_receipts table
```sql
CREATE TABLE petty_cash_receipts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_by INT,
    uploaded_at DATETIME NOT NULL,
    INDEX idx_transaction (transaction_id),
    FOREIGN KEY (transaction_id) REFERENCES petty_cash_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
```

#### petty_cash_reimbursement_receipts table
```sql
CREATE TABLE petty_cash_reimbursement_receipts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reimbursement_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    amount DECIMAL(10,2),
    uploaded_at DATETIME NOT NULL,
    INDEX idx_reimbursement (reimbursement_id),
    FOREIGN KEY (reimbursement_id) REFERENCES petty_cash_reimbursements(id) ON DELETE CASCADE
);
```

## API Usage

### Basic Transaction Operations

#### Create a Petty Cash Transaction
```php
use FA\Modules\PettyCash\PettyCashService;

$transactionData = [
    'employee_id' => 1,
    'amount' => 25.50,
    'description' => 'Office supplies for quarterly review',
    'category' => 'supplies',
    'receipt_required' => true,
    'transaction_date' => '2025-11-30'
];

$transaction = $service->createTransaction($transactionData);
```

#### Approve a Transaction
```php
$transaction = $service->approveTransaction($transactionId, $approverId);
```

#### Attach Receipt to Transaction
```php
$receiptData = [
    'file_path' => '/uploads/receipts/receipt_001.pdf',
    'file_name' => 'receipt_001.pdf',
    'file_size' => 245760,
    'mime_type' => 'application/pdf',
    'uploaded_by' => 1
];

$receipt = $service->attachReceipt($transactionId, $receiptData);
```

### Reimbursement Management

#### Create a Reimbursement Request
```php
$reimbursementData = [
    'employee_id' => 1,
    'amount' => 75.50,
    'description' => 'Business lunch with client',
    'category' => 'meals',
    'expense_date' => '2025-11-25',
    'receipts' => [
        [
            'file_path' => '/receipts/lunch_receipt1.pdf',
            'file_name' => 'lunch_receipt1.pdf',
            'amount' => 45.00
        ],
        [
            'file_path' => '/receipts/lunch_receipt2.pdf',
            'file_name' => 'lunch_receipt2.pdf',
            'amount' => 30.50
        ]
    ]
];

$reimbursement = $service->createReimbursement($reimbursementData);
```

#### Process Reimbursement Payment
```php
$paymentData = [
    'payment_method' => 'bank_transfer',
    'payment_reference' => 'PAY-2025-001',
    'processed_by' => 3
];

$reimbursement = $service->processPayment($reimbursementId, $paymentData);
```

### Reporting & Analytics

#### Get Petty Cash Balance
```php
$balance = $service->getPettyCashBalance();
```

#### Get Monthly Summary
```php
$summary = $service->getMonthlySummary('2025-11');
// Returns transaction counts, approved amounts, reimbursement data
```

#### Get Employee Transactions
```php
$transactions = $service->getTransactionsByEmployee($employeeId, 10);
```

#### Get Pending Reimbursements
```php
$pendingReimbursements = $service->getPendingReimbursements(50);
```

## Events

The module fires PSR-14 compatible events for integration:

- `PettyCashTransactionCreatedEvent`: When a transaction is created
- `PettyCashTransactionApprovedEvent`: When a transaction is approved
- `PettyCashTransactionRejectedEvent`: When a transaction is rejected
- `PettyCashReceiptAttachedEvent`: When a receipt is attached
- `PettyCashReimbursementCreatedEvent`: When a reimbursement is created
- `PettyCashReimbursementApprovedEvent`: When a reimbursement is approved
- `PettyCashReimbursementPaidEvent`: When a reimbursement is paid

### Event Listener Example
```php
use FA\Modules\PettyCash\Events\PettyCashTransactionApprovedEvent;

class PettyCashTransactionListener
{
    public function __invoke(PettyCashTransactionApprovedEvent $event): void
    {
        $transaction = $event->getTransaction();

        // Update petty cash balance
        $this->updatePettyCashBalance($transaction);

        // Send notification to employee
        $this->notifyEmployee($transaction);

        // Log for accounting
        $this->logForAccounting($transaction);
    }
}
```

## Exception Handling

The module provides specific exception types:

- `PettyCashTransactionNotFoundException`: Transaction not found
- `PettyCashReimbursementNotFoundException`: Reimbursement not found
- `PettyCashValidationException`: Validation errors with field-specific messages
- `PettyCashBusinessRuleException`: Business rule violations
- `PettyCashInsufficientFundsException`: Insufficient petty cash funds
- `PettyCashFileUploadException`: File upload errors
- `PettyCashPermissionException`: Permission denied
- `PettyCashStatusTransitionException`: Invalid status transitions

### Exception Handling Example
```php
use FA\Modules\PettyCash\PettyCashValidationException;
use FA\Modules\PettyCash\PettyCashInsufficientFundsException;

try {
    $transaction = $service->createTransaction($data);
} catch (PettyCashValidationException $e) {
    foreach ($e->getValidationErrors() as $field => $message) {
        echo "Validation error for {$field}: {$message}\n";
    }
} catch (PettyCashInsufficientFundsException $e) {
    echo "Insufficient funds. Available: $" . $e->getAvailableBalance() . "\n";
} catch (PettyCashException $e) {
    echo "Petty cash error: " . $e->getMessage();
}
```

## Business Rules

### Transaction Approval Rules
- Transactions ≤ $25 automatically approved (no receipt required)
- Transactions > $25 require receipt and approval
- Office supplies, utilities, maintenance always require receipts
- Travel and meal expenses require detailed receipts

### Reimbursement Rules
- Reimbursements > $50 require pre-approval
- All reimbursements require receipts
- Processing time limit: 30 days from expense date
- Payment methods: cash, bank transfer, check, credit card

### Receipt Requirements
- Digital receipts preferred (PDF, images)
- File size limit: 5MB per receipt
- Supported formats: PDF, JPG, PNG, GIF
- Automatic validation for file type and size

## Integration Points

### With Employee Management
- Link transactions and reimbursements to employee records
- Track employee spending patterns and limits
- Generate employee expense reports

### With Financial Modules
- Automatic journal entries for approved transactions
- Petty cash account balance tracking
- Integration with accounts payable for reimbursements

### With Approval Workflows
- Configurable approval hierarchies
- Email notifications for pending approvals
- Escalation rules for overdue approvals

### With Document Management
- Receipt storage and retrieval
- Document versioning for receipts
- Integration with OCR for receipt data extraction

## Configuration

### Module Registration
Add to your module configuration:

```php
'PettyCash' => [
    'class' => \FA\Modules\PettyCash\PettyCashService::class,
    'dependencies' => [
        'dbal' => \FA\Database\DBALInterface::class,
        'eventDispatcher' => \Psr\EventDispatcher\EventDispatcherInterface::class,
        'logger' => \Psr\Log\LoggerInterface::class
    ]
]
```

### Approval Thresholds Configuration
```php
'petty_cash' => [
    'auto_approve_limit' => 25.00,
    'receipt_required_limit' => 25.00,
    'reimbursement_approval_limit' => 50.00,
    'categories_requiring_receipts' => ['meals', 'travel', 'supplies'],
    'processing_deadline_days' => 30
]
```

### Permission Setup
Required permissions:
- `petty_cash_transaction_create`: Create transactions
- `petty_cash_transaction_approve`: Approve transactions
- `petty_cash_reimbursement_create`: Create reimbursements
- `petty_cash_reimbursement_approve`: Approve reimbursements
- `petty_cash_reimbursement_pay`: Process payments
- `petty_cash_reports`: Access reports
- `petty_cash_admin`: Administrative functions

## Testing

The module includes comprehensive unit tests covering:
- Transaction CRUD operations
- Approval and rejection workflows
- Receipt attachment and validation
- Reimbursement processing
- Business rule enforcement
- Exception scenarios

Run tests with:
```bash
phpunit modules/PettyCash/tests/
```

## Performance Considerations

- Database indexes on frequently queried fields (employee_id, status, dates)
- Efficient queries with proper JOIN operations for reporting
- File storage optimization for receipt management
- Caching strategies for balance calculations
- Background processing for bulk approvals and payments

## Security

- Role-based access control for all operations
- File upload security with type and size validation
- Audit logging for all financial transactions
- Data encryption for sensitive receipt information
- Permission checks for approval and payment operations

## Future Enhancements

- Mobile app for receipt capture and submission
- OCR integration for automatic receipt data extraction
- Integration with credit card feeds for automatic reconciliation
- Advanced approval workflows with multiple approvers
- Machine learning for expense categorization and fraud detection
- Real-time balance monitoring and alerts
- Integration with travel booking systems
- Multi-currency support for international expenses