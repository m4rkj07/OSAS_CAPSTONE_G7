<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index()
    {
        $query = User::query();
        $userRole = auth()->user()->role;

        switch ($userRole) {
            case 'super_admin':
                // Super admin can see all except students
                $query->where('role', '!=', 'student');
                break;

            case 'admin':
                // Admin can see: officer, staff, prefect, teacher
                $query->whereIn('role', ['officer', 'staff', 'prefect', 'teacher']);
                break;

            case 'officer':
                // Officer can see: staff, teacher
                $query->whereIn('role', ['staff', 'teacher']);
                break;

            case 'prefect':
                // Prefect can see: none (if you want to block student too)
                $query->whereIn('role', []); 
                break;

            default:
                // Everyone else only sees themselves
                $query->where('id', auth()->id());
                break;
        }

        $users = $query->latest()->get();

        return view('list-of-user.user', compact('users'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'employee_id' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:50',
        ], [
            'username.required' => 'The username is required.',
            'username.unique' => 'This username is already taken. Please choose a different one.',
            'name.required' => 'The first name is required.',
            'last_name.required' => 'The last name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please use a different one.',
            'password.required' => 'A password is required.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
            'role.required' => 'The user role is required.',
        ]);

        // Hash the password
        $validated['password'] = Hash::make($validated['password']);
        
        // Create the user
        $user = User::create($validated);

        return redirect()->back()->with('success', 'User created successfully!');
    }

    /**
     * Update the specified user in the database.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['nullable', 'string', Rule::in(['super_admin', 'admin', 'teacher', 'prefect', 'student', 'staff', 'officer'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        $validatedData = $request->validate($rules);

        // A better way to handle updates: get all validated data
        $userData = $request->except(['password', 'password_confirmation']);

        // Check if any data has actually been changed
        $hasChanges = false;
        foreach ($userData as $key => $value) {
            if ($user->$key != $value) {
                $hasChanges = true;
                break;
            }
        }

        // Conditionally add the password to the update array
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->input('password'));
            $hasChanges = true;
        }

        if (!$hasChanges) {
            return redirect()->route('users.index')->with('warning', 'No changes were made.');
        }

        $user->update($userData);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}