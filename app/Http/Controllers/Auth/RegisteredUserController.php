<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'institute_name' => ['required', 'string', 'max:255'],
            'institute_code' => ['required', 'string', 'max:50', 'unique:institutes,code'],
            'mobile' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'admin_name' => ['required', 'string', 'max:255'],
        ]);

        $institute = Institute::create([
            'name' => $request->institute_name,
            'code' => strtoupper($request->institute_code),
            'mobile' => $request->mobile,
            'email' => $request->email,
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => $request->admin_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'institute_id' => $institute->id,
            'phone' => $request->mobile,
            'role' => 'admin',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
