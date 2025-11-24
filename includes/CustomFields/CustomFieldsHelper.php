<?php
declare(strict_types=1);

namespace FA\CustomFields;

use FA\CustomFields\CustomFieldManager;
use FA\CustomFields\FieldRenderer;

/**
 * Custom Fields Helper
 *
 * Integrates custom fields with existing FA forms and entities
 */
class CustomFieldsHelper
{
    private CustomFieldManager $fieldManager;

    public function __construct()
    {
        $this->fieldManager = CustomFieldManager::getInstance();
    }

    /**
     * Render custom fields for an entity
     */
    public function renderEntityFields(string $entityType, int $entityId = null, array $attributes = []): string
    {
        $fields = $this->fieldManager->getFieldsForEntity($entityType);

        if (empty($fields)) {
            return '';
        }

        $values = [];
        if ($entityId) {
            $values = $this->fieldManager->getFieldValues($entityType, $entityId);
        }

        return FieldRenderer::renderFields($fields, $values, $attributes);
    }

    /**
     * Save custom field values for an entity
     */
    public function saveEntityFields(string $entityType, int $entityId, array $postData): bool
    {
        $fields = $this->fieldManager->getFieldsForEntity($entityType);
        $success = true;

        foreach ($fields as $field) {
            $fieldName = $field['field_name'];
            $postKey = 'custom_' . $fieldName;

            if (isset($postData[$postKey])) {
                $value = $postData[$postKey];

                // Handle multiselect arrays
                if ($field['field_type'] === 'multiselect' && is_array($value)) {
                    $value = array_filter($value);
                }

                if (!$this->fieldManager->setFieldValue($entityType, $entityId, $fieldName, $value)) {
                    $success = false;
                }
            } elseif ($field['field_type'] === 'boolean') {
                // Handle unchecked checkboxes
                if (!$this->fieldManager->setFieldValue($entityType, $entityId, $fieldName, false)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Delete custom field values for an entity
     */
    public function deleteEntityFields(string $entityType, int $entityId): bool
    {
        return $this->fieldManager->deleteFieldValues($entityType, $entityId);
    }

    /**
     * Validate custom field values
     */
    public function validateEntityFields(string $entityType, array $postData): array
    {
        $fields = $this->fieldManager->getFieldsForEntity($entityType);
        $errors = [];

        foreach ($fields as $field) {
            $fieldName = $field['field_name'];
            $postKey = 'custom_' . $fieldName;
            $value = $postData[$postKey] ?? null;

            // Check required fields
            if ($field['is_required'] && empty($value) && $value !== '0' && $value !== false) {
                $errors[] = sprintf(_("Field '%s' is required"), $field['field_label']);
                continue;
            }

            // Skip further validation if empty and not required
            if (!$field['is_required'] && empty($value) && $value !== '0' && $value !== false) {
                continue;
            }

            // Type-specific validation
            $validationError = $this->validateFieldValue($field, $value);
            if ($validationError) {
                $errors[] = sprintf(_("Field '%s': %s"), $field['field_label'], $validationError);
            }
        }

        return $errors;
    }

    /**
     * Validate a single field value
     */
    private function validateFieldValue(array $field, $value): ?string
    {
        $fieldType = $field['field_type'];

        switch ($fieldType) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return _("Invalid email address");
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return _("Invalid URL");
                }
                break;

            case 'number':
                if (!is_numeric($value)) {
                    return _("Must be a valid number");
                }
                break;

            case 'decimal':
                if (!is_numeric($value)) {
                    return _("Must be a valid decimal number");
                }
                break;

            case 'date':
                $date = date_parse($value);
                if ($date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
                    return _("Invalid date");
                }
                break;

            case 'datetime':
                if (!strtotime($value)) {
                    return _("Invalid date/time");
                }
                break;

            case 'select':
                if (!empty($field['select_options']) && !isset($field['select_options'][$value])) {
                    return _("Invalid option selected");
                }
                break;

            case 'multiselect':
                if (is_array($value)) {
                    $validOptions = $field['select_options'] ?? [];
                    foreach ($value as $selectedValue) {
                        if (!isset($validOptions[$selectedValue])) {
                            return _("Invalid option selected");
                        }
                    }
                }
                break;
        }

        // Check field length
        if ($field['field_length'] && is_string($value) && strlen($value) > $field['field_length']) {
            return sprintf(_("Must not exceed %d characters"), $field['field_length']);
        }

        return null;
    }

    /**
     * Get custom field values as associative array
     */
    public function getEntityFieldValues(string $entityType, int $entityId): array
    {
        return $this->fieldManager->getFieldValues($entityType, $entityId);
    }

    /**
     * Get a specific custom field value
     */
    public function getEntityFieldValue(string $entityType, int $entityId, string $fieldName)
    {
        return $this->fieldManager->getFieldValue($entityType, $entityId, $fieldName);
    }

    /**
     * Check if an entity type has custom fields
     */
    public function hasCustomFields(string $entityType): bool
    {
        $fields = $this->fieldManager->getFieldsForEntity($entityType);
        return !empty($fields);
    }

    /**
     * Get field definitions for an entity type
     */
    public function getEntityFieldDefinitions(string $entityType): array
    {
        return $this->fieldManager->getFieldsForEntity($entityType);
    }

    /**
     * Export custom field data for entities
     */
    public function exportEntityFields(string $entityType, array $entityIds): array
    {
        $export = [];

        foreach ($entityIds as $entityId) {
            $export[$entityId] = $this->getEntityFieldValues($entityType, $entityId);
        }

        return $export;
    }

    /**
     * Import custom field data for entities
     */
    public function importEntityFields(string $entityType, array $importData): bool
    {
        $success = true;

        foreach ($importData as $entityId => $fieldValues) {
            foreach ($fieldValues as $fieldName => $value) {
                if (!$this->fieldManager->setFieldValue($entityType, $entityId, $fieldName, $value)) {
                    $success = false;
                }
            }
        }

        return $success;
    }
}