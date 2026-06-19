<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Workdo\Lead\Entities\Lead;
use App\Models\ESignTemplate;
use App\Models\ESignTemplateField;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ESignController extends Controller
{
    /**
     * KYC Portal calls this to fetch template and variables
     */
    public function getTemplateConfig(Request $request, $id)
    {
        $template = ESignTemplate::with('fields')->find($id);
        
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ], 404);
        }

        $leadId = $request->query('lead_id');
        $lead = Lead::find($leadId);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found'
            ], 404);
        }

        // Prepare prefilled data mapped to template fields
        $prefilledData = [
            'full_name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'pan_number' => $lead->pan_number ?? '',
            'aadhar_number' => $lead->aadhar_number ?? '',
        ];

        return response()->json([
            'success' => true,
            'template_name' => $template->name,
            'pdf_template_url' => url($template->pdf_url),
            'prefilled_data' => $prefilledData,
            'fields' => $template->fields->map(function ($field) {
                return [
                    'key' => $field->field_key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'page' => $field->page_num,
                    'x' => $field->x_coordinate,
                    'y' => $field->y_coordinate,
                    'width' => $field->width,
                    'height' => $field->height
                ];
            })
        ]);
    }

    /**
     * KYC Portal backend calls this webhook when document is fully signed
     */
    public function saveSignedDocument(Request $request)
    {
        $leadId = $request->input('lead_id');
        $signedPdfUrl = $request->input('signed_pdf_url');
        $variables = $request->input('variables', []);

        $lead = null;
        if ($leadId) {
            $lead = Lead::find($leadId);
        }

        // If lead is not found by ID, match by variables (email or phone)
        if (!$lead) {
            $email = null;
            $phone = null;
            $fullName = 'New Client';

            foreach ($variables as $key => $value) {
                $normKey = strtolower($key);
                if ($normKey === 'email') {
                    $email = $value;
                } elseif (in_array($normKey, ['phone', 'mobile', 'phone_number'])) {
                    $phone = $value;
                } elseif (in_array($normKey, ['name', 'full_name'])) {
                    $fullName = $value;
                }
            }

            if ($email) {
                $lead = Lead::where('email', $email)->first();
            }
            if (!$lead && $phone) {
                $lead = Lead::where('phone', $phone)->first();
            }

            // If still not found, create a new Lead
            if (!$lead) {
                $workspace = \App\Models\Workspace::first();
                $workspaceId = $workspace ? $workspace->id : 1;

                $user = \App\Models\User::where('type', 'company')->first();
                $createdBy = $user ? $user->id : 1;

                $lead = new Lead();
                $lead->name = $fullName;
                $lead->email = $email;
                $lead->phone = $phone;
                $lead->workspace_id = $workspaceId;
                $lead->created_by = $createdBy;
                $lead->pipeline_id = 1;
                $lead->stage_id = 1;
                $lead->save();
            }
        }

        // Save incoming variables onto lead fields and custom fields
        if ($lead) {
            $leadChanged = false;
            foreach ($variables as $key => $value) {
                $normKey = strtolower($key);
                if (($normKey === 'full_name' || $normKey === 'name') && $lead->name !== $value) {
                    $lead->name = $value;
                    $leadChanged = true;
                } elseif ($normKey === 'email' && $lead->email !== $value) {
                    $lead->email = $value;
                    $leadChanged = true;
                } elseif (($normKey === 'phone' || $normKey === 'mobile') && $lead->phone !== $value) {
                    $lead->phone = $value;
                    $leadChanged = true;
                } elseif (($normKey === 'pan_number' || $normKey === 'pan') && ($lead->pan_number ?? '') !== $value) {
                    $lead->pan_number = $value;
                    $leadChanged = true;
                } elseif (($normKey === 'aadhar_number' || $normKey === 'aadhar' || $normKey === 'aadhaar') && ($lead->aadhar_number ?? '') !== $value) {
                    $lead->aadhar_number = $value;
                    $leadChanged = true;
                } elseif (($normKey === 'dp_id' || $normKey === 'client_code') && ($lead->dp_id ?? '') !== $value) {
                    $lead->dp_id = $value;
                    $leadChanged = true;
                }
            }
            if ($leadChanged) {
                $lead->save();
            }

            // Save mapped custom fields
            $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $lead->workspace_id)->get();
            foreach ($customFields as $cf) {
                $cfKey = strtolower(str_replace(' ', '_', $cf->name));
                $normCfName = preg_replace('/[^a-z0-9]/', '', strtolower($cf->name));

                foreach ($variables as $varKey => $varVal) {
                    $normVarKey = preg_replace('/[^a-z0-9]/', '', strtolower($varKey));
                    if ($cfKey === strtolower($varKey) || $normCfName === $normVarKey || 'custom_' . $cf->id === strtolower($varKey)) {
                        \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate([
                            'lead_id' => $lead->id,
                            'field_id' => $cf->id
                        ], [
                            'value' => $varVal
                        ]);
                    }
                }
            }
        }
        try {
            $pdfContents = null;
            $hasUploadedFile = $request->hasFile('signed_pdf_file');

            // Check if file is uploaded directly from client compiler
            if ($hasUploadedFile) {
                $pdfContents = file_get_contents($request->file('signed_pdf_file')->getRealPath());
            }

            // 1. Check if it is a local request pointing to our streamPdf endpoint to bypass loopback issues
            if (!$hasUploadedFile && !$pdfContents && $signedPdfUrl && preg_match('/esign-templates\/(\d+)\/pdf/', $signedPdfUrl, $matches)) {
                $templateId = $matches[1];
                $template = ESignTemplate::find($templateId);
                if ($template) {
                    $filePath = str_replace('storage/', '', $template->pdf_url);
                    if (Storage::disk('public')->exists($filePath)) {
                        $pdfContents = Storage::disk('public')->get($filePath);
                    }
                }
            }

            // 2. Fallback to network request if not local or local check failed
            if (!$hasUploadedFile && !$pdfContents && $signedPdfUrl) {
                $pdfResponse = Http::get($signedPdfUrl);
                if ($pdfResponse->successful()) {
                    $pdfContents = $pdfResponse->body();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to download signed PDF: ' . $pdfResponse->body()
                    ], 400);
                }
            }

            if ($pdfContents !== null && $pdfContents !== false) {
                $fileName = 'signed_docs/lead_' . $leadId . '_' . time() . '.pdf';
                Storage::disk('public')->put($fileName, $pdfContents);

                // Update lead state
                $lead->kyc_status = 'signed';
                $lead->signed_doc_path = 'storage/' . $fileName;
                $lead->save();

                // Trigger Direct EKYC Outbound POST to Rivexa/Orion Backoffice Portal
                try {
                    \Workdo\Lead\Http\Controllers\OrionIntegrationController::triggerEkycPostDirectly($lead);
                } catch (\Exception $e) {
                    \Log::error("Direct EKYC Post trigger error: " . $e->getMessage());
                }

                // Save Activity if activities relation exists
                if (method_exists($lead, 'activities')) {
                    $lead->activities()->create([
                        'user_id' => $lead->created_by ?? 1,
                        'log_type' => 'E-Sign Complete',
                        'remark' => json_encode(['message' => 'KYC Document E-Signed and saved successfully.'])
                    ]);
                }

                return response()->json(['success' => true]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve PDF contents.'
            ], 400);

        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Callback execution error: ' . $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Direct EKYC proxy fetch by Application ID
     */
    public function fetchEkycDataDirectly($applicationId)
    {
        try {
            $url = 'https://rivexaflow.space/api/crm/' . trim($applicationId);
            $response = Http::get($url);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch data from Rivexa API: ' . $response->body()
                ], 400);
            }

            $payload = $response->json();
            
            $fields = [
                'full_name' => $this->extractDirectField($payload, 'ClientName'),
                'pan_number' => $this->extractDirectField($payload, 'PanNo'),
                'aadhar_number' => $this->extractDirectField($payload, 'Aadhar'),
                'dob' => $this->extractDirectField($payload, 'Dob'),
                'gender' => $this->extractDirectField($payload, 'Gender'),
                'marital_status' => $this->extractDirectField($payload, 'MaritalStatus'),
                'phone' => $this->extractDirectField($payload, 'ContactNo'),
                'email' => $this->extractDirectField($payload, 'ContactEmail'),
                
                'address_line_1' => $this->extractDirectField($payload, 'AddressLine1'),
                'address_line_2' => $this->extractDirectField($payload, 'AddressLine2'),
                'address_line_3' => $this->extractDirectField($payload, 'AddressLine3'),
                'address_city' => $this->extractDirectField($payload, 'AddressCity'),
                'address_pincode' => $this->extractDirectField($payload, 'AddressPincode'),
                'address_state' => $this->extractDirectField($payload, 'AddressState'),
                
                'bank_account_number' => $this->extractDirectField($payload, 'BankAccountNumber'),
                'bank_ifsc' => $this->extractDirectField($payload, 'BankIFSC'),
                'bank_name' => $this->extractDirectField($payload, 'BankName'),
                'occupation' => $this->extractDirectField($payload, 'Occupation'),
                'annual_income' => $this->extractDirectField($payload, 'AnnualIncome'),
                'networth' => $this->extractDirectField($payload, 'NetWorth'),
                'networth_date' => $this->extractDirectField($payload, 'NetWorthDate'),
                
                'nominee_name' => $this->extractDirectField($payload, 'NomineeName'),
                'nominee_relation' => $this->extractDirectField($payload, 'NomineeRelation'),
                'nominee_dob' => $this->extractDirectField($payload, 'NomineeDOB'),
                'nominee_pan' => $this->extractDirectField($payload, 'NomineePAN'),
                'nominee_share' => $this->extractDirectField($payload, 'NomineeShare'),
                'client_code' => $this->extractDirectField($payload, 'ClientCode'),
                
                'father_name' => $this->extractDirectField($payload, 'FatherName'),
                'mother_name' => $this->extractDirectField($payload, 'MotherName')
            ];

            return response()->json([
                'success' => true,
                'data' => $fields
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $ex->getMessage()
            ], 500);
        }
    }

    private function extractDirectField($payload, $fieldName)
    {
        try {
            $reflector = new \ReflectionClass(\Workdo\Lead\Http\Controllers\OrionIntegrationController::class);
            $method = $reflector->getMethod('extractOrionField');
            $method->setAccessible(true);
            
            $instance = new \Workdo\Lead\Http\Controllers\OrionIntegrationController();
            return $method->invokeArgs($instance, [$payload, $fieldName]);
        } catch (\Exception $e) {
            return null;
        }
    }
}
