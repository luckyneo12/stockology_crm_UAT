<?php

namespace Workdo\Ekyc\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Ekyc\Entities\EkycUiTemplate;
use Workdo\Ekyc\Entities\EkycUiComponent;

class EkycUiBuilderController extends Controller
{
    /**
     * Display the UI builder interface
     */
    public function index()
    {
        $templates = EkycUiTemplate::with('components')->get();
        $activeTemplate = EkycUiTemplate::getActive();

        return view('ekyc::ui-builder.index', compact('templates', 'activeTemplate'));
    }

    /**
     * Save UI template
     */
    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'configuration' => 'required|array',
            'components' => 'nullable|array',
        ]);

        $template = EkycUiTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'configuration' => $request->configuration,
            'is_active' => false,
            'is_default' => false,
            'created_by' => auth()->id(),
        ]);

        // Save components if provided
        if ($request->has('components')) {
            foreach ($request->components as $componentData) {
                EkycUiComponent::create([
                    'template_id' => $template->id,
                    'step_number' => $componentData['step_number'] ?? 1,
                    'component_type' => $componentData['type'],
                    'configuration' => $componentData['configuration'] ?? [],
                    'display_order' => $componentData['order'] ?? 0,
                    'is_visible' => $componentData['visible'] ?? true,
                    'is_required' => $componentData['required'] ?? false,
                    'conditional_logic' => $componentData['conditional_logic'] ?? null,
                    'validation_rules' => $componentData['validation_rules'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully',
            'template_id' => $template->id,
        ]);
    }

    /**
     * Preview template
     */
    public function preview($templateId)
    {
        $template = EkycUiTemplate::with('components')->findOrFail($templateId);

        return view('ekyc::ui-builder.preview', compact('template'));
    }

    /**
     * Activate template
     */
    public function activate($templateId)
    {
        $template = EkycUiTemplate::findOrFail($templateId);
        $template->activate();

        return response()->json([
            'success' => true,
            'message' => 'Template activated successfully',
        ]);
    }
}
