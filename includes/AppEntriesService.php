<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
namespace FA;

/**
 * App Entries Service
 *
 * Manages application entry points for transaction editors.
 * Refactored to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages transaction editor URLs only
 * - Open/Closed: Can be extended for additional editors
 * - Liskov Substitution: Compatible with array access
 * - Interface Segregation: Focused on editor retrieval
 * - Dependency Inversion: No hard dependencies
 *
 * DRY: Centralizes editor URL logic
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | AppEntriesService  |
 * +---------------------+
 * | - transEditors: array |
 * +---------------------+
 * | + __construct()    |
 * | + getEditorUrl()   |
 * | + hasEditor()      |
 * +---------------------+
 *
 * @package FA
 */
class AppEntriesService {

    private array $transEditors;

    /**
     * Constructor
     */
    public function __construct() {
        $this->transEditors = array(
            \ST_JOURNAL => "/gl/gl_journal.php?ModifyGL=Yes&trans_no=%d&trans_type=%d",
            \ST_BANKPAYMENT => "/gl/gl_bank.php?ModifyPayment=Yes&trans_no=%d&trans_type=%d",
            \ST_BANKDEPOSIT => "/gl/gl_bank.php?ModifyDeposit=Yes&trans_no=%d&trans_type=%d",
            //ST_BANKTRANSFER => ,

            \ST_SALESINVOICE => "/sales/customer_invoice.php?ModifyInvoice=%d",
            //   11=>
            // free hand (debtors_trans.order_==0)
            //	"/sales/credit_note_entry.php?ModifyCredit=%d"
            // credit invoice
            //	"/sales/customer_credit_invoice.php?ModifyCredit=%d"
            \ST_CUSTCREDIT =>  "/sales/customer_credit_invoice.php?ModifyCredit=%s",
            \ST_CUSTPAYMENT =>  "/sales/customer_payments.php?trans_no=%d",
            \ST_CUSTDELIVERY => "/sales/customer_delivery.php?ModifyDelivery=%d",

            //ST_LOCTRANSFER =>  ,
            //ST_INVADJUST =>  ,

            \ST_PURCHORDER =>  "/purchasing/po_entry_items.php?ModifyOrderNumber=%d",
            \ST_SUPPINVOICE => "/purchasing/supplier_invoice.php?ModifyInvoice=%d",

            //ST_SUPPCREDIT =>  ,
            //ST_SUPPAYMENT =>  ,
            //ST_SUPPRECEIVE => ,

            //ST_WORKORDER =>  ,
            //ST_MANUISSUE =>  ,
            //ST_MANURECEIVE =>  ,

            \ST_SALESORDER => "/sales/sales_order_entry.php?ModifyOrderNumber=%d",
            \ST_SALESQUOTE => "/sales/sales_order_entry.php?ModifyQuotationNumber=%d",
            //ST_COSTUPDATE =>  ,
            //ST_DIMENSION =>  ,
        );
    }

    /**
     * Get the editor URL for a transaction type
     *
     * @param int $transType Transaction type
     * @param int|string $transNo Transaction number
     * @return string|null Editor URL or null if not found
     */
    public function getEditorUrl(int $transType, $transNo): ?string {
        if (!isset($this->transEditors[$transType])) {
            return null;
        }
        return sprintf($this->transEditors[$transType], $transNo, $transType);
    }

    /**
     * Check if an editor exists for the transaction type
     *
     * @param int $transType Transaction type
     * @return bool True if editor exists
     */
    public function hasEditor(int $transType): bool {
        return isset($this->transEditors[$transType]);
    }

    /**
     * Get all transaction editors
     *
     * @return array Transaction editors array
     */
    public function getAllEditors(): array {
        return $this->transEditors;
    }
}