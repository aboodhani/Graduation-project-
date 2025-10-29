<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorSubmissionController extends Controller
{
    /**
     * عرض قائمة التسليمات + الفلاتر
     */
    public function index(Request $request)
    {
        $doctorId = Auth::id();

        // الأساس: الدكتور يشوف فقط التسليمات التابعة لأقسامه
        $query = Submission::query()
            ->with(['student', 'assignment.section', 'section'])
            ->where(function ($q) use ($doctorId) {
                $q->whereHas('assignment.section', fn($s) => $s->where('created_by', $doctorId))
                  ->orWhereHas('section', fn($s) => $s->where('created_by', $doctorId));
            });

        // ------- فلاتر -------
        if ($student = $request->input('student')) {
            $query->whereHas('student', fn($s) =>
                $s->where('name', 'like', '%' . trim($student) . '%')
            );
        }

        if ($studentNo = $request->input('student_no')) {
            $query->whereHas('student', fn($s) =>
                $s->where('student_number', 'like', '%' . trim($studentNo) . '%')
            );
        }

        if ($section = $request->input('section')) {
            $section = trim($section);
            $query->where(function ($qq) use ($section) {
                $qq->whereHas('section', fn($s) => $s->where('title', 'like', "%{$section}%"))
                   ->orWhereHas('assignment.section', fn($s) => $s->where('title', 'like', "%{$section}%"));
            });
        }

        if ($assignment = $request->input('assignment')) {
            $query->whereHas('assignment', fn($a) =>
                $a->where('title', 'like', '%' . trim($assignment) . '%')
            );
        }

        $result = $request->input('result');
        if ($result !== null && $result !== '') {
            if ($result === 'na') {
                $query->whereNull('code');
            } else {
                $query->where('code', (int) $result);
            }
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('submitted_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('submitted_at', '<=', $to);
        }

        // ------- ترتيب (اختياري عبر باراميترات sort/dir) -------
        $sortable = ['student', 'student_no', 'section', 'assignment', 'result', 'submitted_at'];
        $sort = $request->input('sort', 'submitted_at');
        $dir  = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (in_array($sort, $sortable, true)) {
            switch ($sort) {
                case 'student':
                    $query->join('users as st', 'submissions.student_id', '=', 'st.id')
                          ->orderBy('st.name', $dir)
                          ->select('submissions.*');
                    break;
                case 'student_no':
                    $query->join('users as st2', 'submissions.student_id', '=', 'st2.id')
                          ->orderBy('st2.student_number', $dir)
                          ->select('submissions.*');
                    break;
                case 'section':
                    $query->leftJoin('sections as ss', 'submissions.section_id', '=', 'ss.id')
                          ->leftJoin('assignments as aa', 'submissions.assignment_id', '=', 'aa.id')
                          ->leftJoin('sections as as2', 'aa.section_id', '=', 'as2.id')
                          ->orderByRaw('COALESCE(ss.title, as2.title) ' . $dir)
                          ->select('submissions.*');
                    break;
                case 'assignment':
                    $query->leftJoin('assignments as a3', 'submissions.assignment_id', '=', 'a3.id')
                          ->orderBy('a3.title', $dir)
                          ->select('submissions.*');
                    break;
                case 'result':
                    $query->orderBy('code', $dir);
                    break;
                default:
                    $query->orderBy('submitted_at', $dir);
            }
        } else {
            $query->latest('submitted_at');
        }

        // ------- Pagination -------
        $submissions = $query->paginate(10)->appends($request->query());

        return view('doctor.students-work', compact('submissions'));
    }

    /**
     * حفظ الدرجة والتعليق
     */
    public function grade(Request $request, Submission $submission)
    {
        // 1) تأكيد الدور
        if (! Auth::check() || Auth::user()->role !== 'doctor') {
            abort(403, 'Unauthorized');
        }

        // 2) يملك القسم
        $sectionOwnerId = $submission->assignment?->section?->created_by
            ?? $submission->section?->created_by;

        if ($sectionOwnerId !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // 3) فالديشن
        $data = $request->validate([
            'grade'    => ['nullable','numeric','min:0','max:100'],
            'feedback' => ['nullable','string','max:2000'],
        ]);

        if (array_key_exists('feedback', $data)) {
            $data['feedback'] = trim((string) $data['feedback']);
            if ($data['feedback'] === '') {
                $data['feedback'] = null;
            }
        }

        // 4) حفظ
        DB::transaction(function () use ($submission, $data) {
            $submission->grade      = $data['grade']    ?? null;
            $submission->feedback   = $data['feedback'] ?? null;
            $submission->graded_by  = Auth::id();
            $submission->graded_at  = now();
            $submission->save();
        });

        // 5) JSON لو AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'ok'        => true,
                'message'   => 'Saved successfully',
                'grade'     => $submission->grade,
                'feedback'  => $submission->feedback,
                'graded_at' => $submission->graded_at,
            ]);
        }

        // 6) Back
        return back()->with('success', 'Saved successfully');
    }
}
