# Message System Refactoring Plan

## Current State

### Architecture Issues
The current message system uses global state and procedural code:

```php
// Global state
$messages = array(); // in errors.inc

// Adding messages (multiple ways)
trigger_error($msg, E_USER_WARNING);
error_handler(E_USER_ERROR, $msg, __FILE__, __LINE__);
UiMessageService::displayError($msg);

// Displaying messages
fmt_errors(); // in templates/headers
```

### Problems
1. **Global State**: `$messages` array is globally accessible
2. **No Encapsulation**: Direct array manipulation throughout codebase
3. **No Type Safety**: Messages are plain arrays `[$errno, $errstr, $file, $line, $backtrace]`
4. **Mixed Concerns**: Display logic in `fmt_errors()` mixed with storage
5. **Hard to Test**: Tests must manipulate global state
6. **No Clear API**: Multiple ways to add messages (trigger_error, error_handler, UiMessageService)

## Proposed Architecture

### 1. Message Value Object
```php
<?php
declare(strict_types=1);

namespace FA\Services\Messages;

/**
 * Immutable message value object
 */
class Message
{
    public function __construct(
        private readonly MessageLevel $level,
        private readonly string $text,
        private readonly string $file,
        private readonly int $line,
        private readonly ?string $backtrace = null
    ) {}
    
    public function getLevel(): MessageLevel { return $this->level; }
    public function getText(): string { return $this->text; }
    public function getFile(): string { return $this->file; }
    public function getLine(): int { return $this->line; }
    public function getBacktrace(): ?string { return $this->backtrace; }
    
    public function toArray(): array
    {
        return [
            $this->level->toErrorCode(),
            $this->text,
            $this->file,
            $this->line,
            $this->backtrace
        ];
    }
}
```

### 2. MessageLevel Enum
```php
<?php
declare(strict_types=1);

namespace FA\Services\Messages;

enum MessageLevel: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case NOTICE = 'notice';
    
    public function toErrorCode(): int
    {
        return match($this) {
            self::ERROR => E_USER_ERROR,
            self::WARNING => E_USER_WARNING,
            self::NOTICE => E_USER_NOTICE,
        };
    }
    
    public function getCssClass(): string
    {
        return match($this) {
            self::ERROR => 'err_msg',
            self::WARNING => 'warn_msg',
            self::NOTICE => 'note_msg',
        };
    }
    
    public static function fromErrorCode(int $errno): self
    {
        return match($errno) {
            E_USER_ERROR, E_ERROR => self::ERROR,
            E_USER_WARNING => self::WARNING,
            E_USER_NOTICE => self::NOTICE,
            default => self::NOTICE,
        };
    }
}
```

### 3. MessageCollection
```php
<?php
declare(strict_types=1);

namespace FA\Services\Messages;

/**
 * Collection of messages to display to the user
 * Replaces global $messages array
 */
class MessageCollection
{
    /** @var Message[] */
    private array $messages = [];
    
    public function add(Message $message): void
    {
        // Suppress duplicates (same as error_handler logic)
        foreach ($this->messages as $existing) {
            if ($this->isDuplicate($existing, $message)) {
                return;
            }
        }
        
        $this->messages[] = $message;
    }
    
    public function addError(string $text, string $file, int $line, ?string $backtrace = null): void
    {
        $this->add(new Message(MessageLevel::ERROR, $text, $file, $line, $backtrace));
    }
    
    public function addWarning(string $text, string $file, int $line, ?string $backtrace = null): void
    {
        $this->add(new Message(MessageLevel::WARNING, $text, $file, $line, $backtrace));
    }
    
    public function addNotice(string $text, string $file, int $line, ?string $backtrace = null): void
    {
        $this->add(new Message(MessageLevel::NOTICE, $text, $file, $line, $backtrace));
    }
    
    /** @return Message[] */
    public function getAll(): array
    {
        return $this->messages;
    }
    
    public function count(): int
    {
        return count($this->messages);
    }
    
    public function hasErrors(): bool
    {
        foreach ($this->messages as $msg) {
            if ($msg->getLevel() === MessageLevel::ERROR) {
                return true;
            }
        }
        return false;
    }
    
    public function clear(): void
    {
        $this->messages = [];
    }
    
    private function isDuplicate(Message $existing, Message $new): bool
    {
        return $existing->getLevel() === $new->getLevel()
            && $existing->getText() === $new->getText()
            && $existing->getFile() === $new->getFile()
            && $existing->getLine() === $new->getLine();
    }
}
```

### 4. MessageRenderer
```php
<?php
declare(strict_types=1);

namespace FA\Services\Messages;

/**
 * Renders messages as HTML
 * Replaces fmt_errors() function
 */
class MessageRenderer
{
    public function __construct(
        private readonly MessageCollection $messages
    ) {}
    
    public function render(bool $center = false): string
    {
        if ($this->messages->count() === 0) {
            return '';
        }
        
        global $SysPrefs;
        
        $content = '';
        $highestLevel = MessageLevel::NOTICE;
        
        foreach ($this->messages->getAll() as $message) {
            $level = $message->getLevel();
            
            // Upgrade to error in debug mode
            if ($SysPrefs->go_debug && $level !== MessageLevel::NOTICE) {
                $level = MessageLevel::ERROR;
            }
            
            // Track highest severity
            if ($this->isHigherPriority($level, $highestLevel)) {
                $highestLevel = $level;
                if ($level === MessageLevel::ERROR) {
                    $content = ''; // Clear lower priority messages
                }
            }
            
            if ($level === $highestLevel) {
                $content .= $this->renderMessage($message);
            }
        }
        
        return $this->wrapInDiv($content, $highestLevel);
    }
    
    private function renderMessage(Message $message): string
    {
        global $SysPrefs;
        
        $html = '<tr><td>' . $message->getText() . '</td></tr>';
        
        if ($SysPrefs->go_debug && $message->getBacktrace()) {
            $html .= '<tr><td><pre>' . $message->getBacktrace() . '</pre></td></tr>';
        }
        
        return $html;
    }
    
    private function wrapInDiv(string $content, MessageLevel $level): string
    {
        global $path_to_root;
        
        $cssClass = $level->getCssClass();
        
        return '<div class="' . $cssClass . '">'
            . '<table style="width:100%;">'
            . $content
            . '</table>'
            . '<a href="#" onclick="this.parentElement.style.display=\'none\';return false;">'
            . '<img src="' . $path_to_root . '/themes/default/images/close.png" border="0" style="margin:0px;padding:0px;" />'
            . '</a>'
            . '</div>';
    }
    
    private function isHigherPriority(MessageLevel $a, MessageLevel $b): bool
    {
        $priority = [
            MessageLevel::ERROR => 3,
            MessageLevel::WARNING => 2,
            MessageLevel::NOTICE => 1,
        ];
        
        return $priority[$a] > $priority[$b];
    }
}
```

### 5. Updated UiMessageService
```php
<?php
declare(strict_types=1);

namespace FA\Services;

use FA\Services\Messages\MessageCollection;
use FA\Services\Messages\MessageLevel;

class UiMessageService
{
    private static ?MessageCollection $messages = null;
    
    private static function getMessages(): MessageCollection
    {
        if (self::$messages === null) {
            self::$messages = new MessageCollection();
        }
        return self::$messages;
    }
    
    public static function displayError(string $msg, bool $center = true): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? $trace[0];
        
        self::getMessages()->addError(
            $msg,
            $caller['file'] ?? __FILE__,
            $caller['line'] ?? __LINE__,
            self::getBacktrace()
        );
    }
    
    public static function displayWarning(string $msg, bool $center = true): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? $trace[0];
        
        self::getMessages()->addWarning(
            $msg,
            $caller['file'] ?? __FILE__,
            $caller['line'] ?? __LINE__,
            self::getBacktrace()
        );
    }
    
    public static function displayNotification(string $msg, bool $center = true): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? $trace[0];
        
        self::getMessages()->addNotice(
            $msg,
            $caller['file'] ?? __FILE__,
            $caller['line'] ?? __LINE__,
            self::getBacktrace()
        );
    }
    
    private static function getBacktrace(): ?string
    {
        global $SysPrefs;
        return isset($SysPrefs) && $SysPrefs->go_debug > 1 
            ? get_backtrace(true, 2) 
            : null;
    }
}
```

## Migration Strategy

### Phase 1: Create New Classes (Non-Breaking)
1. Create `Message`, `MessageLevel`, `MessageCollection`, `MessageRenderer` classes
2. Add comprehensive unit tests
3. Keep existing global `$messages` array intact
4. Commit: "Add message system classes (preparation for refactoring)"

### Phase 2: Dual Mode Operation (Bridge Pattern)
1. Update `UiMessageService` to write to BOTH:
   - New `MessageCollection` instance
   - Old global `$messages` array
2. Create adapter function: `getGlobalMessages()` returns old array format
3. Keep `fmt_errors()` working with global array
4. Test thoroughly - both systems should produce identical output
5. Commit: "UiMessageService: Add MessageCollection support (dual mode)"

### Phase 3: Update Display Layer
1. Identify all places that call `fmt_errors()`
2. Update to use `MessageRenderer` instead
3. Inject `MessageCollection` into page templates
4. Run parallel testing: old vs new output
5. Commit: "Replace fmt_errors() with MessageRenderer"

### Phase 4: Update Error Handler
1. Update `error_handler()` to use `MessageCollection`
2. Update `trigger_error()` handling
3. Keep backward compatibility layer
4. Commit: "error_handler: Use MessageCollection"

### Phase 5: Remove Global State
1. Remove writes to global `$messages` array
2. Remove backward compatibility adapters
3. Remove global `$messages` declaration
4. Update all remaining references
5. Commit: "Remove global $messages array"

### Phase 6: Cleanup
1. Remove old `fmt_errors()` function
2. Remove compatibility code
3. Update documentation
4. Commit: "Complete message system refactoring"

## Testing Strategy

### Unit Tests
- `MessageTest` - Test immutability, toArray()
- `MessageLevelTest` - Test enum conversions
- `MessageCollectionTest` - Test add, duplicate suppression, filtering
- `MessageRendererTest` - Test HTML output, CSS classes

### Integration Tests
- Test dual mode operation (both systems produce same output)
- Test error reporting level filtering
- Test debug mode backtrace rendering
- Test message priority handling (errors override warnings)

### Regression Tests
- Capture current `fmt_errors()` output for 100+ scenarios
- Compare new `MessageRenderer` output
- Must be pixel-perfect identical

## Benefits

### Immediate
- **Type Safety**: Can't accidentally corrupt message format
- **Testability**: No global state in tests
- **IDE Support**: Autocomplete, type hints, refactoring tools work

### Long Term
- **Extensibility**: Easy to add new message types (success, info)
- **Flexibility**: Can render messages as JSON, XML, etc.
- **Maintainability**: Clear API and single responsibility
- **Performance**: Can optimize collection operations

## Estimated Effort

- **Phase 1**: 2 hours (new classes + tests)
- **Phase 2**: 2 hours (dual mode + tests)
- **Phase 3**: 3 hours (update display layer)
- **Phase 4**: 2 hours (update error handler)
- **Phase 5**: 1 hour (remove global state)
- **Phase 6**: 1 hour (cleanup)

**Total**: 11 hours

## Risk Assessment

**Risk Level**: Medium

**Risks**:
- Message display is visible to users (UI changes are risky)
- Global `$messages` used in many files (wide impact)
- Error handling is critical (system stability)

**Mitigation**:
- Phased migration with backward compatibility
- Extensive regression testing
- Dual mode operation to verify correctness
- Feature flag to roll back if needed
- Deploy to staging environment first

## Dependencies

None - Can be done independently of other refactoring work.

## Future Enhancements

Once MessageCollection is in place:

1. **Flash Messages**: Persist messages across redirects
2. **Message Queuing**: Defer display until appropriate time
3. **Structured Logging**: Send messages to log files with context
4. **i18n Support**: Message translation built into Message class
5. **Rich Messages**: Support HTML, markdown, or custom formatting
6. **User Dismissal**: Track which messages user has closed
7. **Notification Center**: Show all messages in popup/sidebar

---

**Status**: 📋 Planned  
**Priority**: High  
**Created**: November 17, 2025  
**Owner**: TBD
