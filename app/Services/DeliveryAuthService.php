<?php

namespace App\Services;

use App\Repositories\DeliveryRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeliveryAuthService
{
    protected DeliveryRepository $repository;

    public function __construct(DeliveryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function login(string $phone, string $password)
    {
        $success = Auth::guard('delivery')->attempt([
            'phone_number' => $phone,
            'password' => $password
        ]);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => "Invalid phone number or password."
            ]);
        }
    }

    public function logout(): void
    {
        Auth::guard('delivery')->logout();
    }

    public function getAuthUser()
    {
        return Auth::guard('delivery')->user();
    }

    public function check(): bool
    {
        return Auth::guard('delivery')->check();
    }
}
