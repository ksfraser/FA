<?php
declare(strict_types=1);

namespace FA\Modules;

use FA\Modules\ModuleInterface;

/**
 * Customer Module
 *
 * Provides customer management functionality including sales orders, invoices, payments, etc.
 */
class CustomerModule implements ModuleInterface
{
    public function getName(): string
    {
        return 'customers';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Customer management module providing sales orders, invoices, payments, and customer relationship management.';
    }

    public function getAuthor(): string
    {
        return 'FrontAccounting Team';
    }

    public function getMinimumAppVersion(): string
    {
        return '2.5.0';
    }

    public function getMaximumAppVersion(): ?string
    {
        return null;
    }

    public function getDependencies(): array
    {
        return ['inventory', 'taxes']; // Depends on inventory and tax modules
    }

    public function getMenuItems(): array
    {
        return [
            [
                'module' => 'Transactions',
                'items' => [
                    [
                        'title' => 'Sales Quotation Entry',
                        'url' => 'sales/sales_order_entry.php?NewQuotation=Yes',
                        'access' => 'SA_SALESQUOTE',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Sales Order Entry',
                        'url' => 'sales/sales_order_entry.php?NewOrder=Yes',
                        'access' => 'SA_SALESORDER',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Direct Delivery',
                        'url' => 'sales/sales_order_entry.php?NewDelivery=0',
                        'access' => 'SA_SALESDELIVERY',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Direct Invoice',
                        'url' => 'sales/sales_order_entry.php?NewInvoice=0',
                        'access' => 'SA_SALESINVOICE',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Delivery Against Sales Orders',
                        'url' => 'sales/inquiry/sales_orders_view.php?OutstandingOnly=1',
                        'access' => 'SA_SALESDELIVERY',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Invoice Against Sales Delivery',
                        'url' => 'sales/inquiry/sales_deliveries_view.php?OutstandingOnly=1',
                        'access' => 'SA_SALESINVOICE',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Customer Payments',
                        'url' => 'sales/customer_payments.php',
                        'access' => 'SA_SALESPAYMNT',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Customer Credit Notes',
                        'url' => 'sales/credit_note_entry.php?NewCredit=Yes',
                        'access' => 'SA_SALESCREDIT',
                        'type' => 'transaction'
                    ],
                    [
                        'title' => 'Allocate Customer Payments/Credit Notes',
                        'url' => 'sales/allocations/customer_allocation_main.php',
                        'access' => 'SA_SALESALLOC',
                        'type' => 'transaction'
                    ]
                ]
            ],
            [
                'module' => 'Inquiries and Reports',
                'items' => [
                    [
                        'title' => 'Customer Inquiry',
                        'url' => 'sales/inquiry/customer_inquiry.php',
                        'access' => 'SA_SALESTRANSVIEW',
                        'type' => 'inquiry'
                    ],
                    [
                        'title' => 'Customer Details',
                        'url' => 'sales/manage/customers.php',
                        'access' => 'SA_CUSTOMER',
                        'type' => 'maintenance'
                    ],
                    [
                        'title' => 'Customer Branches',
                        'url' => 'sales/manage/customer_branches.php',
                        'access' => 'SA_CUSTOMER',
                        'type' => 'maintenance'
                    ],
                    [
                        'title' => 'Sales Groups',
                        'url' => 'sales/manage/sales_groups.php',
                        'access' => 'SA_SALESGROUP',
                        'type' => 'maintenance'
                    ],
                    [
                        'title' => 'Recurrent Invoices',
                        'url' => 'sales/manage/recurrent_invoices.php',
                        'access' => 'SA_SRECURRENT',
                        'type' => 'maintenance'
                    ],
                    [
                        'title' => 'Customer Transaction Report',
                        'url' => 'reporting/reports_main.php?Class=0',
                        'access' => 'SA_CUSTOMER',
                        'type' => 'report'
                    ]
                ]
            ]
        ];
    }

    public function getPermissions(): array
    {
        return [
            'SA_SALESQUOTE' => ['description' => 'Sales quotations', 'section' => 'Sales'],
            'SA_SALESORDER' => ['description' => 'Sales orders', 'section' => 'Sales'],
            'SA_SALESDELIVERY' => ['description' => 'Sales deliveries', 'section' => 'Sales'],
            'SA_SALESINVOICE' => ['description' => 'Sales invoices', 'section' => 'Sales'],
            'SA_SALESPAYMNT' => ['description' => 'Customer payments', 'section' => 'Sales'],
            'SA_SALESCREDIT' => ['description' => 'Customer credit notes', 'section' => 'Sales'],
            'SA_SALESALLOC' => ['description' => 'Customer allocations', 'section' => 'Sales'],
            'SA_SALESTRANSVIEW' => ['description' => 'View sales transactions', 'section' => 'Sales'],
            'SA_CUSTOMER' => ['description' => 'Customer maintenance', 'section' => 'Sales'],
            'SA_SALESGROUP' => ['description' => 'Sales groups maintenance', 'section' => 'Sales'],
            'SA_SRECURRENT' => ['description' => 'Recurrent invoices', 'section' => 'Sales']
        ];
    }

    public function activate(): bool
    {
        // Register event listeners for customer-related events
        \FA\Services\EventManager::on('DatabasePreWriteEvent', [$this, 'onPreCustomerWrite']);
        \FA\Services\EventManager::on('DatabasePostWriteEvent', [$this, 'onPostCustomerWrite']);

        // Add custom fields for customers
        \FA\CustomFields\CustomFieldManager::getInstance()->createField('customers', [
            'field_name' => 'customer_type',
            'field_label' => 'Customer Type',
            'field_type' => 'select',
            'select_options' => json_encode(['retail' => 'Retail', 'wholesale' => 'Wholesale', 'corporate' => 'Corporate']),
            'is_required' => false,
            'display_order' => 1
        ]);

        return true;
    }

    public function deactivate(): bool
    {
        // Unregister event listeners
        // Note: Current EventManager doesn't support removing listeners
        return true;
    }

    public function install(): bool
    {
        // Create any module-specific database tables if needed
        // For customers, most tables already exist
        return true;
    }

    public function uninstall(): bool
    {
        // Remove custom fields created by this module
        // Clean up any module-specific data
        return true;
    }

    public function upgrade(string $oldVersion, string $newVersion): bool
    {
        // Handle version upgrades
        return true;
    }

    /**
     * Event handler for pre-customer database write
     */
    public function onPreCustomerWrite(\FA\Events\DatabasePreWriteEvent $event): void
    {
        // Validate customer data before saving
        // if ($event->getTransactionType() === ST_SALESINVOICE) {
            // Custom validation logic
        // }
    }

    /**
     * Event handler for post-customer database write
     */
    public function onPostCustomerWrite(\FA\Events\DatabasePostWriteEvent $event): void
    {
        // Post-processing after customer transaction
        // if ($event->getTransactionType() === ST_SALESINVOICE) {
            // Send notifications, update external systems, etc.
        // }
    }
}