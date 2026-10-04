<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            User::ROLE_SUPER_ADMIN => redirect()->route('superadmin.dashboard'),
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
