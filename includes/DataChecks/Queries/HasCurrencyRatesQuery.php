<?php

namespace FA\DataChecks\Queries;

use FA\Contracts\DatabaseQueryInterface;

/**
 * Query to check if currency rates exist for date
 * 
 * Complex parameterized query - takes currency and date
 */
class HasCurrencyRatesQuery
{
    private DatabaseQueryInterface $db;
    private string $currency;
    private string $date;

    public function __construct(DatabaseQueryInterface $db, string $currency, string $date)
    {
        $this->db = $db;
        $this->currency = $currency;
        $this->date = $date;
    }

    public function exists(): bool
    {
        $escapedCurrency = $this->db->escape($this->currency);
        $escapedDate = $this->db->escape($this->date);
        
        $sql = "SELECT COUNT(*) FROM exchange_rates 
                WHERE curr_code = '$escapedCurrency' 
                AND date_ <= '$escapedDate' 
                ORDER BY date_ DESC LIMIT 1";
                
        $result = $this->db->query($sql);
        $row = $this->db->fetchRow($result);
        return (int)$row[0] > 0;
    }
}
