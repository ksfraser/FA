<?php

namespace FA;

use Ksfraser\HTML\Elements\HtmlTable;
use Ksfraser\HTML\Elements\HtmlTableRow;
use Ksfraser\HTML\Elements\HtmlTd;
use Ksfraser\HTML\Elements\HtmlString;
use Ksfraser\HTML\HtmlElementInterface;
use Ksfraser\HTML\HtmlAttribute;

/**
 * View for displaying voided transaction information
 *
 * Follows MVC pattern with dependency injection.
 * Uses HTML element composition for recursive rendering.
 *
 * SOLID Principles:
 * - Single Responsibility: Renders voided views only
 * - Open/Closed: Can be extended for additional voided display elements
 * - Liskov Substitution: Compatible with HtmlElementInterface
 * - Interface Segregation: Minimal, focused interface
 * - Dependency Inversion: Depends on data arrays, no tight coupling
 *
 * DRY: Reuses HTML element classes, avoids code duplication
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |   VoidedView       |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + render(voidEntry,|
 * |   label): HtmlEle..|
 * +---------------------+
 *
 * @package FA
 */
class VoidedView
{
    /**
     * Render voided information if transaction is voided
     *
     * @param array|null $voidEntry Voided entry data or null if not voided
     * @param string $label Message to display
     * @return HtmlElementInterface|null Returns table element if voided, null otherwise
     */
    public function render(?array $voidEntry, string $label): ?HtmlElementInterface
    {
        if ($voidEntry === null) {
            return null;
        }

        $table = new HtmlTable(new HtmlString(''));
        $table->addAttribute(new HtmlAttribute('class', 'tablestyle'));
        $table->addAttribute(new HtmlAttribute('width', '50%'));

        $row = new HtmlTableRow(new HtmlString(''));
        $cell = new HtmlTd(new HtmlString(''));
        $cell->addAttribute(new HtmlAttribute('align', 'center'));

        $content = "<font color=red>$label</font><br>";
        $content .= "<font color=red>" . _("Date Voided:") . " " . sql2date($voidEntry["date_"]) . "</font><br>";
        if (strlen($voidEntry["memo_"]) > 0) {
            $content .= "<center><font color=red>" . _("Memo:") . " " . $voidEntry["memo_"] . "</font></center><br>";
        }

        $cell->addNested(new HtmlString($content));
        $row->addNested($cell);
        $table->addNested($row);

        return $table;
    }
}