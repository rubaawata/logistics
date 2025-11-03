<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\DeliveryAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DeliveriesLoginController extends Controller
{
    protected DeliveryAuthService $authService;

    public function __construct(DeliveryAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return view('deliveries.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required'],
            'password' => ['required'],
        ]);

        try {
            $this->authService->login($validated['phone'], $validated['password']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        return redirect()->route('deliveries.login')->with('success', 'Logout successful');
    }
}
