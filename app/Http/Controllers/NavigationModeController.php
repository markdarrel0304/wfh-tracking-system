<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NavigationModeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['admin', 'employee'])],
        ]);

        $request->session()->put('navigation_mode', $validated['mode']);

        return back();
    }
}
