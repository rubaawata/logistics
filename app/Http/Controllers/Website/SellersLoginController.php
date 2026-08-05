<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\SellerAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SellersLoginController extends Controller
{
    protected SellerAuthService $authService;

    public function __construct(SellerAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        if ($this->authService->check()) {
            return redirect()->route('sellers.packages.index');
        }

        return view('sellers.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required'],
            'password' => ['required'],
        ]);

        // Throws a ValidationException (handled as a redirect-back with errors) on failure.
        $this->authService->login($validated['phone'], $validated['password']);

        // Prevent session fixation now that the seller is authenticated.
        $request->session()->regenerate();

        return redirect()->route('sellers.packages.index')->with('success', 'تم تسجيل الدخول بنجاح');
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('sellers.login')->with('success', 'تم تسجيل الخروج بنجاح');
    }
}
