# Git Submodule Setup: ksf_PrefCache

## Overview

The `PreferenceCache` library has been extracted as a standalone Git repository and integrated as a submodule. This allows the library to be reused across multiple projects while maintaining version control.

## Submodule Details

- **Repository**: https://github.com/ksfraser/ksf_PrefCache
- **Local Path**: `libs/ksf_PrefCache/`
- **Namespace**: `KSF\PrefCache`
- **Package**: `ksfraser/pref-cache`
- **License**: GPL-3.0-or-later

## Directory Structure

```
libs/ksf_PrefCache/
├── src/
│   ├── PreferenceProviderInterface.php  # Contract for data sources
│   └── PreferenceCache.php              # Generic caching implementation
├── composer.json                         # Package configuration
├── README.md                            # Standalone documentation
├── README_LIBRARY.md                    # Developer guide
└── LICENSE                              # GPL-3.0
```

## Cloning the Project

When cloning the FA project, initialize the submodule:

```bash
git clone <FA-repo-url>
cd FA
git submodule init
git submodule update
```

Or clone with submodules in one step:

```bash
git clone --recurse-submodules <FA-repo-url>
```

## Updating the Submodule

To pull the latest changes from the ksf_PrefCache repository:

```bash
cd libs/ksf_PrefCache
git pull origin main
cd ../..
git add libs/ksf_PrefCache
git commit -m "Update ksf_PrefCache submodule to latest"
```

## Integration in FA

The FA codebase uses the library through provider adapters:

### 1. FA Session Provider (`includes/Providers/FASessionPreferenceProvider.php`)
```php
<?php
declare(strict_types=1);

namespace FA\Providers;

use KSF\PrefCache\PreferenceProviderInterface;

class FASessionPreferenceProvider implements PreferenceProviderInterface
{
    // FA-specific implementation reading from $_SESSION
}
```

### 2. Facade Service (`includes/Services/UserPrefsCacheV2.php`)
```php
<?php
declare(strict_types=1);

namespace FA\Services;

use KSF\PrefCache\PreferenceCache;
use FA\Providers\FASessionPreferenceProvider;

class UserPrefsCacheV2
{
    private static ?PreferenceCache $cache = null;
    
    // Convenience methods for FA preference access
}
```

## Testing

The test suite loads the library from the submodule:

**tests/bootstrap.php:**
```php
// Library classes (from submodule)
require_once __DIR__ . '/../libs/ksf_PrefCache/src/PreferenceProviderInterface.php';
require_once __DIR__ . '/../libs/ksf_PrefCache/src/PreferenceCache.php';
```

Run tests:
```bash
php vendor/bin/phpunit tests/PreferenceCacheLibraryTest.php
php vendor/bin/phpunit tests/UserPrefsCacheTest.php
```

All 33 tests passing:
- 9 library tests (generic PreferenceCache)
- 11 UserPrefs tests (FA facade)
- 4 integration tests (invalidation)
- 9 Format tests (performance)

## Contributing to the Library

To contribute improvements to the generic library:

1. Navigate to submodule:
   ```bash
   cd libs/ksf_PrefCache
   ```

2. Create a feature branch:
   ```bash
   git checkout -b feature/my-improvement
   ```

3. Make changes and test

4. Commit and push to library repo:
   ```bash
   git commit -m "Add feature"
   git push origin feature/my-improvement
   ```

5. Create pull request on GitHub

6. After merge, update FA to use new version:
   ```bash
   cd libs/ksf_PrefCache
   git checkout main
   git pull
   cd ../..
   git add libs/ksf_PrefCache
   git commit -m "Update ksf_PrefCache to include <feature>"
   ```

## Benefits

### Separation of Concerns
- Generic library has no FA dependencies
- Can be tested independently
- Easier to maintain

### Reusability
- Can be used in other projects via:
  - Git submodule (as FA does)
  - Composer: `composer require ksfraser/pref-cache`
  - Direct download from GitHub

### Version Control
- Library has its own version history
- FA can pin to specific library version
- Easy to track library changes separately

### Community Contribution
- Standalone repo makes it easier for others to:
  - Find and discover the library
  - Submit issues and pull requests
  - Fork for their own needs

## Migration Summary

**Before:**
- Library at `includes/Library/Cache/`
- Namespace: `FA\Library\Cache`
- Tightly coupled with FA

**After:**
- Library at `libs/ksf_PrefCache/` (submodule)
- Namespace: `KSF\PrefCache`
- Framework-agnostic, reusable
- GitHub: https://github.com/ksfraser/ksf_PrefCache

## Commits

1. `1f14d394` - Extract PreferenceCache library as Git submodule
2. `7fc748de` - Remove old includes/Library/Cache directory

## Related Documentation

- `docs/UserPrefsCache_Integration.md` - Integration guide
- `docs/UserPrefsCache_Performance.md` - Performance analysis
- `docs/PreferenceCache_Migration.md` - Migration from old to new
- `libs/ksf_PrefCache/README.md` - Standalone library documentation
