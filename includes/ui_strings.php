<?php
/**
 * UI string constants for multilingual readiness.
 */

if (!defined('UI_TEXT_GL_INQUIRY_TITLE')) {
    define('UI_TEXT_GL_INQUIRY_TITLE', 'General Ledger Inquiry');
    define('UI_TEXT_ACCOUNT_LABEL', 'Account:');
    define('UI_TEXT_ALL_ACCOUNTS', 'All Accounts');
    define('UI_TEXT_FROM_LABEL', 'from:');
    define('UI_TEXT_TO_LABEL', 'to:');
    define('UI_TEXT_DIMENSION', 'Dimension');
    define('UI_TEXT_MEMO_PLACEHOLDER', 'Enter memo fragment or leave empty');
    define('UI_TEXT_AMOUNT_MIN', 'Amount min:');
    define('UI_TEXT_AMOUNT_MAX', 'Amount max:');
    define('UI_TEXT_SHOW', 'Show');
    define('UI_TEXT_TYPE', 'Type');
    define('UI_TEXT_NUMBER', '#');
    define('UI_TEXT_REFERENCE', 'Reference');
    define('UI_TEXT_DATE', 'Date');
    define('UI_TEXT_ACCOUNT_COLUMN', 'Account');
    define('UI_TEXT_PERSON_ITEM', 'Person/Item');
    define('UI_TEXT_DEBIT', 'Debit');
    define('UI_TEXT_CREDIT', 'Credit');
    define('UI_TEXT_BALANCE', 'Balance');
    define('UI_TEXT_MEMO', 'Memo');
    define('UI_TEXT_OPENING_BALANCE', 'Opening Balance');
    define('UI_TEXT_ENDING_BALANCE', 'Ending Balance');
    define('UI_TEXT_WARNING_OPENING_MISMATCH', 'The Opening Balance is not in balance, probably due to a non closed Previous Fiscalyear.');
    define('UI_TEXT_GL_LABEL', 'GL');
    define('UI_TEXT_SELECT', 'Select');
    define('UI_TEXT_VOID_TRANSACTION_TITLE', 'Void a Transaction');
    define('UI_TEXT_TRANSACTION_TYPE_LABEL', 'Transaction Type:');
    define('UI_TEXT_SEARCH', 'Search');
    define('UI_TEXT_MARKED_WILL_BE_VOIDED', 'Marked transactions will be voided.');
    define('UI_TEXT_TRANSACTION_NUMBER', 'Transaction #:');
    define('UI_TEXT_VOIDING_DATE', 'Voiding Date:');
    define('UI_TEXT_MEMO_LABEL', 'Memo:');
    define('UI_TEXT_VOID_TRANSACTION_BUTTON', 'Void Transaction');
    define('UI_TEXT_ENTERED_TRANSACTION_NOT_FOUND', 'The entered transaction does not exist or cannot be voided.');
    define('UI_TEXT_INSUFFICIENT_QUANTITY_WARNING', 'The void cannot be processed because there is an insufficient quantity for item:');
    define('UI_TEXT_QUANTITY_ON_HAND', 'Quantity On Hand');
    define('UI_TEXT_VOID_CONFIRMATION', 'Are you sure you want to void this transaction ? This action cannot be undone.');
    define('UI_TEXT_VOID_PROCEED', 'Proceed');
    define('UI_TEXT_CANCEL', 'Cancel');
    define('UI_TEXT_CLOSED_TRANSACTION_ERROR', 'The selected transaction was closed for edition and cannot be voided.');
    define('UI_TEXT_INVALID_DATE_ERROR', 'The entered date is invalid.');
    define('UI_TEXT_DATE_OUT_OF_FISCAL_YEAR_ERROR', 'The entered date is out of fiscal year or is closed for further data entry.');
    define('UI_TEXT_NUMERIC_TRANSACTION_NUMBER_ERROR', 'The transaction number is expected to be numeric and greater than zero.');
    define('UI_TEXT_ALREADY_VOIDED_ERROR', 'The selected transaction has already been voided.');
    define('UI_TEXT_TRANSACTION_VOIDED_NOTICE', 'Selected transaction has been voided.');
}
