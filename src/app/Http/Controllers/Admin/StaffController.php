<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $staffs = User::where('role', 'staff')->get();

        return view('admin.staff.list', compact('staffs'));
    }
}
