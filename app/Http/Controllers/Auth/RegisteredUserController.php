<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
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
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'department_id' => ['required', 'exists:departments,id'],
            'role' => ['required', 'in:employee,supervisor,admin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
            'role' => $request->role,
        ]);

        // Create employee record
        $nameParts = explode(' ', $request->name, 3);
        $firstName = $nameParts[0] ?? '';
        $middleName = $nameParts[1] ?? null;
        $lastName = $nameParts[2] ?? ($nameParts[1] ?? '');

        // Map role to position
        $positionMap = [
            'employee' => 'Employee',
            'supervisor' => 'Supervisor',
            'admin' => 'Administrator',
        ];

        Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-'.str_pad($user->id, 3, '0', STR_PAD_LEFT),
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'department_id' => $request->department_id,
            'position' => $positionMap[$request->role] ?? 'Employee',
            'date_hired' => now(),
            'status' => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
