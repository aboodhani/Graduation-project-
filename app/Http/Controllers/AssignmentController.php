<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Section;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    /**
     * Show assignment page (students view).
     * Route example: GET /assignments/{assignment}
     */
    public function show(Assignment $assignment)
    {
        // يمكنك جلب تقديمات الطالب الحالي هنا إن أردت:
        // $mySubmission = $assignment->submissions()->where('student_id', auth()->id())->latest()->first();

        return view('assignments.show', [
            'assignment'    => $assignment,
            // 'mySubmission'  => $mySubmission,
        ]);
    }

    /**
     * Store assignment inside a section.
     * Route example (doctor): POST /doctor/sections/{section}/assignments
     *
     * يتوقّع أن يستخدم route-model binding للـ Section.
     */
    public function store(Request $request, Section $section)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'deadline'         => 'nullable|date',
            'allow_file_upload'=> 'sometimes|boolean',
            // optional: إذا تريد عمل placeholders لطلاب القسم
            'create_placeholders' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        // Authorization: تأكد أن المستخدم هو صاحب/مدرّس هذا الـ section
        // بعض جداولك قد تحتوي 'teacher_id' أو 'created_by' — نتحقق من الاثنين.
        if (! ( ($section->teacher_id ?? null) === $user->id || ($section->created_by ?? null) === $user->id ) ) {
            abort(403, 'Unauthorized to create assignment in this section.');
        }

        DB::beginTransaction();
        try {
            $assignment = Assignment::create([
                'section_id'       => $section->id,
                'created_by'       => $user->id,
                'title'            => $request->input('title'),
                'description'      => $request->input('description'),
                'deadline'         => $request->input('deadline') ? $request->input('deadline') : null,
                'allow_file_upload'=> (bool) $request->input('allow_file_upload', true),
            ]);

            // إذا طلبت إنشاء placeholders للطلاب المسجلين في هذا القسم:
            if ($request->boolean('create_placeholders')) {
                // نتوقع وجود علاقة students() في Section
                if (method_exists($section, 'students')) {
                    $students = $section->students()->pluck('id')->all();
                    foreach ($students as $studentId) {
                        // تجنّب التكرار إن وُجدت submissions سابقة للطالب لنفس الواجب
                        $exists = Submission::where('assignment_id', $assignment->id)
                                            ->where('student_id', $studentId)
                                            ->exists();
                        if (! $exists) {
                            Submission::create([
                                'assignment_id' => $assignment->id,
                                'student_id'    => $studentId,
                                'content'       => null,
                                'file_path'     => null,
                                'submitted_at'  => null,
                            ]);
                        }
                    }
                } else {
                    // لو العلاقة غير موجودة، سجّل تحذيراً (يمكنك استبدال بسلوك آخر)
                    Log::warning("Section {$section->id} has no students() relation — skipping placeholders.");
                }
            }

            DB::commit();

            // إن كان الطلب AJAX (مثلاً من UI)، أرجع JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'assignment' => $assignment,
                ], 201);
            }

            // وإلاّ أعد التوجيه لصفحة الـ section أو داشبورد الدكتور
            return redirect()
                ->route('doctor.sections.show', $section->id)
                ->with('success', 'Assignment created successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create assignment', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => 'Server error'], 500);
            }

            return back()->withErrors(['error' => 'Failed to create assignment.'])->withInput();
        }
    }

  
}
