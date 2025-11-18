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
 * Tax Calculation Service
 *
 * Handles tax calculations for items and transactions.
 * Refactored to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages tax calculations only
 * - Open/Closed: Can be extended for additional tax logic
 * - Liskov Substitution: Compatible with tax interfaces
 * - Interface Segregation: Focused tax methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses tax calculation logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | TaxCalculationService |
 * +---------------------+
 * |                     |
 * +---------------------+
 * | + getTaxFreePriceForItem() |
 * | + getFullPriceForItem()    |
 * | + getTaxesForItem()        |
 * | + getTaxForItems()         |
 * +---------------------+
 *
 * @package FA
 */
class TaxCalculationService {

    /**
     * Constructor
     */
    public function __construct() {
        // Include necessary db files
        include_once($path_to_root . "/taxes/db/tax_groups_db.inc");
        include_once($path_to_root . "/taxes/db/tax_types_db.inc");
        include_once($path_to_root . "/taxes/db/item_tax_types_db.inc");
    }

    /**
     * Returns the price of a given item minus any included taxes
     *
     * @param string $stockId Stock ID
     * @param float $price Line price
     * @param int $taxGroup Tax group ID
     * @param int $taxIncluded Tax included flag
     * @param array|null $taxGroupArray Tax group array
     * @return float Tax-free price
     */
    public function getTaxFreePriceForItem(string $stockId, float $price, int $taxGroup, int $taxIncluded, ?array $taxGroupArray = null): float {
        // if price is zero, then can't be taxed !
        if ($price == 0) {
            return 0;
        }

        if ($taxIncluded == 0) {
            return $price;
        }

        // if array already read, then make a copy and use that
        if ($taxGroupArray) {
            $retTaxArray = $taxGroupArray;
        } else {
            $retTaxArray = get_tax_group_items_as_array($taxGroup);
        }

        $taxArray = $this->getTaxesForItem($stockId, $retTaxArray);

        // if no exemptions or taxgroup is empty, then no included/excluded taxes
        if ($taxArray == null) {
            return $price;
        }

        // to avoid rounding errors we have to just subtract taxes from tax_included price.
        $taxMultiplier = 0;
        foreach ($taxArray as $taxitem) {
            $taxMultiplier += $taxitem["rate"];
        }

        $tax = 0;
        foreach ($taxArray as $taxitem) {
            $tax += round($price * $taxitem['rate'] / (100 + $taxMultiplier), \FA\UserPrefsCache::getPriceDecimals());
        }
        return $price - $tax;
    }

    /**
     * Full price (incl. VAT) for item
     *
     * @param string $stockId Stock ID
     * @param float $price Line price
     * @param int $taxGroup Tax group ID
     * @param int $taxIncluded Tax included flag
     * @param array|null $taxGroupArray Tax group array
     * @return float Full price
     */
    public function getFullPriceForItem(string $stockId, float $price, int $taxGroup, int $taxIncluded, ?array $taxGroupArray = null): float {
        // if price is zero, then can't be taxed !
        if ($price == 0) {
            return 0;
        }

        if ($taxIncluded == 1) {
            return $price;
        }

        // if array already read, then make a copy and use that
        if ($taxGroupArray) {
            $retTaxArray = $taxGroupArray;
        } else {
            $retTaxArray = get_tax_group_items_as_array($taxGroup);
        }

        $taxArray = $this->getTaxesForItem($stockId, $retTaxArray);
        // if no exemptions or taxgroup is empty, then no included/excluded taxes
        if ($taxArray == null) {
            return $price;
        }

        $taxMultiplier = 0;

        // loop for all items
        foreach ($taxArray as $taxitem) {
            $taxMultiplier += $taxitem["rate"];
        }

        return round($price * (1 + ($taxMultiplier / 100)), \FA\UserPrefsCache::getPriceDecimals());
    }

    /**
     * Return an array of taxes for item
     *
     * @param string $stockId Stock ID
     * @param array $taxGroupItemsArray Tax group items array
     * @return array|null Tax array
     */
    public function getTaxesForItem(string $stockId, array $taxGroupItemsArray): ?array {
        $itemTaxType = get_item_tax_type_for_item($stockId);

        // if the item is exempt from all taxes then return 0
        if ($itemTaxType["exempt"]) {
            return null;
        }

        // get the exemptions for this item tax type
        $itemTaxTypeExemptionsDb = get_item_tax_type_exemptions($itemTaxType["id"]);

        // read them all into an array to minimize db querying
        $itemTaxTypeExemptions = array();
        while ($itemTaxTypeExemp = db_fetch($itemTaxTypeExemptionsDb)) {
            $itemTaxTypeExemptions[] = $itemTaxTypeExemp["tax_type_id"];
        }

        $retTaxArray = array();

        // if any of the taxes of the tax group are in the exemptions, then skip
        foreach ($taxGroupItemsArray as $taxGroupItem) {
            $skip = false;

            // if it's in the exemptions, skip
            foreach ($itemTaxTypeExemptions as $exemption) {
                if (($taxGroupItem['tax_type_id'] == $exemption)) {
                    $skip = true;
                    break;
                }
            }

            if (!$skip) {
                $index = $taxGroupItem['tax_type_id'];
                $retTaxArray[$index] = $taxGroupItem;
            }
        }

        return $retTaxArray;
    }

    /**
     * Return an array of taxes for items
     *
     * @param array $items Items array
     * @param array $prices Prices array
     * @param float $shippingCost Shipping cost
     * @param int $taxGroup Tax group ID
     * @param int|null $taxIncluded Tax included flag
     * @param array|null $taxItemsArray Tax items array
     * @param int|null $taxAlgorithm Tax algorithm
     * @return array Tax array
     */
    public function getTaxForItems(array $items, array $prices, float $shippingCost, int $taxGroup, ?int $taxIncluded = null, ?array $taxItemsArray = null, ?int $taxAlgorithm = null): array {
        if (!$taxAlgorithm) {
            $taxAlgorithm = get_company_pref('tax_algorithm');
        }
        // first create and set an array with all the tax types of the tax group
        if ($taxItemsArray != null) {
            $retTaxArray = $taxItemsArray;
        } else {
            $retTaxArray = get_tax_group_items_as_array($taxGroup);
        }

        $dec = \FA\UserPrefsCache::getPriceDecimals();

        $fullyExempt = true;
        foreach ($retTaxArray as $k => $t) {
            if ($t['rate'] !== null) {
                $fullyExempt = false;
            }
            $retTaxArray[$k]['Net'] = 0;
        }

        $retTaxArray['exempt'] = array('Value' => 0, 'Net' => 0, 'rate' => null, 'tax_type_id' => '', 'sales_gl_code' => '');
        $dec = \FA\UserPrefsCache::getPriceDecimals();
        // loop for all items
        for ($i = 0; $i < count($items); $i++) {
            $itemTaxes = $this->getTaxesForItem($items[$i], $retTaxArray);
            if ($itemTaxes == null || $fullyExempt) {
                $retTaxArray['exempt']['Value'] += round2(0, $dec);
                $retTaxArray['exempt']['Net'] += $prices[$i];
            } else {
                $taxMultiplier = 0;
                foreach ($itemTaxes as $taxitem) {
                    $taxMultiplier += $taxitem['rate'];
                }
                foreach ($itemTaxes as $itemTax) {
                    if ($itemTax['rate'] !== null) {
                        $index = $itemTax['tax_type_id'];
                        if ($taxIncluded == 1) {
                            $retTaxArray[$index]['Value'] += round2($prices[$i] * $itemTax['rate'] / (100 + $taxMultiplier), $dec);
                            $retTaxArray[$index]['Net'] += round2($prices[$i] * 100 / (100 + $taxMultiplier), $dec);
                        } else {
                            $retTaxArray[$index]['Value'] += round2($prices[$i] * $itemTax['rate'] / 100, $dec);
                            $retTaxArray[$index]['Net'] += $prices[$i];
                        }
                    }
                }
            }
        }
        // add the shipping taxes, only if non-zero, and only if tax group taxes shipping
        if ($shippingCost != 0) {
            $itemTaxes = get_shipping_tax_as_array($taxGroup);
            if ($itemTaxes != null) {
                if ($taxIncluded == 1) {
                    $taxRate = 0;
                    foreach ($itemTaxes as $itemTax) {
                        $index = $itemTax['tax_type_id'];
                        if (isset($retTaxArray[$index])) {
                            $taxRate += $itemTax['rate'];
                        }
                    }
                    $shippingNet = round2($shippingCost * 100 / (100 + $taxRate), $dec);
                }
                foreach ($itemTaxes as $itemTax) {
                    $index = $itemTax['tax_type_id'];
                    if ($itemTax['rate'] !== null && $retTaxArray[$index]['rate'] !== null) {
                        if ($taxIncluded == 1) {
                            $retTaxArray[$index]['Value'] += round2($shippingCost * $itemTax['rate'] / (100 + $taxRate), $dec);
                            $retTaxArray[$index]['Net'] += $shippingNet;
                        } else {
                            $retTaxArray[$index]['Value'] += round2($shippingCost * $itemTax['rate'] / 100, $dec);
                            $retTaxArray[$index]['Net'] += $shippingCost;
                        }
                    }
                }
            }
        }

        if ($taxAlgorithm == TCA_TOTALS) {
            // update taxes with
            foreach ($retTaxArray as $index => $itemTax) {
                $retTaxArray[$index]['Value'] = round2($itemTax['Net'] * $itemTax['rate'] / 100, $dec);
            }
        }

        return $retTaxArray;
    }
}