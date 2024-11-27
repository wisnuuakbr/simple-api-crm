<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'  => 'required|string|unique:companies|max:255',
                'email' => 'required|email|unique:companies|max:255',
                'phone' => 'nullable|string|max:13',
            ]);

            $company = Company::create($validated);

            // Extract domain from company email
            $emailParts = explode('@', $company->email);
            $emailDomain = $emailParts[1] ?? 'default.com';

            // Create default manager account
            $manager = User::create([
                'name'       => 'Default Manager',
                'email'      => 'manager@' . $emailDomain,
                'password'   => Hash::make('manager123'),
                'role'       => 'manager',
                'company_id' => $company->id,
            ]);

            return response()->json([
                    'message'       => 'success',
                    'data_company'  => $company,
                    'data_manager'  => $manager
            ],201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed!',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'message' => 'Company creation failed!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
