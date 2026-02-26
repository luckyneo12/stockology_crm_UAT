<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class EkycUiTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_default',
        'config',
        'preview_image',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Get the user who created the template
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get components for this template
     */
    public function components()
    {
        return $this->hasMany(EkycUiComponent::class, 'template_id');
    }

    /**
     * Get components for a specific step
     */
    public function componentsForStep($stepNumber)
    {
        return $this->components()
                    ->where('step_number', $stepNumber)
                    ->where('is_visible', true)
                    ->orderBy('display_order')
                    ->get();
    }

    /**
     * Activate this template (deactivate others)
     */
    public function activate()
    {
        // Deactivate all other templates
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this template
        $this->update(['is_active' => true]);
    }

    /**
     * Get the active template
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first() ?? self::getDefault();
    }

    /**
     * Get the default template
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first();
    }

    /**
     * Create default template
     */
    public static function createDefault()
    {
        return self::create([
            'name' => 'Default KYC Template',
            'description' => 'Clean and professional KYC form template',
            'is_active' => true,
            'is_default' => true,
            'config' => [
                'branding' => [
                    'logo_url' => '',
                    'company_name' => 'Company Name',
                    'tagline' => 'Complete your KYC verification',
                ],
                'colors' => [
                    'primary' => '#0033CC',
                    'secondary' => '#00CC66',
                    'accent' => '#FF6600',
                    'background' => '#F5F7FA',
                    'text' => '#1A1A1A',
                    'border' => '#E0E0E0',
                ],
                'typography' => [
                    'font_family' => "'Inter', sans-serif",
                    'heading_size' => '24px',
                    'body_size' => '16px',
                    'small_size' => '14px',
                ],
                'layout' => [
                    'container_width' => '1200px',
                    'form_width' => '450px',
                    'border_radius' => '8px',
                    'spacing' => '16px',
                ],
                'components' => [
                    'button' => [
                        'background' => '#0033CC',
                        'text_color' => '#FFFFFF',
                        'border_radius' => '4px',
                        'padding' => '12px 24px',
                    ],
                    'input' => [
                        'border_color' => '#E0E0E0',
                        'focus_border_color' => '#0033CC',
                        'border_radius' => '4px',
                        'padding' => '10px 12px',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
