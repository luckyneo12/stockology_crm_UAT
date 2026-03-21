<?php

namespace Workdo\StockMarket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\StockMarket\Entities\StockCategory;

class StockCategoryController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAbleTo('stock category manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categories = StockCategory::where('workspace', getActiveWorkSpace())
            ->orWhere('created_by', creatorId())
            ->withCount('signals')
            ->get();

        return view('stockmarket::categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('stock category create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate(['name' => 'required|string|max:100']);

        StockCategory::create([
            'name' => $request->name,
            'type' => $request->type ?? 'equity',
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', __('Category created successfully!'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('stock category edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $category = StockCategory::findOrFail($id);
        $category->update(['name' => $request->name, 'type' => $request->type]);

        return redirect()->back()->with('success', __('Category updated successfully!'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('stock category delete')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        StockCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', __('Category deleted successfully!'));
    }
}
