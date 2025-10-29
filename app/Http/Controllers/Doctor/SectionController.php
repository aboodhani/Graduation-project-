<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Section;
use App\Models\Assignment;

class SectionController extends Controller
{
    /**
     * Display a listing of the doctor's sections.
     */
    public function index(Request $request)
    {
        $doctorId = $request->user()->id;

        // جلب الأقسام مع عدد الطلاب والواجبات
        $sections = Section::withCount(['users as students_count', 'assignments'])
            ->where('created_by', $doctorId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('doctor.sections.index', compact('sections'));
    }

    /**
     * Store a newly created section.
     * Returns JSON (201) when requested via AJAX, otherwise redirects to section show.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
        ]);

        $section = Section::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        // إذا الطلب من نوع AJAX (fetch/fetch API) أعد JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'section' => $section,
            ], 201);
        }

        return redirect()->route('doctor.sections.show', $section->id)
            ->with('success', 'Section created successfully.');
    }

    /**
     * Show details for a single section (students, assignments).
     */
    public function show(Request $request, Section $section)
    {
        // تأكيد الصلاحية: فقط صاحب الـ section يمكنه رؤيته هنا
        if ($section->created_by !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // جلب الطلاب والواجبات مع عدد التسليمات لكل واجب
        $students = $section->users()->orderBy('name')->get();
        $assignments = $section->assignments()->withCount('submissions')->orderBy('created_at', 'desc')->get();

        return view('doctor.sections.show', compact('section', 'students', 'assignments'));
    }

     public function update(Request $request, Section $section)
    {
        abort_unless($section->created_by === $request->user()->id, 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
        ]);

        $section->update($validated);

        return redirect()
            ->route('doctor.sections.index')
            ->with('success', 'Section updated.');
    }

    public function destroy(Request $request, Section $section)
    {
        abort_unless($section->created_by === $request->user()->id, 403);

        $force = $request->boolean('force');

        $hasAssignments = $section->assignments()->exists();
        if ($hasAssignments && ! $force) {
            return back()->withErrors([
                'error' => 'لا يمكن حذف القسم لوجود مهام داخله. فعّل خيار "Force delete" لحذف القسم وكل ما داخله.'
            ]);
        }

        DB::transaction(function () use ($section, $force) {
            if ($force) {
                foreach ($section->assignments as $as) {
                    $as->submissions()->delete();
                }
                $section->assignments()->delete();
            } else {
                // تأكّد ما فيه مهام (تحوّط)
                if ($section->assignments()->exists()) {
                    abort(400, 'Section still has assignments.');
                }
            }

            $section->delete();
        });

        return redirect()
            ->route('doctor.sections.index')
            ->with('success', $force ? 'تم حذف القسم وكل المهام والتسليمات بداخله.' : 'تم حذف القسم.');
    }
}
