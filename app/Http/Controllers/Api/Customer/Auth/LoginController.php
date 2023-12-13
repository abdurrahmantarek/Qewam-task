<?php

namespace App\Http\Controllers\Api\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\Auth\LoginRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $loginRequest)
    {
        $credentials = $loginRequest->only(['email', 'password']);

        $customer = Customer::where('email', $credentials['email'])->first();
        if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
            return response()->json(['error' => 'Credentials are incorrect.'], 400);
        }

        $token = $customer->createToken('customer:token')->plainTextToken;

        return response()->json(['token' => $token]);

    }
}
