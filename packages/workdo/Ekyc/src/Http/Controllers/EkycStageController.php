<?php

namespace Workdo\Ekyc\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Ekyc\Entities\EkycStage;
use Workdo\Ekyc\Entities\EkycPipeline;
use Illuminate\Support\Facades\Auth;

class EkycStageController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $stages = EkycStage::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->orderBy('order')->get();
            $pipelines = EkycPipeline::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
            return view('ekyc::stages.index', compact('stages', 'pipelines'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $pipelines = EkycPipeline::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
            return view('ekyc::stages.create', compact('pipelines'));
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
                    'name' => 'required|string|max:30',
                    'pipeline_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $stage             = new EkycStage();
            $stage->name       = $request->name;
            $stage->pipeline_id = $request->pipeline_id;
            $stage->created_by = creatorId();
            $stage->workspace_id = getActiveWorkSpace();
            $stage->save();

            return redirect()->back()->with('success', __('The stage has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $stage = EkycStage::find($id);
            if($stage->created_by == creatorId() && $stage->workspace_id == getActiveWorkSpace())
            {
                $pipelines = EkycPipeline::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
                return view('ekyc::stages.edit', compact('stage', 'pipelines'));
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
            $stage = EkycStage::find($id);
            if ($stage->created_by == creatorId() && $stage->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|string|max:30',
                        'pipeline_id' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $stage->name = $request->name;
                $stage->pipeline_id = $request->pipeline_id;
                $stage->save();

                return redirect()->back()->with('success', __('The stage is updated successfully.'));
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
            $stage = EkycStage::find($id);
            if ($stage->created_by == creatorId() && $stage->workspace_id == getActiveWorkSpace()) {
                // Check if stage is being used (optional, if there are leads attached)
                $stage->delete();
                return redirect()->back()->with('success', __('The stage has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
