<?php

namespace Workdo\Ekyc\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Ekyc\Entities\EkycPipeline;
use Illuminate\Support\Facades\Auth;

class EkycPipelineController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $pipelines = EkycPipeline::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->get();
            return view('ekyc::pipelines.index', compact('pipelines'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            return view('ekyc::pipelines.create');
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
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $pipeline             = new EkycPipeline();
            $pipeline->name       = $request->name;
            $pipeline->created_by = creatorId();
            $pipeline->workspace_id = getActiveWorkSpace();
            $pipeline->save();

            return redirect()->route('ekyc.pipelines.index')->with('success', __('The pipeline has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $pipeline = EkycPipeline::find($id);
            if($pipeline->created_by == creatorId() && $pipeline->workspace_id == getActiveWorkSpace())
            {
                return view('ekyc::pipelines.edit', compact('pipeline'));
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
            $pipeline = EkycPipeline::find($id);
            if ($pipeline->created_by == creatorId() && $pipeline->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|string|max:30',
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $pipeline->name = $request->name;
                $pipeline->save();

                return redirect()->route('ekyc.pipelines.index')->with('success', __('The pipeline is updated successfully.'));
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
            $pipeline = EkycPipeline::find($id);
            if ($pipeline->created_by == creatorId() && $pipeline->workspace_id == getActiveWorkSpace()) {
                if (count($pipeline->stages) == 0) {
                    $pipeline->delete();
                    return redirect()->route('ekyc.pipelines.index')->with('success', __('The pipeline has been deleted.'));
                } else {
                    return redirect()->route('ekyc.pipelines.index')->with('error', __('There are some stages in the pipeline, please remove them first.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
