<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstituteController extends Controller
{
    public function edit()
    {
        $institute = Auth::user()->institute;
        abort_if(!$institute, 404);
        return view('institutes.edit', compact('institute'));
    }

    public function update(Request $request)
    {
        $institute = Auth::user()->institute;
        abort_if(!$institute, 404);

        $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:institutes,email,' . $institute->id,
            'address' => 'nullable|string',
        ]);

        $institute->update($request->only(['name', 'name_bn', 'mobile', 'email', 'address']));

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg|max:2048']);
            $path = $request->file('logo')->store('logos', 'public');
            $institute->update(['logo' => $path]);
        }

        return redirect()->route('institute.edit')->with('success', 'Profile updated successfully.');
    }
}
