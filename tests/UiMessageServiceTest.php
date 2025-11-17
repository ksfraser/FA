<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FA\Services\UiMessageService;

class UiMessageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset error handler state
        global $messages;
        $messages = null;
    }

    public function testDisplayErrorTriggersError(): void
    {
        // Capture the error
        $errorTriggered = false;
        $errorMessage = '';
        
        set_error_handler(function($errno, $errstr) use (&$errorTriggered, &$errorMessage) {
            $errorTriggered = true;
            $errorMessage = $errstr;
            return true;
        });
        
        UiMessageService::displayError('Test error');
        $originalTriggered = $errorTriggered;
        $originalMessage = $errorMessage;
        
        $errorTriggered = false;
        $errorMessage = '';
        
        UiMessageService::displayError('Test error');
        $replacementTriggered = $errorTriggered;
        $replacementMessage = $errorMessage;
        
        restore_error_handler();
        
        $this->assertEquals($originalTriggered, $replacementTriggered, 'Both should trigger error');
        $this->assertEquals($originalMessage, $replacementMessage, 'Error messages must be identical');
    }

    public function testDisplayNotificationTriggersNotice(): void
    {
        $errorTriggered = false;
        $errorLevel = 0;
        
        set_error_handler(function($errno, $errstr) use (&$errorTriggered, &$errorLevel) {
            $errorTriggered = true;
            $errorLevel = $errno;
            return true;
        });
        
        display_notification('Test notice');
        $originalLevel = $errorLevel;
        
        $errorLevel = 0;
        
        UiMessageService::displayNotification('Test notice');
        $replacementLevel = $errorLevel;
        
        restore_error_handler();
        
        $this->assertEquals($originalLevel, $replacementLevel, 'Error levels must be identical');
        $this->assertEquals(E_USER_NOTICE, $replacementLevel, 'Should trigger E_USER_NOTICE');
    }

    public function testDisplayWarningTriggersWarning(): void
    {
        $errorLevel = 0;
        
        set_error_handler(function($errno, $errstr) use (&$errorLevel) {
            $errorLevel = $errno;
            return true;
        });
        
        display_warning('Test warning');
        $originalLevel = $errorLevel;
        
        $errorLevel = 0;
        
        UiMessageService::displayWarning('Test warning');
        $replacementLevel = $errorLevel;
        
        restore_error_handler();
        
        $this->assertEquals($originalLevel, $replacementLevel, 'Error levels must be identical');
        $this->assertEquals(E_USER_WARNING, $replacementLevel, 'Should trigger E_USER_WARNING');
    }
}
