<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'total_students' => User::where('role', User::ROLE_STUDENT)->count(),
            'total_subjects' => Subject::count(),
            'total_materials' => Material::count(),
        ];

        $recentAdmins = User::where('role', User::ROLE_ADMIN)
            ->latest()
            ->take(5)
            ->get();

        $recentStudents = User::where('role', User::ROLE_STUDENT)
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recentAdmins', 'recentStudents'));
    }
}
