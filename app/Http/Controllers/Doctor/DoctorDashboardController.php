<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section; // تأكدي أن موديل Section موجود
use App\Models\Submission; // <-- ADD THIS
use App\Models\User;       // <-- ADD THIS


class DoctorDashboardController extends Controller
{
public function index()
    {
        $doctorId = auth()->id();

        // 1. Get stats
        $sectionCount = Section::where('created_by', $doctorId)->count();

        // Count students who are in the doctor's sections
        $studentCount = User::where('role', 'student')
            ->whereHas('sections', function ($query) use ($doctorId) {
                $query->where('created_by', $doctorId);
            })->count();

        // Count submissions from students in the doctor's sections
        $submissionCount = Submission::whereHas('student.sections', function ($query) use ($doctorId) {
            $query->where('created_by', $doctorId);
        })->count();

        // 2. Get 5 recent submissions
        $recentSubmissions = Submission::with(['student', 'assignment'])
            ->whereHas('student.sections', function ($query) use ($doctorId) {
                $query->where('created_by', $doctorId);
            })
            ->latest() // Order by newest
            ->take(5)  // Get the top 5
            ->get();

        // 3. Return the view with all the new data
        return view('doctor.dashboard', compact(
            'sectionCount',
            'studentCount',
            'submissionCount',
            'recentSubmissions'
        ));
    }
}
