<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    public function index()
    {
        $resources = Resource::where('institute_id', Auth::user()->institute_id)
            ->orderBy('location_lab')
            ->orderBy('resource_name')
            ->get();

        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')
            ->get();

        return view('resources.index', compact('resources', 'courses'));
    }

    public function create()
    {
        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')
            ->get();

        return view('resources.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'resource_name' => 'required|string|max:255',
            'resource_code' => 'required|string|unique:resources,resource_code',
            'location_lab' => 'required|string|max:255',
            'condition' => 'required|in:good,damaged,under_maintenance',
            'description' => 'nullable|string',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
        ]);

        $resource = Resource::create([
            'institute_id' => Auth::user()->institute_id,
            'resource_name' => $request->resource_name,
            'resource_code' => $request->resource_code,
            'location_lab' => $request->location_lab,
            'condition' => $request->condition,
            'description' => $request->description,
        ]);

        if ($request->courses) {
            $resource->courses()->sync($request->courses);
        }

        return redirect()->route('resources.index')
            ->with('success', 'Resource added successfully.');
    }

    public function edit(Resource $resource)
    {
        abort_if($resource->institute_id !== Auth::user()->institute_id, 403);

        $courses = Course::where('institute_id', Auth::user()->institute_id)
            ->where('status', 'active')
            ->get();

        return view('resources.edit', compact('resource', 'courses'));
    }

    public function update(Request $request, Resource $resource)
    {
        abort_if($resource->institute_id !== Auth::user()->institute_id, 403);

        $request->validate([
            'resource_name' => 'required|string|max:255',
            'resource_code' => 'required|string|unique:resources,resource_code,' . $resource->id,
            'location_lab' => 'required|string|max:255',
            'condition' => 'required|in:good,damaged,under_maintenance',
            'description' => 'nullable|string',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
        ]);

        $resource->update($request->only([
            'resource_name', 'resource_code', 'location_lab', 'condition', 'description',
        ]));

        if ($request->courses) {
            $resource->courses()->sync($request->courses);
        }

        return redirect()->route('resources.index')
            ->with('success', 'Resource updated successfully.');
    }

    public function destroy(Resource $resource)
    {
        abort_if($resource->institute_id !== Auth::user()->institute_id, 403);
        $resource->delete();

        return redirect()->route('resources.index')
            ->with('success', 'Resource deleted successfully.');
    }
}
