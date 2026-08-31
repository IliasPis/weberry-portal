<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the application's UI locale for the current session
     * (and, once authenticated, persist it as the user's preference).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:el,en',
        ]);

        $request->session()->put('locale', $validated['locale']);

        $request->user()?->update(['locale' => $validated['locale']]);

        return back();
    }
}
