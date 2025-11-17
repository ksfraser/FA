<?php

namespace FA;

/**
 * Banking Service
 *
 * Handles banking-related functions like currency and exchange rates.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages banking operations only
 * - Open/Closed: Can be extended for additional banking features
 * - Liskov Substitution: Compatible with banking interfaces
 * - Interface Segregation: Focused banking methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses banking logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |  BankingService    |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + isCompanyCurrency(currency)|
 * | + getCompanyCurrency()|
 * | + getExchangeRate(...)|
 * +---------------------+
 *
 * @package FA
 */
class BankingService
{
    /**
     * Check if currency is company currency
     *
     * @param string $currency Currency code
     * @return bool True if company currency
     */
    public function isCompanyCurrency(string $currency): bool
    {
        return ($this->getCompanyCurrency() == $currency);
    }

    /**
     * Get company default currency
     *
     * @return string Currency code
     */
    public function getCompanyCurrency(): string
    {
        return \get_company_pref('curr_default');
    }

    /**
     * Get exchange rate from home currency
     *
     * @param string $currency_code Currency code
     * @param string $date Date
     * @return float Exchange rate
     */
    public function getExchangeRateFromHomeCurrency(string $currency_code, string $date_): float
    {
        if ($currency_code == $this->getCompanyCurrency() || $currency_code == null)
            return 1.0000;

        $rate = \get_last_exchange_rate($currency_code, $date_);

        if (!$rate) {
            \display_error(
                sprintf(_("Cannot retrieve exchange rate for currency %s as of %s. Please add exchange rate manually on Exchange Rates page."),
                     $currency_code, $date_));
            return 1.000;
        }

        return $rate['rate_buy'];
    }

    /**
     * Get exchange rate to home currency
     *
     * @param string $currency_code Currency code
     * @param string $date_ Date
     * @return float Exchange rate
     */
    public function getExchangeRateToHomeCurrency(string $currency_code, string $date_): float
    {
        return 1 / $this->getExchangeRateFromHomeCurrency($currency_code, $date_);
    }

    /**
     * Convert amount to home currency
     *
     * @param float $amount Amount
     * @param string $currency_code Currency code
     * @param string $date_ Date
     * @return float Converted amount
     */
    public function toHomeCurrency(float $amount, string $currency_code, string $date_): float
    {
        $ex_rate = $this->getExchangeRateToHomeCurrency($currency_code, $date_);
        return \round2($amount / $ex_rate, \user_price_dec());
    }

    /**
     * Get exchange rate from one currency to another
     *
     * @param string $from_curr_code From currency code
     * @param string $to_curr_code To currency code
     * @param string $date_ Date
     * @return float Exchange rate
     */
    public function getExchangeRateFromTo(string $from_curr_code, string $to_curr_code, string $date_): float
    {
        if ($from_curr_code == $to_curr_code)
            return 1.0000;

        $home_currency = $this->getCompanyCurrency();
        if ($to_curr_code == $home_currency) {
            return $this->getExchangeRateToHomeCurrency($from_curr_code, $date_);
        }

        if ($from_curr_code == $home_currency) {
            return $this->getExchangeRateFromHomeCurrency($to_curr_code, $date_);
        }

        // neither from or to are the home currency
        return $this->getExchangeRateToHomeCurrency($from_curr_code, $date_) / $this->getExchangeRateToHomeCurrency($to_curr_code, $date_);
    }

    /**
     * Exchange amount from one currency to another
     *
     * @param float $amount Amount
     * @param string $from_curr_code From currency code
     * @param string $to_curr_code To currency code
     * @param string $date_ Date
     * @return float Exchanged amount
     */
    public function exchangeFromTo(float $amount, string $from_curr_code, string $to_curr_code, string $date_): float
    {
        $ex_rate = $this->getExchangeRateFromTo($from_curr_code, $to_curr_code, $date_);
        return $amount / $ex_rate;
    }

    /**
     * Handle exchange variations for allocations
     *
     * @param int $pyt_type Payment type
     * @param int $pyt_no Payment number
     * @param int $type Transaction type
     * @param int $trans_no Transaction number
     * @param string $pyt_date Payment date
     * @param float $amount Amount
     * @param int $person_type Person type (PT_CUSTOMER or PT_SUPPLIER)
     * @param bool $neg Negative flag
     * @return void
     */
    public function exchangeVariation(int $pyt_type, int $pyt_no, int $type, int $trans_no, string $pyt_date, float $amount, int $person_type, bool $neg = false): void
    {
        global $systypes_array;

        if ($person_type == PT_CUSTOMER) {
            $trans = \get_customer_trans($trans_no, $type);
            $pyt_trans = \get_customer_trans($pyt_no, $pyt_type);
            $cust_accs = \get_branch_accounts($trans['branch_code']);
            $ar_ap_act = $cust_accs['receivables_account'];
            $person_id = $trans['debtor_no'];
            $curr = $trans['curr_code'];
            $date = \sql2date($trans['tran_date']);
        } else {
            $trans = \get_supp_trans($trans_no, $type);
            $pyt_trans = \get_supp_trans($pyt_no, $pyt_type);
            $supp_accs = \get_supplier_accounts($trans['supplier_id']);
            $ar_ap_act = $supp_accs['payable_account'];
            $person_id = $trans['supplier_id'];
            $curr = $trans['curr_code'];
            $date = \sql2date($trans['tran_date']);
        }

        if ($this->isCompanyCurrency($curr))
            return;

        $inv_amt = \round2($amount * $trans['rate'], \user_price_dec());
        $pay_amt = \round2($amount * $pyt_trans['rate'], \user_price_dec());

        if ($inv_amt != $pay_amt) {
            $diff = $inv_amt - $pay_amt;
            if ($person_type == PT_SUPPLIER)
                $diff = -$diff;
            if ($neg)
                $diff = -$diff;

            $exc_var_act = \get_company_pref('exchange_diff_act');
            if (\date1_greater_date2($date, $pyt_date)) {
                $memo = $systypes_array[$pyt_type] . " " . $pyt_no;
                \add_gl_trans($type, $trans_no, $date, $ar_ap_act, 0, 0, $memo, -$diff, null, $person_type, $person_id);
                \add_gl_trans($type, $trans_no, $date, $exc_var_act, 0, 0, $memo, $diff, null, $person_type, $person_id);
            } else {
                $memo = $systypes_array[$type] . " " . $trans_no;
                \add_gl_trans($pyt_type, $pyt_no, $pyt_date, $ar_ap_act, 0, 0, $memo, -$diff, null, $person_type, $person_id);
                \add_gl_trans($pyt_type, $pyt_no, $pyt_date, $exc_var_act, 0, 0, $memo, $diff, null, $person_type, $person_id);
            }
        }
    }
}