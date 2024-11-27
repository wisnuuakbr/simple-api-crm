<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = User::query()->where('company_id', $user->company_id);

        // Cek role
        if ($user->role === 'employee') {
            $query->where('role', 'employee');
        }

        // Search with name for parameter
        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Default sort by 'name'
        $sortBy = $request->input('sort_by', 'name');
        // Default order 'asc'
        $sortOrder = $request->input('sort_order', 'asc');

        // Validation field
        $sortableColumns = ['name', 'email', 'created_at'];
        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $employee = $query->paginate(10);

        return response()->json(
            [
                'message' => 'success',
                'data' => $employee,
            ],
            200,
        );
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Only manager can access
        if ($user->role !== 'manager') {
            return response()->json(
                [
                    'message' => 'Permission denied',
                ],
                403,
            );
        }

        try {
            $request->validate([
                'name' => 'required|string|unique:users|max:255',
                'phone' => 'nullable|string|max:13',
                'address' => 'required|string',
                'email' => 'nullable|email|unique:users|max:255',
                'password' => 'nullable|string|min:6',
            ]);

            $employee = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'employee',
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => $request->password ? Hash::make($request->password) : null,
                'company_id' => auth()->user()->company_id,
            ]);

            return response()->json(
                [
                    'message' => 'success',
                    'data' => $employee,
                ],
                201,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(
                [
                    'message' => 'Validation failed!',
                    'errors' => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json(
                [
                    'message' => 'Employee creation failed!',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        // Only manager can access
        if ($user->role !== 'manager') {
            return response()->json(
                [
                    'message' => 'Permission denied',
                ],
                403,
            );
        }

        try {
            $employee = User::where('company_id', auth()->user()->company_id)
                ->where('id', $id)
                ->firstOrFail();

            $request->validate([
                'name' => 'required|string|unique:users,name,' . $employee->id . '|max:255',
                'phone' => 'nullable|string|max:13',
                'address' => 'required|string',
                'email' => 'nullable|email|unique:users,email,' . $employee->id . '|max:255',
                'password' => 'nullable|string|min:6',
            ]);

            // Update fields
            $employee->update([
                'name' => $request->name ?? $employee->name,
                'phone' => $request->phone ?? $employee->phone,
                'address' => $request->address ?? $employee->address,
                'email' => $request->email ?? $employee->email,
                'password' => $request->password ? Hash::make($request->password) : $employee->password,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $employee,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'message' => 'Employee not found!',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Employee update failed!',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function show(Request $request, $id)
    {
        try {
            // Get user login
            $user = $request->user();

            $query = User::query()
                ->where('company_id', $user->company_id)
                ->where('id', $id);

            // Cek role
            if ($user->role === 'employee') {
                $query
                    ->where('company_id', $user->company_id)
                    ->where('id', $id)
                    ->where('role', 'employee');
            }

            // Eksekusi query
            $employee = $query->firstOrFail();

            // Kembalikan response JSON
            return response()->json([
                'message' => 'success',
                'data' => $employee,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'message' => 'Employee not found!',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Failed to retrieve employee details!',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();

        // Only manager can access
        if ($user->role !== 'manager') {
            return response()->json(
                [
                    'message' => 'Permission denied',
                ],
                403,
            );
        }

        try {
            $employee = User::where('company_id', auth()->user()->company_id)
                ->where('id', $id)
                ->firstOrFail();

            $employee->delete();

            return response()->json([
                'message' => 'success!',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(
                [
                    'message' => 'Employee not found!',
                ],
                404,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Failed to delete employee data !',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
