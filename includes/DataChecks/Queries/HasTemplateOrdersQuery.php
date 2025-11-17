<?php

namespace FA\DataChecks\Queries;

use FA\Contracts\DatabaseQueryInterface;

/**
 * Query for template orders (sales orders with type = 1)
 */
class HasTemplateOrdersQuery
{
    private DatabaseQueryInterface $db;

    public function __construct(DatabaseQueryInterface $db)
    {
        $this->db = $db;
    }

    public function exists(): bool
    {
        $sql = "SELECT sorder.order_no 
            FROM " . \TB_PREF . "sales_orders as sorder,"
                . \TB_PREF . "sales_order_details as line
            WHERE sorder.order_no = line.order_no AND sorder.type = 1
            GROUP BY line.order_no";

        return $this->db->hasRows($sql);
    }
}
