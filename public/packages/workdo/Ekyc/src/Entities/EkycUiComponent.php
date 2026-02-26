<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EkycUiComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'step_number',
        'component_type',
        'component_config',
        'display_order',
        'is_visible',
        'is_required',
        'conditional_logic',
        'validation_rules',
    ];

    protected $casts = [
        'component_config' => 'array',
        'is_visible' => 'boolean',
        'is_required' => 'boolean',
        'conditional_logic' => 'array',
        'validation_rules' => 'array',
    ];

    /**
     * Get the template that owns the component
     */
    public function template()
    {
        return $this->belongsTo(EkycUiTemplate::class, 'template_id');
    }

    /**
     * Check if component should be displayed based on conditional logic
     */
    public function shouldDisplay($formData = [])
    {
        if (!$this->is_visible) {
            return false;
        }

        if (empty($this->conditional_logic)) {
            return true;
        }

        // Evaluate conditional logic
        foreach ($this->conditional_logic as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '==';
            $value = $condition['value'] ?? null;

            if (!isset($formData[$field])) {
                return false;
            }

            $fieldValue = $formData[$field];

            switch ($operator) {
                case '==':
                    if ($fieldValue != $value) return false;
                    break;
                case '!=':
                    if ($fieldValue == $value) return false;
                    break;
                case 'contains':
                    if (strpos($fieldValue, $value) === false) return false;
                    break;
                case 'not_empty':
                    if (empty($fieldValue)) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Get validation rules for this component
     */
    public function getValidationRules()
    {
        $rules = $this->validation_rules ?? [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        }

        // Add default rules based on component type
        switch ($this->component_type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'phone':
                $rules[] = 'numeric';
                break;
            case 'pan':
                $rules[] = 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/';
                break;
            case 'aadhaar':
                $rules[] = 'numeric';
                $rules[] = 'digits:12';
                break;
            case 'ifsc':
                $rules[] = 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/';
                break;
        }

        return array_unique($rules);
    }

    /**
     * Scope for specific step
     */
    public function scopeForStep($query, $stepNumber)
    {
        return $query->where('step_number', $stepNumber);
    }

    /**
     * Scope for visible components
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
}
