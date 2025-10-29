<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SubmissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Student creates/updates a submission.
     * Accepts text, PDF, or both depending on $assignment->submission_type (text|pdf|both).
     */
    public function store(Request $request, int $assignmentId)
    {
        $assignment = Assignment::with('section.students')->findOrFail($assignmentId);

        // --- Enrollment guard ---
        $student = $request->user();
        if (! $assignment->section || ! $assignment->section->students->contains($student->id)) {
            abort(403, 'You are not enrolled in this section.');
        }

        // --- Optional strict deadline check ---
        // if ($assignment->deadline && now()->greaterThan($assignment->deadline)) {
        //     abort(400, 'Deadline passed.');
        // }

        // --- Base validation ---
        $validated = $request->validate([
            'content' => 'nullable|string',
            'file'    => 'nullable|file|mimes:pdf|max:10240', // 10 MB, PDF only
        ]);

        $type    = $assignment->submission_type ?? 'both'; // 'text' | 'pdf' | 'both'
        $hasText = filled($validated['content'] ?? null);
        $hasFile = $request->hasFile('file');

        // --- Enforce assignment type rules ---
        if ($type === 'text' && ! $hasText) {
            throw ValidationException::withMessages([
                'content' => 'This assignment requires a text submission.',
            ]);
        }
        if ($type === 'pdf' && ! $hasFile) {
            throw ValidationException::withMessages([
                'file' => 'This assignment requires a PDF file.',
            ]);
        }
        if ($type === 'both' && ! ($hasText || $hasFile)) {
            throw ValidationException::withMessages([
                'content' => 'Enter text or upload a PDF.',
                'file'    => 'Enter text or upload a PDF.',
            ]);
        }

        // --- Build payload ---
        $payload = [
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'submitted_at'  => now(),
        ];

        // If re-submitting with a new file, delete the previous stored file.
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($hasFile) {
            $newPath = $request->file('file')->store('submissions', 'public'); // storage/app/public/submissions
            $payload['file_path'] = $newPath;

            if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
        }

        if ($hasText) {
            $payload['content'] = $validated['content'];
        }

        // --- Upsert: one submission per (assignment, student) ---
        $submission = Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            $payload
        );

        if ($request->wantsJson()) {
            return response()->json($submission, 201);
        }

        return back()->with('success', 'Submission saved successfully.');
    }

    /**
     * Teacher grades a submission.
     */
    public function grade(Request $request, int $submissionId)
    {
        $data = $request->validate([
            'grade'    => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission = Submission::with('assignment.section')->findOrFail($submissionId);
        $section    = optional($submission->assignment)->section;

        // Only the section owner (teacher) can grade
        if (! $section || $section->teacher_id !== $request->user()->id) {
            abort(403);
        }

        $submission->update([
            'grade'     => $data['grade'],
            'feedback'  => $data['feedback'] ?? null,
            'graded_by' => $request->user()->id,
            'graded_at' => now(),
        ]);

        return $request->wantsJson()
            ? response()->json($submission)
            : back()->with('success', 'Grade saved.');
    }

    /**
     * Optional: show a single submission (teacher or owner).
     */
    public function show(Request $request, int $submissionId)
    {
        $submission = Submission::with(['assignment.section', 'student'])->findOrFail($submissionId);

        // Access control: owner student OR section teacher
        $user    = $request->user();
        $section = optional($submission->assignment)->section;
        $isOwner = $submission->student_id === $user->id;
        $isTeacher = $section && $section->teacher_id === $user->id;

        if (! $isOwner && ! $isTeacher) {
            abort(403);
        }

        return $request->wantsJson()
            ? response()->json($submission)
            : view('submissions.show', compact('submission'));
    }

    /**
     * Optional: stream/download the PDF (access controlled).
     */
    public function download(Request $request, int $submissionId)
    {
        $submission = Submission::with(['assignment.section'])->findOrFail($submissionId);

        $user    = $request->user();
        $section = optional($submission->assignment)->section;
        $isOwner = $submission->student_id === $user->id;
        $isTeacher = $section && $section->teacher_id === $user->id;

        if (! $isOwner && ! $isTeacher) {
            abort(403);
        }

        if (! $submission->file_path || ! Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($submission->file_path);
    }
    /**
     * ------- New: Pure Text Submission (no AI modal, no files) -------
     * Show form for text-only submission.
     */
    public function createText(Request $request, int $assignmentId)
    {
        $assignment = Assignment::with('section.students')->findOrFail($assignmentId);

        // Only for text-only assignments
        if (($assignment->submission_type ?? 'both') !== 'text') {
            abort(404);
        }

        // Must be enrolled
        $student = $request->user();
        if (! $assignment->section || ! $assignment->section->students->contains($student->id)) {
            abort(403, 'You are not enrolled in this section.');
        }

        // Existing submission (for edit/resubmit view)
        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        return view('submissions.text-submit', compact('assignment', 'submission'));
    }

    /**
     * ------- New: Pure Text Submission (no AI modal, no files) -------
     * Store/update text-only submission.
     */
    public function storeText(Request $request, int $assignmentId)
    {
        $assignment = Assignment::with('section.students')->findOrFail($assignmentId);

        // Only for text-only assignments
        if (($assignment->submission_type ?? 'both') !== 'text') {
            abort(404);
        }

        // Must be enrolled
        $student = $request->user();
        if (! $assignment->section || ! $assignment->section->students->contains($student->id)) {
            abort(403, 'You are not enrolled in this section.');
        }

        // Optional deadline check
        if ($assignment->deadline && now()->greaterThan($assignment->deadline)) {
            abort(400, 'Deadline passed');
        }

        // ✅ التحقق من الحقول
        $data = $request->validate([
            'content' => 'required|string|min:3',
            'score'   => 'required|integer|between:1,30', // ⬅️ جديد
        ]);

        // ✅ البيانات المراد تخزينها
        $payload = [
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'score'         => $data['score'], // ⬅️ جديد
            'content'       => $data['content'],
            'submitted_at'  => now(),
        ];

        // ✅ Upsert (يحدث أو ينشئ)
        $submission = Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            $payload
        );

        return redirect()
            ->route('assignments.text.submit', $assignment->id)
            ->with('success', 'Submission saved successfully.');
    }
}
