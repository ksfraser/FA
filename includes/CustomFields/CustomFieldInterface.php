<?php
declare(strict_types=1);

namespace FA\CustomFields;

/**
 * Custom Field Interface
 *
 * Defines operations for custom fields
 */
interface CustomFieldInterface
{
    /**
     * Get field name
     */
    public function getName(): string;

    /**
     * Get field label
     */
    public function getLabel(): string;

    /**
     * Get field type
     */
    public function getType(): string;

    /**
     * Get field configuration
     */
    public function getConfig(): array;

    /**
     * Validate field value
     */
    public function validate($value): bool;

    /**
     * Render field HTML
     */
    public function render($value = null, array $attributes = []): string;

    /**
     * Sanitize field value
     */
    public function sanitize($value);
}

/**
 * Custom Field Manager Interface
 */
interface CustomFieldManagerInterface
{
    /**
     * Create a custom field
     */
    public function createField(string $entityType, array $fieldData): int;

    /**
     * Update a custom field
     */
    public function updateField(int $fieldId, array $fieldData): bool;

    /**
     * Delete a custom field
     */
    public function deleteField(int $fieldId): bool;

    /**
     * Get field definition
     */
    public function getField(int $fieldId): ?array;

    /**
     * Get fields for entity type
     */
    public function getFieldsForEntity(string $entityType): array;

    /**
     * Set field value for entity
     */
    public function setFieldValue(string $entityType, int $entityId, string $fieldName, $value): bool;

    /**
     * Get field value for entity
     */
    public function getFieldValue(string $entityType, int $entityId, string $fieldName);

    /**
     * Get all field values for entity
     */
    public function getFieldValues(string $entityType, int $entityId): array;

    /**
     * Delete all field values for entity
     */
    public function deleteFieldValues(string $entityType, int $entityId): bool;
}