<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    public function index()
    {
        $segments = Segment::where('is_active', true)->get();
        return view('admin.segments.index', compact('segments'));
    }

    public function create()
    {
        return view('admin.segments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:segments',
        ]);

        Segment::create($request->all());

        return redirect()->route('admin.segments.index')->with('success', __('admin.created_successfully'));
    }

    public function edit(Segment $segment)
    {
        return view('admin.segments.edit', compact('segment'));
    }

    public function update(Request $request, Segment $segment)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:segments,slug,' . $segment->id,
        ]);

        $segment->update($request->all());

        return redirect()->route('admin.segments.index')->with('success', __('admin.updated_successfully'));
    }

    public function destroy(Segment $segment)
    {
        $segment->update(['is_active' => false]);
        return redirect()->route('admin.segments.index')->with('success', __('admin.deleted_successfully'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:segments,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->action === 'activate';
        Segment::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return redirect()->back()->with('success', __('admin.bulk_action_success'));
    }
}
