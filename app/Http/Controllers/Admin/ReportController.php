<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isTeacher = $user && $user->isAdmin() && !$user->isSuperAdmin();

        if ($isTeacher && $user->subject_id) {
            $subjects = Subject::where('id', $user->subject_id)->with('levels')->get();
            $query = User::where('role', User::ROLE_STUDENT)
                ->whereHas('enrollments', fn($q) => $q->where('subject_id', $user->subject_id))
                ->with([
                    'progresses' => fn($q) => $q->where('subject_id', $user->subject_id),
                    'progresses.currentLevel',
                    'progresses.subject',
                    'levelStatuses.level' => fn($q) => $q->where('subject_id', $user->subject_id),
                ]);
        } else {
            $subjects = Subject::with('levels')->get();
            $query = User::where('role', User::ROLE_STUDENT)
                ->with(['progresses.currentLevel', 'progresses.subject', 'levelStatuses.level.subject']);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(15)->withQueryString();

        return view('admin.reports.index', compact('students', 'subjects', 'isTeacher', 'user'));
    }
}
