<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ESignTemplate;
use App\Models\ESignTemplateField;
use Illuminate\Support\Facades\Storage;

class ESignTemplateController extends Controller
{
    public function index()
    {
        $templates = ESignTemplate::withCount('fields')->get();
        $leads = \Workdo\Lead\Entities\Lead::where('workspace_id', getActiveWorkSpace())->get();
        return view('esign_templates.index', compact('templates', 'leads'));
    }

    public function create()
    {
        return view('esign_templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // Limit to 10MB
        ]);

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $fileName = 'templates/' . time() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->put($fileName, file_get_contents($file));

            $template = ESignTemplate::create([
                'name' => $request->name,
                'pdf_url' => 'storage/' . $fileName
            ]);

            return redirect()->route('esign-templates.edit', $template->id)->with('success', 'Template uploaded successfully! Now map your variables.');
        }

        return back()->with('error', 'Failed to upload PDF file.');
    }

    public function edit($id)
    {
        $template = ESignTemplate::with('fields')->findOrFail($id);
        return view('esign_templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = ESignTemplate::findOrFail($id);

        if ($request->hasFile('pdf_file')) {
            $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:10240',
            ]);

            // Delete old file
            $oldPath = str_replace('storage/', '', $template->pdf_url);
            Storage::disk('public')->delete($oldPath);

            $file = $request->file('pdf_file');
            $fileName = 'templates/' . time() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->put($fileName, file_get_contents($file));

            $template->update([
                'pdf_url' => 'storage/' . $fileName
            ]);

            return redirect()->back()->with('success', 'Template PDF file replaced successfully!');
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $template->update([
            'name' => $request->name
        ]);

        return redirect()->route('esign-templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = ESignTemplate::findOrFail($id);
        // Delete PDF file if exists
        $filePath = str_replace('storage/', '', $template->pdf_url);
        Storage::disk('public')->delete($filePath);

        $template->delete();

        return redirect()->route('esign-templates.index')->with('success', 'Template deleted successfully.');
    }

    /**
     * Custom endpoint to add field coordinates
     */
    public function addField(Request $request, $templateId)
    {
        $request->validate([
            'field_key' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,signature,checkmark,whiteout',
            'page_num' => 'required|integer|min:1',
            'x_coordinate' => 'required|numeric',
            'y_coordinate' => 'required|numeric',
            'width' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
        ]);

        $template = ESignTemplate::findOrFail($templateId);

        $template->fields()->create($request->all());

        return back()->with('success', 'Field coordinate added successfully.');
    }

    /**
     * Custom endpoint to delete field coordinates
     */
    /**
     * Custom endpoint to delete field coordinates
     */
    public function removeField($fieldId)
    {
        $field = ESignTemplateField::findOrFail($fieldId);
        $field->delete();

        return back()->with('success', 'Field coordinate removed.');
    }

    /**
     * Batch save field coordinates from the visual builder
     */
    public function saveBatchFields(Request $request, $id)
    {
        $template = ESignTemplate::findOrFail($id);

        // Delete existing mappings
        $template->fields()->delete();

        $fields = $request->input('fields', []);
        foreach ($fields as $field) {
            $template->fields()->create([
                'field_key' => $field['field_key'],
                'label' => $field['label'],
                'type' => $field['type'],
                'page_num' => (int) $field['page_num'],
                'x_coordinate' => (float) $field['x_coordinate'],
                'y_coordinate' => (float) $field['y_coordinate'],
                'width' => (float) $field['width'],
                'height' => (float) $field['height'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'All variable coordinates saved successfully!'
        ]);
    }

    /**
     * Streams the PDF file dynamically to bypass server config path / symlink 404s
     */
    public function streamPdf($id)
    {
        $template = ESignTemplate::findOrFail($id);
        $filePath = str_replace('storage/', '', $template->pdf_url);

        // 1. Check local public storage disk
        if (Storage::disk('public')->exists($filePath)) {
            $fileContents = Storage::disk('public')->get($filePath);
            return response($fileContents, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($template->pdf_url) . '"'
            ]);
        }

        // 2. Fallback to public/ directory directly
        $publicPath = public_path($template->pdf_url);
        if (file_exists($publicPath)) {
            $fileContents = file_get_contents($publicPath);
            return response($fileContents, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($template->pdf_url) . '"'
            ]);
        }

        // 3. Absolute fallback to root public/storage if path mismatch
        $altPublicPath = public_path('storage/' . $filePath);
        if (file_exists($altPublicPath)) {
            $fileContents = file_get_contents($altPublicPath);
            return response($fileContents, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($template->pdf_url) . '"'
            ]);
        }

        abort(404, 'PDF file not found.');
    }

    /**
     * Renders visual PDF form filling page for a Lead.
     */
    public function fillPdfForm($leadId, $templateId = null)
    {
        $lead = \Workdo\Lead\Entities\Lead::findOrFail($leadId);
        $templates = ESignTemplate::all();
        
        $selectedTemplate = null;
        if ($templateId) {
            $selectedTemplate = ESignTemplate::with('fields')->findOrFail($templateId);
        } elseif ($templates->count() > 0) {
            // Default to template ID 2 if exists, otherwise first template
            $selectedTemplate = ESignTemplate::with('fields')->find(2) ?: ESignTemplate::with('fields')->first();
        }

        // Fetch dedicated Lead custom fields values
        $leadCustomFieldValues = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)
            ->pluck('value', 'field_id')
            ->toArray();

        // Get dedicated Lead custom field names definitions
        $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();

        return view('esign_templates.fill', compact('lead', 'templates', 'selectedTemplate', 'leadCustomFieldValues', 'customFields'));
    }

    /**
     * Renders visual PDF form filling page for a guest/client (public access).
     */
    public function fillPdfFormPublic($templateId, $leadId = null)
    {
        $templates = ESignTemplate::all();
        $selectedTemplate = ESignTemplate::with('fields')->findOrFail($templateId);
        
        $lead = null;
        $leadCustomFieldValues = [];
        $customFields = collect();

        if ($leadId) {
            $lead = \Workdo\Lead\Entities\Lead::find($leadId);
            if ($lead) {
                // Fetch dedicated Lead custom fields values
                $leadCustomFieldValues = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)
                    ->pluck('value', 'field_id')
                    ->toArray();

                // Get dedicated Lead custom field names definitions (using lead workspace instead of session active workspace)
                $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $lead->workspace_id)->get();
            }
        }

        return view('esign_templates.fill_public', compact('lead', 'templates', 'selectedTemplate', 'leadCustomFieldValues', 'customFields'));
    }
}
