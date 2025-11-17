<?php

namespace FA;

/**
 * Access Levels Service
 *
 * Manages security sections and areas for access control.
 * Refactored from global arrays to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages access levels only
 * - Open/Closed: Can be extended for additional security features
 * - Liskov Substitution: Compatible with access interfaces
 * - Interface Segregation: Focused access methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses access logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | AccessLevelsService|
 * +---------------------+
 * | - security_sections: array|
 * | - security_areas: array|
 * +---------------------+
 * | + getSecuritySections()|
 * | + getSecurityAreas() |
 * | + isAreaAllowed(area,roles)|
 * +---------------------+
 *
 * @package FA
 */
class AccessLevelsService
{
    private array $security_sections = [
        SS_SADMIN => _("System administration"),
        SS_SETUP => _("Company setup"),
        SS_SPEC => _("Special maintenance"),
        SS_SALES_C => _("Sales configuration"),
        SS_SALES => _("Sales transactions"),
        SS_SALES_A => _("Sales related reports"),
        SS_PURCH_C => _("Purchase configuration"),
        SS_PURCH => _("Purchase transactions"),
        SS_PURCH_A => _("Purchase analytics"),
        SS_ITEMS_C => _("Inventory configuration"),
        SS_ITEMS => _("Inventory operations"),
        SS_ITEMS_A => _("Inventory analytics"),
        SS_ASSETS_C => _("Fixed Assets configuration"),
        SS_ASSETS => _("Fixed Assets operations"),
        SS_ASSETS_A => _("Fixed Assets analytics"),
        SS_MANUF_C => _("Manufacturing configuration"),
        SS_MANUF => _("Manufacturing transactions"),
        SS_MANUF_A => _("Manufacturing analytics"),
        SS_DIM_C => _("Dimensions configuration"),
        SS_DIM => _("Dimensions"),
        SS_GL_C => _("Banking & GL configuration"),
        SS_GL => _("Banking & GL transactions"),
        SS_GL_A => _("Banking & GL analytics")
    ];

    private array $security_areas = [
        'SA_CREATECOMPANY' => [SS_SADMIN|1, _("Install/update companies")],
        // Add more as needed
    ];

    /**
     * Get security sections
     *
     * @return array Security sections
     */
    public function getSecuritySections(): array
    {
        return $this->security_sections;
    }

    /**
     * Get security areas
     *
     * @return array Security areas
     */
    public function getSecurityAreas(): array
    {
        return $this->security_areas;
    }

    /**
     * Check if area is allowed for user roles
     *
     * @param string $area Area code
     * @param array $user_roles User roles
     * @return bool True if allowed
     */
    public function isAreaAllowed(string $area, array $user_roles): bool
    {
        if (!isset($this->security_areas[$area])) return false;
        $required = $this->security_areas[$area][0];
        return in_array($required, $user_roles);
    }
}