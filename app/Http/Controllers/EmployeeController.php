<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    // Display a listing of employees
    public function index(Request $request)
{
    $query = Employee::query();

    if ($request->has('search')) {
        $search = $request->input('search');
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('mobile_number', 'like', "%{$search}%")
              ->orWhere('position', 'like', "%{$search}%");
    }

    $employees = $query->get();

    // Handle AJAX search request
    if ($request->ajax()) {
        return view('employees.partials.employee_table', compact('employees'))->render();
    }

    return view('employees.index', compact('employees'));
}


    // Show the form for creating a new employee
    public function create()
    {
        return view('employees.create');
    }

    // Store a newly created employee in the database
public function store(Request $request)
{
    // Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'sex' => 'required|in:Male,Female,Other', 
        'marital_status' => 'required|in:Single,Married',
        'age' => 'required|integer|min:18|max:100',
        'address' => 'required|string|max:255',
        'email' => 'required|email|unique:employees,email',
        'mobile_number' => [
            'required',
            'regex:/^09\d{9}$/', 
            'unique:employees,mobile_number'
        ],
        'position' => 'required|string|max:255',
        'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ], [
        'email.unique' => 'The email address is already in use.',
        'mobile_number.unique' => 'The mobile number is already in use.',
        'mobile_number.regex' => 'Mobile number must start with "09" and contain exactly 11 digits.',
    ]);

    // Create a new employee
    $employee = new Employee();
    $employee->name = $request->name;
    $employee->sex = $request->sex;
    $employee->marital_status = $request->marital_status; // Fixed typo here
    $employee->age = $request->age;
    $employee->address = $request->address;
    $employee->email = $request->email;
    $employee->mobile_number = $request->mobile_number;
    $employee->position = $request->position;

    // Handle profile image upload
    if ($request->hasFile('profile_image')) {
        $imagePath = $request->file('profile_image')->store('profile_images', 'public');
        $employee->profile_image = $imagePath;
    }

    $employee->save();

    return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
}

    // Display the specified employee
    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    // Show the form for editing the specified employee
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'mobile_number' => 'required|numeric|digits:11|unique:employees,mobile_number,' . $employee->id,
            'position' => 'required|string|max:255',
            'sex' => 'required|string|in:Male,Female,Other',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'age' => 'required|integer|min:18|max:100',
            'address' => 'required|string|max:500',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'email.unique' => 'The email address is already in use.',
            'mobile_number.unique' => 'The mobile number is already in use.',
        ]);

        // Extract relevant inputs
        $input = $request->only([
            'name', 'email', 'mobile_number', 'position', 'sex', 'marital_status', 'age', 'address'
        ]);

        $isImageChanged = $request->hasFile('profile_image');
        $isDataChanged = false;

        // Log comparisons
        foreach ($input as $key => $value) {
            $original = strtolower((string) $employee->getOriginal($key));
            $incoming = strtolower((string) $value);

            \Log::info("Comparing field: {$key}", [
                'original' => $original,
                'incoming' => $incoming,
            ]);

            if ($original !== $incoming) {
                $isDataChanged = true;
                break;
            }
        }

        if (!$isDataChanged && !$isImageChanged) {
            return redirect()->back()->with('info', 'No changes detected.');
        }

        // Update employee data if any field changed
        if ($isDataChanged) {
            $employee->update($input);
        }

        // Handle profile image if changed
        if ($isImageChanged) {
            if ($employee->profile_image) {
                Storage::disk('public')->delete($employee->profile_image);
            }

            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $employee->profile_image = $imagePath;
            $employee->save();
        }

         return redirect()->back()->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        // Delete the profile image if it exists
        if ($employee->profile_image) {
            Storage::disk('public')->delete($employee->profile_image);
        }

        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
    
}