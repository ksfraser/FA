<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Regression Test for Legacy Display Functions
 *
 * This test captures the current behavior of legacy display_error(),
 * display_notification(), and display_warning() function calls before
 * they are refactored to use UiMessageService.
 *
 * RED Phase: Capture current behavior with regression tests
 * GREEN Phase: Replace calls with UiMessageService equivalents
 * REFACTOR Phase: Remove legacy functions and clean up
 */
class UiMessageServiceRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        global $messages, $cur_error_level, $SysPrefs;
        $messages = [];
        $cur_error_level = error_reporting();
        $SysPrefs = new \stdClass();
        $SysPrefs->go_debug = 0;
    }

    /**
     * Test: display_notification() calls in tax_types.php
     */
    public function testTaxTypesDisplayNotifications(): void
    {
        global $messages;

        // Simulate the calls from tax_types.php
        display_notification(_('New tax type has been added'));
        display_notification(_('Selected tax type has been updated'));
        display_notification(_('Selected tax type has been deleted'));

        $this->assertCount(3, $messages, 'Should have 3 notification messages');
        $this->assertEquals(E_USER_NOTICE, $messages[0][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[1][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[2][0]);
    }

    /**
     * Test: display_notification() calls in tax_groups.php
     */
    public function testTaxGroupsDisplayNotifications(): void
    {
        global $messages;

        // Simulate the calls from tax_groups.php
        display_notification(_('Selected tax group has been updated'));
        display_notification(_('New tax group has been added'));
        display_notification(_('Selected tax group has been deleted'));

        $this->assertCount(3, $messages, 'Should have 3 notification messages');
        $this->assertEquals(E_USER_NOTICE, $messages[0][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[1][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[2][0]);
    }

    /**
     * Test: display_notification() calls in item_tax_types.php
     */
    public function testItemTaxTypesDisplayNotifications(): void
    {
        global $messages;

        // Simulate the calls from item_tax_types.php
        display_notification(_('Selected item tax type has been updated'));
        display_notification(_('New item tax type has been added'));
        display_notification(_('Selected item tax type has been deleted'));

        $this->assertCount(3, $messages, 'Should have 3 notification messages');
        $this->assertEquals(E_USER_NOTICE, $messages[0][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[1][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[2][0]);
    }

    /**
     * Test: display_warning() call in customer_payments.php
     */
    public function testCustomerPaymentsDisplayWarning(): void
    {
        global $messages;

        // Simulate the call from customer_payments.php
        display_warning(_("This customer account is on hold."));

        $this->assertCount(1, $messages, 'Should have 1 warning message');
        $this->assertEquals(E_USER_WARNING, $messages[0][0]);
        $this->assertEquals(_("This customer account is on hold."), $messages[0][1]);
    }

    /**
     * Test: display_notification() calls in sales_people.php
     */
    public function testSalesPeopleDisplayNotifications(): void
    {
        global $messages;

        // Simulate the calls from sales_people.php
        display_notification(_('Selected sales person data have been updated'));
        display_notification(_('New sales person data have been added'));
        display_notification(_('Selected sales person data have been deleted'));

        $this->assertCount(3, $messages, 'Should have 3 notification messages');
        $this->assertEquals(E_USER_NOTICE, $messages[0][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[1][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[2][0]);
    }

    /**
     * Test: display_notification() calls in sales_types.php
     */
    public function testSalesTypesDisplayNotifications(): void
    {
        global $messages;

        // Simulate the calls from sales_types.php
        display_notification(_('New sales type has been added'));
        display_notification(_('Selected sales type has been updated'));

        $this->assertCount(2, $messages, 'Should have 2 notification messages');
        $this->assertEquals(E_USER_NOTICE, $messages[0][0]);
        $this->assertEquals(E_USER_NOTICE, $messages[1][0]);
    }
}