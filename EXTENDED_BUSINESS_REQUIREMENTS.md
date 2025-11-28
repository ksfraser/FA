# FrontAccounting Extended Business Requirements

## Overview
This document outlines the expanded business requirements for the FrontAccounting (FA) refactoring project. Beyond the current TDD-based refactoring to replace legacy procedural calls with modern OOP services, we will extend FA's capabilities with:

1. **Event Handler/Workflow System** (SuiteCRM-style)
2. **Plugin/Extension System** (WordPress-style)
3. **Universal Module Integration** (expand predb/postdb hooks to all modules)
4. **External System Integrations** (SuiteCRM, SquareUp, WooCommerce, etc.)
5. **Employee Management** (using extended Contact system)
6. **Unified Contact System** (Customer + Supplier + Employee)

## Current System Analysis

### Existing Hook System
- **File**: `includes/hooks.inc`
- **Current Hooks**:
  - `db_prewrite(&$cart, $trans_type)` - Before database write
  - `db_postwrite(&$cart, $trans_type)` - After database write
  - `db_prevoid($trans_type, $trans_no)` - Before transaction void
- **Current Integration**: Only Sales module has integrated hooks
- **Limitation**: Not all modules support hooks

### Existing Contact System
- **File**: `includes/db/crm_contacts_db.inc`
- **Structure**: `crm_persons` table with basic contact information
- **Current Usage**: Customer branches and contacts
- **Limitation**: Not unified across customer/supplier/employee entities

### Module Structure
- **Applications**: `applications/*.php` (customers, suppliers, inventory, etc.)
- **Modules Directory**: `modules/` (currently minimal)
- **Extension System**: Basic hook system exists but limited

## Expanded Requirements

### 1. Event Handler/Workflow System (SuiteCRM-style)

#### 1.1 Event Types
- **Database Events**: pre/post create, update, delete for all entities
- **Business Logic Events**: order approval, payment received, inventory alerts
- **System Events**: user login/logout, module activation, backup completion
- **Custom Events**: User-defined events for specific business processes

#### 1.2 Event Handler Architecture
```php
interface EventHandlerInterface {
    public function handle(Event $event): void;
    public function getSubscribedEvents(): array;
}

class EventManager {
    public function dispatch(string $eventName, Event $event): void;
    public function addHandler(EventHandlerInterface $handler): void;
    public function removeHandler(EventHandlerInterface $handler): void;
}
```

#### 1.3 Workflow Engine
- **Visual Workflow Designer**: Drag-and-drop workflow creation
- **Conditional Logic**: If/then/else conditions based on data
- **Action Types**:
  - Send notifications (email, SMS)
  - Update records
  - Create related records
  - Call external APIs
  - Execute custom code
- **Approval Workflows**: Multi-step approval processes

#### 1.4 Configuration
- **Database Tables**:
  - `workflows` - Workflow definitions
  - `workflow_steps` - Individual steps in workflow
  - `workflow_instances` - Running workflow instances
  - `event_handlers` - Registered event handlers

### 2. Plugin/Extension System (WordPress-style)

#### 2.1 Plugin Architecture
```php
interface PluginInterface {
    public function getName(): string;
    public function getVersion(): string;
    public function activate(): bool;
    public function deactivate(): bool;
    public function getHooks(): array;
}

class PluginManager {
    public function registerPlugin(PluginInterface $plugin): void;
    public function activatePlugin(string $pluginName): bool;
    public function deactivatePlugin(string $pluginName): bool;
    public function getActivePlugins(): array;
}
```

#### 2.2 Plugin Types
- **Integration Plugins**: Connect to external systems
- **Feature Plugins**: Add new functionality
- **Theme Plugins**: Customize UI appearance
- **Report Plugins**: Add custom reports
- **API Plugins**: Extend REST API capabilities

#### 2.3 Plugin Marketplace
- **Plugin Repository**: Centralized plugin directory
- **Version Management**: Automatic updates and compatibility checking
- **Security**: Plugin code review and sandboxing
- **Monetization**: Premium plugin support

#### 2.4 Plugin API
- **Hook System**: WordPress-style action/filter system
- **Service Injection**: Access to all FA services
- **Database Access**: Safe database abstraction layer
- **UI Integration**: Seamless UI extension points

### 3. Universal Module Integration

#### 3.1 Expanded Hook System
- **All Modules**: Extend predb/postdb hooks to ALL modules
- **Hook Types**:
  - `pre_db_write` - Before any database write
  - `post_db_write` - After any database write
  - `pre_db_delete` - Before record deletion
  - `post_db_delete` - After record deletion
  - `pre_validation` - Before data validation
  - `post_validation` - After data validation

#### 3.2 Module Integration Points
- **Customers**: Customer creation/update triggers external sync
- **Suppliers**: Supplier data pushed to procurement systems
- **Inventory**: Product data synced with e-commerce platforms
- **Employees**: HR system integration
- **Financial**: Accounting data export to external systems

#### 3.3 Integration Framework
```php
interface IntegrationInterface {
    public function getName(): string;
    public function getSupportedEntities(): array;
    public function syncEntity(string $entityType, array $data): bool;
    public function getSyncStatus(string $entityType, $entityId): array;
}

class IntegrationManager {
    public function registerIntegration(IntegrationInterface $integration): void;
    public function syncEntity(string $entityType, $entityId): void;
    public function getIntegrationStatus(): array;
}
```

### 3.3 Modular Architecture and Extensibility

#### 3.3.1 SuiteCRM-Style Field Extensibility
- **Name-Value Table Pattern**: Implement dynamic field addition using name-value tables
- **Entity Extension**: Allow custom fields on all major entities (customers, suppliers, items, etc.)
- **No Schema Changes**: Add fields without modifying native database tables
- **Field Types**: Support text, number, date, boolean, dropdown, and multi-select fields
- **Validation Rules**: Configurable field validation and business rules
- **UI Integration**: Seamless integration with existing forms and grids

#### 3.3.2 Full Application Modularization
- **Module Registry**: Convert all applications outside GL/JE to pluggable modules
- **Core Modules**: Define core modules (GL, AR/AP, Inventory) vs. optional modules
- **Module Dependencies**: Manage inter-module dependencies and compatibility
- **Module Lifecycle**: Support install, enable/disable, uninstall operations
- **Version Management**: Independent versioning and upgrade paths for modules

#### 3.3.3 Reports as Modules
- **Report Modules**: Convert all reports to pluggable modules
- **Custom Report Builder**: Visual report designer for end-users
- **Report Templates**: Pre-built report templates with customization
- **Export Formats**: Support PDF, Excel, CSV, and custom formats
- **Scheduled Reports**: Automated report generation and distribution
- **Report Security**: Role-based access control for reports

#### 3.3.4 WebERP Feature Integration
- **Feature Comparison**: Comprehensive analysis of WebERP capabilities
- **Selective Integration**: Identify and integrate valuable WebERP features
- **Manufacturing Module**: Advanced manufacturing and work order management
- **Advanced Inventory**: Multi-warehouse, lot tracking, and serial numbers
- **Project Management**: Project costing and time tracking integration
- **Quality Control**: Inspection and quality management features

#### 3.3.5 Asset Management Extensions
- **Loanable Assets**: Track assets that can be loaned to employees/customers
- **Asset Issuance**: Record asset checkout/checkin with responsible parties
- **Asset Reservations**: Allow advance booking of assets
- **Asset Maintenance**: Schedule and track maintenance activities
- **Asset Depreciation**: Enhanced depreciation methods and reporting
- **Asset Insurance**: Track insurance coverage and claims

#### 3.3.6 Extended Asset Purchasing
- **Supplier Integration**: Direct integration with supplier asset catalogs
- **Purchase Workflows**: Multi-step approval for asset purchases
- **Asset Specifications**: Detailed technical specifications and requirements
- **Vendor Management**: Preferred vendors and contract management
- **Asset Leasing**: Support for leased assets with lease terms
- **Warranty Tracking**: Warranty information and claim management

### 4. External System Integrations

#### 4.1 Supported Systems
- **SuiteCRM**: Customer and contact synchronization
- **SquareUp**: Payment processing and transaction sync
- **WooCommerce**: Product and order synchronization
- **QuickBooks**: Accounting data exchange
- **Xero**: Cloud accounting integration
- **Zapier**: Workflow automation platform
- **Custom APIs**: Generic REST API integration

#### 4.2 Integration Patterns
- **Real-time Sync**: Immediate data synchronization
- **Batch Sync**: Scheduled bulk data transfers
- **Event-driven**: Triggered by specific events
- **Bidirectional**: Two-way data synchronization

#### 4.3 Data Mapping
- **Field Mapping**: Configure field relationships between systems
- **Data Transformation**: Apply business rules during sync
- **Conflict Resolution**: Handle data conflicts automatically
- **Audit Trail**: Track all synchronization activities

### 5. Employee Management System

#### 5.1 Employee Entity
- **Base Contact**: Extend existing contact system
- **Employee-specific Fields**:
  - Employee ID
  - Department
  - Position/Title
  - Manager
  - Hire Date
  - Salary Information
  - Benefits
  - Skills/Competencies

#### 5.2 Employee Module Features
- **Employee Records**: Complete employee profile management
- **Organizational Chart**: Visual representation of company structure
- **Time Tracking**: Clock in/out, project time allocation
- **Performance Management**: Goals, reviews, feedback
- **Training Records**: Certifications, courses completed
- **Leave Management**: Vacation, sick leave tracking

#### 5.3 Integration Points
- **Payroll Systems**: Export employee data for payroll processing
- **HR Systems**: Sync with external HR platforms
- **Time Tracking**: Integration with project management tools
- **Benefits Administration**: Insurance and benefits management

### 6. Unified Contact System

#### 6.1 Contact Architecture
```php
class Contact {
    private $id;
    private $type; // customer, supplier, employee, contact
    private $personId; // Reference to crm_persons
    private $entityId; // Reference to specific entity (customer, supplier, employee)
    private $roles = []; // Array of roles this contact can have

    public function addRole(string $role): void;
    public function removeRole(string $role): void;
    public function hasRole(string $role): bool;
}
```

#### 6.2 Contact Types
- **Customer Contact**: Can place orders, receive invoices
- **Supplier Contact**: Can receive purchase orders, send invoices
- **Employee Contact**: Internal staff member
- **General Contact**: External contact without specific business relationship

#### 6.3 Contact Management
- **Unified Search**: Search across all contact types
- **Relationship Management**: Track relationships between contacts
- **Communication History**: Log all interactions with contacts
- **Contact Segmentation**: Group contacts by various criteria
- **Bulk Operations**: Mass update contact information

## Technical Implementation Plan

### Phase 1: Foundation (Current - Complete)
- ✅ TDD-based refactoring
- ✅ Service layer architecture
- ✅ Static wrapper methods for backward compatibility

### Phase 2: Event System (Complete)
- ✅ Implement EventManager and Event classes
- ✅ Create database tables for workflows
- ✅ Add event dispatching to all database operations
- ✅ Build workflow designer UI (basic WorkflowService implemented)

### Phase 3: Plugin System (Complete)
- ✅ Create PluginManager and PluginInterface
- ✅ Implement plugin loading and activation system
- ✅ Add plugin marketplace infrastructure
- ✅ Create plugin development tools

### Phase 4: Modular Architecture Foundation (In Progress)
- ✅ Implement SuiteCRM-style name-value table system for dynamic fields
- 🔄 Create module registry and dependency management system
- ⏳ Convert core applications (Customers, Suppliers, Inventory, Manufacturing) to modules
- ⏳ Implement module lifecycle management (install/enable/disable/uninstall)
- ✅ Create base module interfaces and abstract classes

### Phase 5: Universal Hooks
1. Extend hooks.inc to support all modules
2. Add hook registration systemcd c:\Users\prote\FA

# Check git status
git status

# Add all changes
git add .

# Commit with a descriptive message
git commit -m "TDD Refactor: Complete plugin system testing and legacy call replacements

- Added comprehensive PluginManagerTest with 15 test methods
- Implemented mock objects for isolated testing
- Replaced add_gl_trans calls with BankingService::addGlTrans
- Updated HTML library to use UserPrefsCache::getPriceDecimals
- Added static wrapper tests for BankingService
- Maintained backward compatibility throughout"

# Push to the current branch
git push origin refactor/replace-legacy-callscd c:\Users\prote\FA

# Check git status
git status

# Add all changes
git add .

# Commit with a descriptive message
git commit -m "TDD Refactor: Complete plugin system testing and legacy call replacements

- Added comprehensive PluginManagerTest with 15 test methods
- Implemented mock objects for isolated testing
- Replaced add_gl_trans calls with BankingService::addGlTrans
- Updated HTML library to use UserPrefsCache::getPriceDecimals
- Added static wrapper tests for BankingService
- Maintained backward compatibility throughout"

# Push to the current branch
git push origin refactor/replace-legacy-callscd c:\Users\prote\FA

# Check git status
git status

# Add all changes
git add .

# Commit with a descriptive message
git commit -m "TDD Refactor: Complete plugin system testing and legacy call replacements

- Added comprehensive PluginManagerTest with 15 test methods
- Implemented mock objects for isolated testing
- Replaced add_gl_trans calls with BankingService::addGlTrans
- Updated HTML library to use UserPrefsCache::getPriceDecimals
- Added static wrapper tests for BankingService
- Maintained backward compatibility throughout"

# Push to the current branch
git push origin refactor/replace-legacy-calls
3. Implement hook execution pipeline
4. Update all modules to use hooks

### Phase 6: Report System Modularization
1. Convert all existing reports to pluggable modules
2. Implement custom report builder with visual designer
3. Add report template system with customization capabilities
4. Create scheduled report execution and distribution system
5. Implement report security and access control

### Phase 7: Asset Management Extensions
1. Extend fixed assets module for loanable/issue-able assets
2. Implement asset checkout/checkin tracking system
3. Add asset reservation and booking functionality
4. Create asset maintenance scheduling and tracking
5. Enhance depreciation methods and reporting

### Phase 8: Extended Purchasing Integration
1. Integrate asset purchasing with supplier catalogs
2. Implement multi-step approval workflows for asset purchases
3. Add detailed asset specifications and requirements tracking
4. Create vendor management and contract system
5. Implement asset leasing and warranty tracking

### Phase 9: WebERP Feature Integration
1. Conduct comprehensive WebERP feature analysis and comparison
2. Implement advanced manufacturing and work order management
3. Add multi-warehouse inventory with lot/serial tracking
4. Integrate project management and time tracking
5. Implement quality control and inspection features

### Phase 10: External Integrations
1. Create IntegrationManager
2. Implement SuiteCRM connector
3. Add WooCommerce synchronization
4. Build generic API integration framework

### Phase 11: Employee System
1. Extend contact system for employees
2. Create employee management UI
3. Add employee-specific features
4. Integrate with payroll systems

### Phase 12: Unified Contacts
1. Refactor contact system architecture
2. Implement role-based contact management
3. Create unified contact UI
4. Migrate existing data

## Success Criteria

### Functional Requirements
- [ ] SuiteCRM-style dynamic fields work on all major entities
- [ ] All applications outside GL/JE converted to pluggable modules
- [ ] All reports converted to pluggable modules with custom builder
- [ ] WebERP advanced manufacturing and inventory features integrated
- [ ] Asset management supports loanable/issue-able assets with tracking
- [ ] Extended asset purchasing with supplier integration and workflows
- [ ] Event system handles 100+ concurrent events
- [ ] Plugin system supports 50+ active plugins
- [ ] All modules have pre/post database hooks
- [ ] Real-time sync with external systems < 5 seconds
- [ ] Employee management supports 1000+ employees
- [ ] Unified contacts work across all entity types


### Non-Functional Requirements
- [ ] System performance impact < 10%
- [ ] Plugin security sandboxing
- [ ] Comprehensive audit logging
- [ ] Backward compatibility maintained
- [ ] Mobile-responsive UI
- [ ] Multi-tenant support

### Business Value
- [ ] SuiteCRM-style extensibility enables rapid customization
- [ ] Modular architecture supports unlimited business growth
- [ ] Advanced asset management improves resource utilization
- [ ] WebERP integration provides enterprise-grade features
- [ ] 50% reduction in manual data entry
- [ ] Real-time business insights
- [ ] Seamless system integrations
- [ ] Scalable plugin ecosystem
- [ ] Enhanced employee management
- [ ] Improved customer/supplier relationships

## Risk Assessment

### Technical Risks
- **Modular Complexity**: Full modularization increases system complexity and maintenance overhead
- **Dynamic Fields Performance**: Name-value table pattern could impact query performance at scale
- **Module Dependencies**: Inter-module dependencies could create upgrade and compatibility issues
- **Asset Tracking Complexity**: Loanable/issue-able assets add significant workflow complexity
- **WebERP Integration**: Large-scale feature integration may introduce bugs and conflicts
- **Performance Impact**: Event system could slow down operations
- **Security**: Plugin system introduces security vulnerabilities
- **Data Integrity**: External sync could cause data conflicts
- **Complexity**: Unified contact system increases complexity

### Business Risks
- **Integration Failures**: External system dependencies
- **Data Loss**: Migration to unified contact system
- **User Adoption**: Complex new features may confuse users
- **Support Overhead**: Plugin ecosystem increases support needs

### Mitigation Strategies
- Comprehensive testing and performance monitoring
- Plugin code review and sandboxing
- Gradual rollout with feature flags
- Extensive documentation and training
- Professional services for complex integrations

## Conclusion

This expanded vision transforms FrontAccounting from a traditional ERP system into a modern, extensible business platform capable of seamless integration with external systems and supporting complex business workflows. The modular architecture will enable rapid customization and integration while maintaining the robustness and reliability that FA users expect.