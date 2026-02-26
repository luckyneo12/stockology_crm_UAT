<?php

namespace Workdo\Ekyc\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Ekyc\Entities\EkycCustomField;
use Illuminate\Support\Facades\Auth;

class EkycCustomFieldController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $customFields = EkycCustomField::where('created_by', '=', creatorId())
                ->where('workspace_id', '=', getActiveWorkSpace())
                ->orderBy('order')
                ->get();
            return view('ekyc::custom_fields.index', compact('customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $fieldTypes = EkycCustomField::$fieldTypes;
            return view('ekyc::custom_fields.create', compact('fieldTypes'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'type' => 'required|in:' . implode(',', array_keys(EkycCustomField::$fieldTypes)),
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $customField = new EkycCustomField();
            $customField->name = $request->name;
            $customField->type = $request->type;
            $customField->created_by = creatorId();
            $customField->workspace_id = getActiveWorkSpace();
            $customField->order = EkycCustomField::where('created_by', creatorId())
                ->where('workspace_id', getActiveWorkSpace())
                ->max('order') + 1;
            $customField->save();

            return redirect()->route('ekyc.custom-fields.index')->with('success', __('Custom field created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $customField = EkycCustomField::find($id);
            if($customField->created_by == creatorId() && $customField->workspace_id == getActiveWorkSpace())
            {
                $fieldTypes = EkycCustomField::$fieldTypes;
                return view('ekyc::custom_fields.edit', compact('customField', 'fieldTypes'));
            }
            else
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $customField = EkycCustomField::find($id);
            if ($customField->created_by == creatorId() && $customField->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|string|max:255',
                        'type' => 'required|in:' . implode(',', array_keys(EkycCustomField::$fieldTypes)),
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $customField->name = $request->name;
                $customField->type = $request->type;
                $customField->save();

                return redirect()->route('ekyc.custom-fields.index')->with('success', __('Custom field updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $customField = EkycCustomField::find($id);
            if ($customField->created_by == creatorId() && $customField->workspace_id == getActiveWorkSpace()) {
                $customField->delete();
                return redirect()->route('ekyc.custom-fields.index')->with('success', __('Custom field deleted successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
