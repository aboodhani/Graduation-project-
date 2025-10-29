<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DoctorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $doctor = $request->user();

        // sections من الداتابيس لو عندك موديل، مؤقتًا بنحط Mock
        $sections = [
            ['id' => 1, 'name' => 'Section 1'],
            ['id' => 2, 'name' => 'Section 2'],
            ['id' => 3, 'name' => 'Section 3'],
        ];

        return view('doctor.dashboard', compact('doctor', 'sections'));
    }
}
