<?php
declare(strict_types=1);

namespace FA\CustomFields;

/**
 * Field Renderer
 *
 * Generates HTML for custom field types
 */
class FieldRenderer
{
    /**
     * Render a custom field
     */
    public static function renderField(array $field, $value = null, array $attributes = []): string
    {
        $fieldName = $field['field_name'];
        $fieldLabel = $field['field_label'];
        $fieldType = $field['field_type'];
        $isRequired = $field['is_required'];
        $defaultValue = $field['default_value'];
        $selectOptions = $field['select_options'] ?? [];

        // Use default value if no value provided
        if ($value === null && $defaultValue !== null) {
            $value = $defaultValue;
        }

        // Generate field HTML
        $html = '<div class="custom-field custom-field-' . htmlspecialchars($fieldType) . '">';

        // Field label
        $html .= '<label for="custom_' . htmlspecialchars($fieldName) . '">';
        $html .= htmlspecialchars($fieldLabel);
        if ($isRequired) {
            $html .= ' <span class="required">*</span>';
        }
        $html .= '</label>';

        // Field input
        $html .= '<div class="field-input">';
        $html .= self::renderFieldInput($field, $value, $attributes);
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render field input based on type
     */
    private static function renderFieldInput(array $field, $value, array $attributes): string
    {
        $fieldName = $field['field_name'];
        $fieldType = $field['field_type'];
        $fieldLength = $field['field_length'];
        $isRequired = $field['is_required'];
        $selectOptions = $field['select_options'] ?? [];

        $inputName = 'custom_' . $fieldName;
        $inputId = $inputName;

        // Base attributes
        $baseAttrs = array_merge([
            'id' => $inputId,
            'name' => $inputName,
            'class' => 'custom-field-input'
        ], $attributes);

        if ($isRequired) {
            $baseAttrs['required'] = 'required';
        }

        if ($fieldLength) {
            $baseAttrs['maxlength'] = $fieldLength;
        }

        switch ($fieldType) {
            case 'text':
                return self::renderTextInput($value, $baseAttrs);

            case 'textarea':
                return self::renderTextarea($value, $baseAttrs);

            case 'number':
                $baseAttrs['type'] = 'number';
                return self::renderTextInput($value, $baseAttrs);

            case 'decimal':
                $baseAttrs['type'] = 'number';
                $baseAttrs['step'] = '0.01';
                return self::renderTextInput($value, $baseAttrs);

            case 'date':
                $baseAttrs['type'] = 'date';
                return self::renderTextInput($value, $baseAttrs);

            case 'datetime':
                $baseAttrs['type'] = 'datetime-local';
                return self::renderTextInput($value, $baseAttrs);

            case 'boolean':
                return self::renderCheckbox($value, $baseAttrs);

            case 'select':
                return self::renderSelect($value, $selectOptions, $baseAttrs);

            case 'multiselect':
                return self::renderMultiselect($value, $selectOptions, $baseAttrs);

            case 'email':
                $baseAttrs['type'] = 'email';
                return self::renderTextInput($value, $baseAttrs);

            case 'url':
                $baseAttrs['type'] = 'url';
                return self::renderTextInput($value, $baseAttrs);

            case 'phone':
                $baseAttrs['type'] = 'tel';
                return self::renderTextInput($value, $baseAttrs);

            default:
                return self::renderTextInput($value, $baseAttrs);
        }
    }

    /**
     * Render text input
     */
    private static function renderTextInput($value, array $attributes): string
    {
        $attrs = self::buildAttributes($attributes);
        $value = htmlspecialchars((string) $value);
        return "<input value=\"{$value}\" {$attrs}>";
    }

    /**
     * Render textarea
     */
    private static function renderTextarea($value, array $attributes): string
    {
        $attrs = self::buildAttributes($attributes);
        $value = htmlspecialchars((string) $value);
        return "<textarea {$attrs}>{$value}</textarea>";
    }

    /**
     * Render checkbox
     */
    private static function renderCheckbox($value, array $attributes): string
    {
        $attrs = self::buildAttributes($attributes);
        $checked = $value ? 'checked' : '';
        return "<input type=\"checkbox\" value=\"1\" {$checked} {$attrs}>";
    }

    /**
     * Render select dropdown
     */
    private static function renderSelect($value, array $options, array $attributes): string
    {
        $attrs = self::buildAttributes($attributes);
        $html = "<select {$attrs}>";

        if (!isset($attributes['required'])) {
            $html .= '<option value="">-- Select --</option>';
        }

        foreach ($options as $optionValue => $optionLabel) {
            $selected = ($value == $optionValue) ? 'selected' : '';
            $optionValue = htmlspecialchars((string) $optionValue);
            $optionLabel = htmlspecialchars((string) $optionLabel);
            $html .= "<option value=\"{$optionValue}\" {$selected}>{$optionLabel}</option>";
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Render multiselect
     */
    private static function renderMultiselect($value, array $options, array $attributes): string
    {
        $attributes['multiple'] = 'multiple';
        $attributes['size'] = isset($attributes['size']) ? $attributes['size'] : min(5, count($options));

        $attrs = self::buildAttributes($attributes);
        $html = "<select {$attrs}>";

        $selectedValues = is_array($value) ? $value : [];

        foreach ($options as $optionValue => $optionLabel) {
            $selected = in_array($optionValue, $selectedValues) ? 'selected' : '';
            $optionValue = htmlspecialchars((string) $optionValue);
            $optionLabel = htmlspecialchars((string) $optionLabel);
            $html .= "<option value=\"{$optionValue}\" {$selected}>{$optionLabel}</option>";
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Build HTML attributes string
     */
    private static function buildAttributes(array $attributes): string
    {
        $attrStrings = [];

        foreach ($attributes as $name => $value) {
            if ($value === true) {
                $attrStrings[] = htmlspecialchars($name);
            } elseif ($value !== false && $value !== null) {
                $attrStrings[] = htmlspecialchars($name) . '="' . htmlspecialchars((string) $value) . '"';
            }
        }

        return implode(' ', $attrStrings);
    }

    /**
     * Render multiple fields
     */
    public static function renderFields(array $fields, array $values = [], array $attributes = []): string
    {
        $html = '<div class="custom-fields">';

        foreach ($fields as $field) {
            $fieldName = $field['field_name'];
            $value = isset($values[$fieldName]) ? $values[$fieldName] : null;
            $html .= self::renderField($field, $value, $attributes);
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render fields grouped by field groups
     */
    public static function renderGroupedFields(array $fields, array $groups, array $values = [], array $attributes = []): string
    {
        $html = '<div class="custom-fields-grouped">';

        // Group fields by group_id
        $groupedFields = [];
        $ungroupedFields = [];

        foreach ($fields as $field) {
            $groupId = $field['group_id'];
            if ($groupId && isset($groups[$groupId])) {
                $groupedFields[$groupId][] = $field;
            } else {
                $ungroupedFields[] = $field;
            }
        }

        // Render grouped fields
        foreach ($groupedFields as $groupId => $groupFields) {
            $group = $groups[$groupId];
            $html .= self::renderFieldGroup($group, $groupFields, $values, $attributes);
        }

        // Render ungrouped fields
        if (!empty($ungroupedFields)) {
            $html .= '<div class="field-group ungrouped">';
            $html .= self::renderFields($ungroupedFields, $values, $attributes);
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render a field group
     */
    private static function renderFieldGroup(array $group, array $fields, array $values, array $attributes): string
    {
        $groupName = htmlspecialchars($group['group_name']);
        $groupLabel = htmlspecialchars($group['group_label']);
        $isCollapsible = $group['is_collapsible'];

        $html = '<div class="field-group" data-group="' . $groupName . '">';

        if ($isCollapsible) {
            $html .= '<div class="group-header collapsible" onclick="toggleFieldGroup(this)">';
            $html .= '<h4>' . $groupLabel . '</h4>';
            $html .= '<span class="toggle-icon">▼</span>';
            $html .= '</div>';
            $html .= '<div class="group-content">';
        } else {
            $html .= '<h4>' . $groupLabel . '</h4>';
            $html .= '<div class="group-content always-visible">';
        }

        $html .= self::renderFields($fields, $values, $attributes);
        $html .= '</div></div>';

        return $html;
    }
}