<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SectionAssignmentController extends Controller
{
   

    public function store(Request $request, Section $section)
    {
        // ---------------------------------------------------------------------
        // Authorization: only section owner/creator can create assignments
        // (Adjust this to your policy if you use teacher_id instead of created_by)
        // ---------------------------------------------------------------------
        if ($section->created_by !== $request->user()->id) {
            abort(403);
        }

        // ---------------------------------------------------------------------
        // Validation
        // If 'submission_type' is not provided, we fallback to legacy 'allow_file_upload'
        //  - allow_file_upload = false  => 'text'
        //  - allow_file_upload = true   => 'both'
        // ---------------------------------------------------------------------
        $validated = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string|max:5000',
            'deadline'          => 'nullable|date',
            'submission_type'   => 'nullable|in:text,pdf,both',
            // Legacy support (optional incoming field)
            'allow_file_upload' => 'sometimes|boolean',
            // Optional: whether to create placeholder submissions for all students
            'create_placeholders' => 'sometimes|boolean',
        ]);

        // Decide submission_type
        $submissionType = $validated['submission_type']
            ?? (array_key_exists('allow_file_upload', $validated)
                    ? ($validated['allow_file_upload'] ? 'both' : 'text')
                    : 'both'); // default

        DB::beginTransaction();

        try {
            // -----------------------------------------------------------------
            // Create assignment
            // Ensure your Assignment model has 'submission_type' in $fillable
            // -----------------------------------------------------------------
            $assignment = Assignment::create([
                'section_id'       => $section->id,
                'created_by'       => $request->user()->id,
                'title'            => $validated['title'],
                'description'      => $validated['description'] ?? null,
                'deadline'         => $validated['deadline'] ?? null,
                'submission_type'  => $submissionType, // <-- NEW
            ]);

            // -----------------------------------------------------------------
            // Fetch students in the section
            // Adjust relationship name if yours is different (students/users)
            // -----------------------------------------------------------------
            $studentIds = $section->users()->pluck('users.id')->all();

            // -----------------------------------------------------------------
            // Prepare placeholder submissions (optional but often useful)
            // - Creates one row per student with assignment_id & section_id.
            // - Uses insertOrIgnore to avoid unique-key conflicts if you add
            //   a unique index on (assignment_id, student_id).
            // -----------------------------------------------------------------
            $placeholdersEnabled = (bool)($validated['create_placeholders'] ?? true);
            if ($placeholdersEnabled && !empty($studentIds)) {
                $now  = now();
                $rows = [];
                foreach ($studentIds as $sid) {
                    $rows[] = [
                        'assignment_id' => $assignment->id,
                        'section_id'    => $section->id,
                        'student_id'    => $sid,
                        'content'       => null,
                        'file_path'     => null,
                        'image_path'    => null,
                        'label'         => null,
                        'code'          => null,
                        'submitted_at'  => null,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }

                // Chunk large inserts to avoid packet size issues
                foreach (array_chunk($rows, 1000) as $chunk) {
                    DB::table('submissions')->insertOrIgnore($chunk);
                }
            }

            DB::commit();

            return redirect()
                ->route('doctor.sections.show', $section->id)
                ->with('success', 'Assignment created (type: ' . strtoupper($submissionType) . ') and assigned to ' . count($studentIds) . ' student(s).');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed creating assignment for section', [
                'section_id' => $section->id,
                'user_id'    => $request->user()->id,
                'request'    => $request->except(['_token']),
                'exception'  => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Failed to create assignment.']);
        }
    }

    /**
     * (Optional) Update an existing assignment (doctor-side).
     * Keeps submission_type consistent.
     */
    public function update(Request $request, Section $section, Assignment $assignment)
    {
        if ($section->created_by !== $request->user()->id || $assignment->section_id !== $section->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'nullable|string|max:5000',
            'deadline'        => 'nullable|date',
            'submission_type' => 'required|in:text,pdf,both',
        ]);

        $assignment->update([
            'title'           => $validated['title'],
            'description'     => $validated['description'] ?? null,
            'deadline'        => $validated['deadline'] ?? null,
            'submission_type' => $validated['submission_type'],
        ]);

        return redirect()
            ->route('doctor.sections.show', $section->id)
            ->with('success', 'Assignment updated.');
    }
    public function destroy(Request $request, Section $section, Assignment $assignment)
{
    // Authorization
    if ($section->created_by !== $request->user()->id || $assignment->section_id !== $section->id) {
        abort(403);
    }

    // Option A (آمن): امنعي الحذف إن كان فيه تسليمات، إلا لو force=1
    $hasSubs = $assignment->submissions()->exists();
    $force   = $request->boolean('force');

    if ($hasSubs && ! $force) {
        return back()->withErrors([
            'error' => 'Cannot delete: assignment already has submissions. Enable force delete to proceed.'
        ]);
    }

    DB::transaction(function () use ($assignment, $force) {
        if ($force) {
            
            $assignment->submissions()->delete();
        }
        $assignment->delete();
    });

    return redirect()
        ->route('doctor.sections.show', $section->id)
        ->with('success', $force ? 'Assignment and its submissions deleted.' : 'Assignment deleted.');
}


}
