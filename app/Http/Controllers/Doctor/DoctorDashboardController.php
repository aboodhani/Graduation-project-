<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section; // تأكدي أن موديل Section موجود

class DoctorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $doctorId = $request->user()->id;

        // استبدلي 'created_by' باسم العمود الصحيح عندك إن اختلف
        $sections = Section::where('created_by', $doctorId)
            ->orderBy('created_at', 'desc')
            ->get();

        // مرّر المتغيّر للـ Blade
        return view('doctor.dashboard', compact('sections'));
    }
}
