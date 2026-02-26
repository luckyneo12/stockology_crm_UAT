<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\LeadDocument;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadDocumentFile;
use Illuminate\Support\Facades\Auth;

class LeadDocumentController extends Controller
{
    public function index()
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $documents = LeadDocument::where('workspace_id', getActiveWorkSpace())->get();
            return view('lead::documents.index', compact('documents'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function create()
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $stages = LeadStage::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            $stages->prepend('All Stages', 0);
            return view('lead::documents.create', compact('stages'));
        }
    }

    public function store(Request $request)
    {
         if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $document = new LeadDocument();
            $document->name = $request->name;
            $document->stage_id = $request->stage_id != 0 ? $request->stage_id : null;
            $document->is_required = $request->has('is_required') ? 1 : 0;
            $document->workspace_id = getActiveWorkSpace();
            $document->created_by = Auth::user()->id;
            $document->save();

            return redirect()->route('lead-documents.index')->with('success', __('Document successfully created.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function edit($id)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $document = LeadDocument::find($id);
            $stages = LeadStage::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            $stages->prepend('All Stages', 0);
            return view('lead::documents.edit', compact('document', 'stages'));
        }
    }

    public function update(Request $request, $id)
    {
         if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $document = LeadDocument::find($id);
            $document->name = $request->name;
            $document->stage_id = $request->stage_id != 0 ? $request->stage_id : null;
            $document->is_required = $request->has('is_required') ? 1 : 0;
            $document->save();
            return redirect()->route('lead-documents.index')->with('success', __('Document successfully updated.'));
        }
    }

    public function destroy($id)
    {
         if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $document = LeadDocument::find($id);
            $document->delete();
            return redirect()->route('lead-documents.index')->with('success', __('Document successfully deleted.'));
        }
    }

    public function upload(Request $request, $id, $document_id)
    {
        $lead = Lead::find($id);
        $document = LeadDocument::find($document_id);
        
        $validator = \Validator::make(
            $request->all(),
            [
                'file' => 'required',
            ]
        );

        if ($validator->fails()) {
             return response()->json(['is_success' => false, 'error' => $validator->getMessageBag()->first()]);
        }
        
        $file = $request->file('file');
        $file_name = $file->getClientOriginalName();
        $file_path = "lead_documents/" . $id . "/" . md5(time()) . "_" . $file_name;
        
        
        if(function_exists('upload_file')){
             $result = upload_file($request, 'file', $file_name, 'lead_documents');
             if($result['flag'] == 1){
                 $file_path = $result['url'];
             } else {
                 return redirect()->back()->with('error', $result['msg']);
             }
        } else {
             $request->file('file')->storeAs('lead_documents', $file_path);
        }

        $docFile = new LeadDocumentFile();
        $docFile->lead_id = $lead->id;
        $docFile->document_id = $document->id;
        $docFile->file_name = $file_name;
        $docFile->file_path = $file_path; // Or result url
        $docFile->save();
        
        return redirect()->back()->with('success', __('File successfully uploaded.'));
    }

    public function deleteFile($id, $file_id)
    {
        $file = LeadDocumentFile::find($file_id);
        if($file){
            $path = $file->file_path;
            // Delete from storage
             if(file_exists(storage_path('lead_documents/'.$path))){
                 // delete
             }
            $file->delete();
            return redirect()->back()->with('success', __('File successfully deleted.'));
        }
        return redirect()->back()->with('error', __('File not found.'));
    }
}
