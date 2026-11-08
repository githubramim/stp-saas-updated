<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::active()->orderByDesc('is_pinned')->latest()->paginate(20);
        return view('notices.index', compact('notices'));
    }

    public function create()
    {
        return view('notices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'is_pinned' => 'sometimes|boolean',
            'expiry_date' => 'nullable|date',
        ]);
        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['institute_id'] = auth()->user()->institute_id ?? null;
        $validated['created_by'] = auth()->id();
        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('notices', 'public');
        }
        Notice::create($validated);
        return redirect()->route('notices.index')->with('success', 'Notice created.');
    }

    public function show(Notice $notice)
    {
        return view('notices.show', compact('notice'));
    }

    public function edit(Notice $notice)
    {
        return view('notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'is_pinned' => 'sometimes|boolean',
            'expiry_date' => 'nullable|date',
        ]);
        $validated['is_pinned'] = $request->boolean('is_pinned');
        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('notices', 'public');
        }
        $notice->update($validated);
        return redirect()->route('notices.index')->with('success', 'Updated.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return redirect()->route('notices.index')->with('success', 'Deleted.');
    }
}

