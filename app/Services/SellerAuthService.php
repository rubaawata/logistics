<?php

namespace App\Services;

use App\Repositories\SellerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SellerAuthService
{
    protected SellerRepository $repository;

    public function __construct(SellerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Attempt to log a seller in using the "seller" session guard.
     *
     * @throws ValidationException
     */
    public function login(string $phone, string $password): void
    {
        $success = Auth::guard('seller')->attempt([
            'phone_number' => $phone,
            'password' => $password,
        ]);

        if (!$success) {
            throw ValidationException::withMessages([
                'phone' => 'رقم الهاتف أو كلمة المرور غير صحيحة.',
            ]);
        }
    }

    public function logout(): void
    {
        Auth::guard('seller')->logout();
    }

    public function getAuthUser()
    {
        return Auth::guard('seller')->user();
    }

    public function check(): bool
    {
        return Auth::guard('seller')->check();
    }
}
