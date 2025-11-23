# FrontAccounting Refactoring Project

## Overview

This project is undergoing a comprehensive refactoring to modernize the codebase using Object-Oriented Programming (OOP) principles and software engineering best practices. The goal is to transform the legacy procedural code into a maintainable, testable, and scalable system, and then extend it with advanced capabilities including event-driven workflows, plugin architecture, and seamless external integrations.

## Objectives

### Core Refactoring (Phase 1 - In Progress)
- **OOP Transformation**: Convert procedural code to object-oriented classes and structures.
- **Test Coverage**: Ensure all classes and files have comprehensive unit test cases and integration test cases using PHPUnit.
- **Best Practices**:
  - **SOLID Principles**: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion.
  - **DRY (Don't Repeat Yourself)**: Eliminate code duplication.
  - **DI (Dependency Injection)**: Inject dependencies for better testability and flexibility.
  - **SRP (Single Responsibility Principle)**: Each class has one reason to change.
  - **TDD (Test-Driven Development)**: Write tests before implementing code.
  - **UML**: Document architecture with Unified Modeling Language diagrams.
  - **PHPDoc**: Comprehensive documentation for all classes, methods, and properties.
  - **PHPUnit**: Framework for unit and integration testing.
- **MVC Separation**: Clearly separate Model, View, and Controller layers.
- **Architectural Patterns**:
  - **Repositories**: For data access abstraction.
  - **Services**: For business logic encapsulation.
  - **Handlers**: For processing requests and responses.
  - **DAO/DTO/Models**: Data Access Objects, Data Transfer Objects, and Domain Models for data handling.
- **Version Control**: Frequent `git add` and `git commit` with detailed change documentation to track progress incrementally.

### Extended Capabilities (Phase 2-7 - Planned)
- **Event Handler/Workflow System**: SuiteCRM-style configurable workflows and event handling
- **Plugin/Extension System**: WordPress-style plugin architecture for extensibility
- **Universal Module Integration**: Expand predb/postdb hooks to ALL modules for external system sync
- **External System Integrations**: SuiteCRM, SquareUp, WooCommerce, QuickBooks, Xero, Zapier
- **Employee Management**: Extend contact system to include comprehensive employee management
- **Unified Contact System**: Single contact system that can be customer, supplier, or employee

## Current Progress

### Phase 1: Core Refactoring (75% Complete)
- ✅ Refactored view files (e.g., `view_dispatch.php`, `view_credit.php`, `view_invoice.php`) to use OOP HTML rendering with Ksfraser\HTML library.
- ✅ Created models like `Dispatch`, `Invoice`, etc., with data loading and business logic.
- ✅ Implemented views like `ViewDispatch`, `ViewInvoice`, etc., for rendering.
- ✅ Added unit tests for models and views.
- ✅ Enhanced documentation with PHPDoc and UML diagrams.
- ✅ **COMPLETED**: All `get_qoh_on_date()` calls replaced with `InventoryService::getQohOnDate()` (32 replacements)
- 🔄 **IN PROGRESS**: Continuing systematic replacement of legacy function calls

### Phase 2-7: Extended Capabilities (Planned)
See [EXTENDED_BUSINESS_REQUIREMENTS.md](EXTENDED_BUSINESS_REQUIREMENTS.md) for detailed specifications of the advanced features planned after core refactoring completion.

## Structure

- `includes/`: Core classes (Models, Views, Services, etc.)
- `tests/`: PHPUnit test cases
- `sales/view/`: Refactored view controllers
- Other directories remain as per original structure until refactored.

## Key Documentation Files

- [EXTENDED_BUSINESS_REQUIREMENTS.md](EXTENDED_BUSINESS_REQUIREMENTS.md) - Detailed specifications for advanced capabilities
- [REPLACEMENT_PROGRESS.md](REPLACEMENT_PROGRESS.md) - Current refactoring progress and status
- [REFACTORING_COMPLETE.md](REFACTORING_COMPLETE.md) - Completed refactoring documentation
- [DI_COMPLETE_REPORT.md](DI_COMPLETE_REPORT.md) - Dependency injection implementation details

## How to Contribute

1. Follow TDD: Write tests first.
2. Implement code adhering to SOLID and other principles.
3. Add PHPDoc and UML where applicable.
4. Commit frequently with descriptive messages (e.g., "Refactor ViewDispatch to use DI and add unit tests").

## Tools and Libraries

- **PHPUnit**: For testing.
- **Ksfraser\HTML**: For OOP HTML element composition.
- **Git**: For version control.

## Future Steps

- Refactor remaining view files and controllers.
- Implement repositories for database access.
- Add services for complex business logic.
- Ensure full test coverage.
- Update documentation as architecture evolves.

This refactoring aims to make FrontAccounting more robust, easier to maintain, and aligned with modern PHP development standards.

---

## Post-Refactoring Expansion Requirements

After completing the refactoring, the following business requirements will be implemented to expand FrontAccounting's capabilities:

### Event Handler/Workflow System
- Implement a configurable event-driven workflow system similar to SuiteCRM
- Allow administrators to define custom workflows triggered by business events
- Support conditional logic, approvals, and automated actions in workflows

### Plugin/Extension System
- Develop a registration/plugin architecture similar to WordPress
- Enable third-party developers to create and install extensions
- Provide hooks and APIs for extending core functionality without modifying base code

### Universal Pre/Post Database Action Integration
- Extend the existing preDB and postDB action system (currently only in Sales module) to ALL modules
- Ensure consistent event triggering across Customers, Suppliers, Inventory, Purchasing, etc.
- Allow plugins to hook into these events for custom integrations

### Cross-Application Data Synchronization
- **Customer Integration**: Automatically send new customer data to external web applications
- **Product Integration**: Push product additions/updates to SuiteCRM, SquareUp, WooCommerce, and other platforms
- **Supplier Integration**: Sync supplier information with external systems
- Implement configurable webhooks and API integrations for real-time data sharing

### REST API Expansion
- Implement a comprehensive REST API using Slim Framework for all modules
- Provide standardized endpoints for CRUD operations across Customers, Suppliers, Inventory, Sales, Purchasing, etc.
- Enable third-party integrations and mobile app connectivity
- Include authentication, rate limiting, and API versioning

### Employee Management Extension
- Extend FrontAccounting to include comprehensive employee management
- Reuse and enhance the existing Contact system architecture
- Allow a single contact record to serve as Customer, Supplier, and/or Employee
- Add employee-specific fields and relationships while maintaining data integrity

---

![FrontAccounting ERP](./themes/default/images/logo_frontaccounting.jpg  "FrontAccounting ERP")

FrontAccounting ERP is open source, web-based accounting software for small and medium enterprises.
It supports double entry accounting providing both low level journal entry and user friendly, document based 
interface for everyday business activity with automatic GL postings generation. This is multicurrency,
multilanguage system with active worldwide users community:

* [Project web site](http://frontaccounting.com)
* [SourceForge project page](http://sourceforge.net/projects/frontaccounting/)
* [Central users forum](http://frontaccounting.com/punbb/index.php)
* [Main code repository](https://sourceforge.net/p/frontaccounting/git/ci/master/tree/)
* [GitHub mirror](http://github.com/FrontAccountingERP/FA)
* [Mantis bugtracker](http://mantis.frontaccounting.com)
* [FrontAccounting Wiki](http://frontaccounting.com/fawiki/)

This project is developed as cooperative effort by FrontAccounting team and available under [GPL v.3 license](./doc/license.txt) 

## Requirements

To use FrontAccounting application you should have already installed: 

*   Any HTTP web server supporting php eg. _**Apache with mod_php**_ or _**IIS**_.
*   **_PHP_** >=5.0 (version 5.6 or 7.x is recommended)
*   **_MySQL_** >=4.1 server with **_Innodb_** tables enabled, or any version on **MariaDB** server
*   **_Adobe Acrobat Reader_** (or any another PDF reader like _**evince**_) is handy for viewing reports before printing them out.

## Installation
### 1. PHP configuration checks

*   One critical aspect of the PHP installation is the setting of **_session.auto_start_** in the php.ini file. Some rpm distributions of PHP have the default setting of **_session.auto_start = 1_**. This starts a new session at the beginning of each script. However, this makes it impossible to instantiate any class objects that the system relies on. Classes are used extensively by this system. When sessions are required they are started by the system and this setting of **_session.auto_start_** can and should be set to 0.
*   For security reasons both Register Globals and Magic Quotes php settings should be set to Off. When FrontAccounting is used with www server running php as Apache module, respective flags are set in .htaccess file. When your server uses CGI interface to PHP you should set  **_magic_quotes_gpc = 0_** and **_register_globals = 0_** in php.ini file.
*   **_Innodb_** tables must be enabled in the MySQL server. These tables allow database transactions which are a critical component of the software. This is enabled by default in the newer versions of MySQL. If you need to enable it yourself, consult the MySQL manual.

### 2. Download application files

* Download and unpack latest FrontAccounting tarball from SourceForge into folder created under web server document root, e.g. **/var/www/html/frontaccounting**

* If you prefer easy upgrades when new minor versions are released, you can clone sources from SourceForge project page or Github mirror e.g.:
>	# cd  /var/www/html
>	# git clone `https://git.code.sf.net/p/frontaccounting/git` frontaccounting

Master branch contains all the latest bugfixes made atop the last stable release.
	
### 3. Installation

FrontAccounting should NOT be used via unsecure http protocol. If you really need this - change SECURE_ONLY constant in /includes/session.inc to false (comment in the file added). Unfortunately this option cannot be added in sysprefs/config.php because the settings are not available before session is started.

Use your browser to open page at URL related to chosen installation folder. As an example, if you plan to use application locally and in previous step you have put files on your Linux box in /var/www/html/frontaccounting subfolder, just select `http://localhost/frontaccounting` url in your browser, and you will see start page of installation wizard. Follow instructions displayed during the process.

During installation you will need to provide data server credentials with permissions to create new database, or you will have to provide existing database name and credentials for user with valid usage permissions to access it. You will have to chose also a couple of other options including installation language, optimal encoding for database data etc. Keep in mind that some options (like additional translations and charts of accounts) presented during installation process could be installed also later, when FrontAccounting is already in use.

After successful installation please remove or rename your install directory for safety reasons. You won't need it any more.

### 4. Logging In For the First Time

Open a browser and enter the URL for the web server directory where FrontAccounting is installed. Enter the user name  **admin** and use password declared during install process to login as company administrator. Now you can proceed with configuration process setting up additional user accounts, creating fiscal years, defining additional currencies, GL accounts etc. All configuration options available in application are described in [FrontAccounting Wiki](http://frontaccounting.com/fawiki/) available directly from Help links on every application page under ![Help](./themes/default/images/help.gif  "Help") icon.
 

## Troubleshooting

If you encountered any problems with FrontAccounting configuration or usage, please consult your case with other users on [Frontaccounting forum](http://frontaccounting.com/punbb/index.php). If you think you have encountered a bug in application and after consulting other community members you still are sure this is really a bug, please fill in a report in project [Mantis bugtracker](http://mantis.frontaccounting.com) with all details which allow development team reproduce the problem, and hopefully fix it. Keep in mind, that  [GitHub](http://github.com/FrontAccountingERP/FA) page is mainly passive mirror for project based on SorceForge, so posting bug reports here is at least suboptimal.
