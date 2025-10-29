<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DoctorWorkController extends Controller
{
    /**
     * Students Work (doctor view) with two tabs:
     *  - AI Submit  : submissions that have a file/image (file_path OR image_path)
     *  - Text Submit: submissions that have text content AND no file/image
     *
     * Filters (via query string):
     *  - student:     student name (LIKE)
     *  - student_no:  student_number (LIKE)
     *  - section:     section title (LIKE) OR numeric id (exact) — both supported
     *  - assignment:  assignment title (LIKE) OR numeric id (exact) — both supported
     *  - result:      0|1|2|na (code or NULL)
     *  - date_from:   YYYY-MM-DD (submitted_at >=)
     *  - date_to:     YYYY-MM-DD (submitted_at <=)
     *  - q:           free text across student name/number and assignment title
     *
     * Pagination:
     *  - AI tab   -> query param "ai_page"
     *  - Text tab -> query param "text_page"
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'ai'); // 'ai' | 'text'
        $perPage = (int) $request->query('per_page', 15);

        // Collect filters in one place for reuse
        $filters = [
            'student'    => $request->query('student'),
            'student_no' => $request->query('student_no'),
            'section'    => $request->query('section'),
            'assignment' => $request->query('assignment'),
            'result'     => $request->query('result'),     // 0|1|2|na|''
            'date_from'  => $request->query('date_from'),  // YYYY-MM-DD
            'date_to'    => $request->query('date_to'),    // YYYY-MM-DD
            'q'          => $request->query('q'),
        ];

        // Base query with eager loads
        $base = Submission::query()
            ->with([
                'student',
                'assignment.section', // assignment -> section
                'section',            // direct section relation (if submissions.section_id is present)
            ])
            ->when($filters['student'], function ($q, $v) {
                $q->whereHas('student', fn($qq) => $qq->where('name', 'like', "%{$v}%"));
            })
            ->when($filters['student_no'], function ($q, $v) {
                $q->whereHas('student', fn($qq) => $qq->where('student_number', 'like', "%{$v}%"));
            })
            // Section filter: accept numeric id or title LIKE
            ->when($filters['section'], function ($q, $v) {
                if (is_numeric($v)) {
                    $q->where(function ($w) use ($v) {
                        $w->where('section_id', (int) $v)
                            ->orWhereHas('assignment', fn($qa) => $qa->where('section_id', (int) $v));
                    });
                } else {
                    $q->whereHas('assignment.section', fn($qq) => $qq->where('title', 'like', "%{$v}%"));
                }
            })
            // Assignment filter: accept numeric id or title LIKE
            ->when($filters['assignment'], function ($q, $v) {
                if (is_numeric($v)) {
                    $q->where('assignment_id', (int) $v);
                } else {
                    $q->whereHas('assignment', fn($qa) => $qa->where('title', 'like', "%{$v}%"));
                }
            })
            // Result filter
            ->when($filters['result'] !== null && $filters['result'] !== '', function ($q) use ($filters) {
                if ($filters['result'] === 'na') {
                    $q->whereNull('code');
                } else {
                    $q->where('code', (int) $filters['result']);
                }
            })
            // Date range
            ->when($filters['date_from'], fn($q, $v) => $q->whereDate('submitted_at', '>=', $v))
            ->when($filters['date_to'],   fn($q, $v) => $q->whereDate('submitted_at', '<=', $v))
            // Free text "q": name/number/assignment title
            ->when($filters['q'], function ($q, $v) {
                $q->where(function ($where) use ($v) {
                    $where->whereHas('student', function ($s) use ($v) {
                        $s->where('name', 'like', "%{$v}%")
                            ->orWhere('student_number', 'like', "%{$v}%");
                    })->orWhereHas('assignment', function ($a) use ($v) {
                        $a->where('title', 'like', "%{$v}%");
                    });
                });
            })
            ->latest('submitted_at');

        // AI submissions: has any file/image
        $aiSubs = (clone $base)
            ->where(function ($q) {
                $q->whereNotNull('file_path')
                    ->orWhereNotNull('image_path');
            })
            ->paginate($perPage, ['*'], 'ai_page')
            ->appends($request->query());

        // Text submissions: has content and no file/image
        $textSubs = (clone $base)
            ->whereNotNull('content')
            ->whereNull('file_path')
            ->whereNull('image_path')
            ->paginate($perPage, ['*'], 'text_page')
            ->appends($request->query());

        // Render your existing doctor.work view (update it to use tabs if you haven't already)
        return view('doctor.work', [
            'tab'       => $tab,
            'aiSubs'    => $aiSubs,
            'textSubs'  => $textSubs,
            'filters'   => $filters,
        ]);
    }

    private function baseQueryWithFilters(array $filters): Builder
    {
        return Submission::query()
            ->with(['student', 'assignment.section', 'section'])
            ->when($filters['student']    ?? null, fn($q, $v) => $q->whereHas('student', fn($qq) => $qq->where('name', 'like', "%{$v}%")))
            ->when($filters['student_no'] ?? null, fn($q, $v) => $q->whereHas('student', fn($qq) => $qq->where('student_number', 'like', "%{$v}%")))
            ->when($filters['section']    ?? null, function ($q, $v) {
                if (is_numeric($v)) {
                    $q->where(function ($w) use ($v) {
                        $w->where('section_id', (int)$v)
                            ->orWhereHas('assignment', fn($qa) => $qa->where('section_id', (int)$v));
                    });
                } else {
                    $q->whereHas('assignment.section', fn($qq) => $qq->where('title', 'like', "%{$v}%"));
                }
            })
            ->when($filters['assignment'] ?? null, function ($q, $v) {
                if (is_numeric($v)) $q->where('assignment_id', (int)$v);
                else $q->whereHas('assignment', fn($qa) => $qa->where('title', 'like', "%{$v}%"));
            })
            ->when(array_key_exists('result', $filters) && $filters['result'] !== null && $filters['result'] !== '', function ($q) use ($filters) {
                $v = $filters['result'];
                if ($v === 'na') $q->whereNull('code');
                else $q->where('code', (int)$v);
            })
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('submitted_at', '>=', $v))
            ->when($filters['date_to']   ?? null, fn($q, $v) => $q->whereDate('submitted_at', '<=', $v))
            ->when($filters['q']         ?? null, function ($q, $v) {
                $q->where(function ($where) use ($v) {
                    $where->whereHas('student', function ($s) use ($v) {
                        $s->where('name', 'like', "%{$v}%")
                            ->orWhere('student_number', 'like', "%{$v}%");
                    })->orWhereHas('assignment', fn($a) => $a->where('title', 'like', "%{$v}%"));
                });
            })
            ->latest('submitted_at');
    }

    private function codeLabel($code): string
    {
        // Adjust these labels to your app’s meaning
        return match (true) {
            $code === null       => 'N/A',
            (int)$code === 0     => 'Fail',
            (int)$code === 1     => 'Pass',
            (int)$code === 2     => 'Review',
            default              => (string)$code,
        };
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // scope: ai | text | all (defaults to current tab or 'ai')
        $scope = $request->query('scope', $request->query('tab', 'ai'));

        $filters = [
            'student'    => $request->query('student'),
            'student_no' => $request->query('student_no'),
            'section'    => $request->query('section'),
            'assignment' => $request->query('assignment'),
            'result'     => $request->query('result'),
            'date_from'  => $request->query('date_from'),
            'date_to'    => $request->query('date_to'),
            'q'          => $request->query('q'),
        ];

        $filename = 'submissions_' . $scope . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Clean any output buffers (prevents ERR_INVALID_RESPONSE)
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($filters, $scope) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel displays Unicode properly
            fwrite($out, "\xEF\xBB\xBF");

            // Shared formatter
            $fmt = function ($s) {
                $studentName = optional($s->student)->name ?? '';
                $studentNo   = optional($s->student)->student_number ?? '';
                $sectionT    = optional(optional($s->assignment)->section)->title
                    ?? optional($s->section)->title
                    ?? '';
                $assignT     = optional($s->assignment)->title ?? '';
                $submitted   = optional($s->submitted_at)?->format('Y-m-d H:i');
                return [$studentName, $studentNo, $sectionT, $assignT, $submitted];
            };

            if ($scope === 'text') {
                // -------- TEXT CSV --------
                fputcsv($out, ['Student', 'Sdt-No.', 'Section', 'Assignment', 'Submitted at', 'Text', 'Score']);

                $q = $this->baseQueryWithFilters($filters)
                    ->whereNotNull('content')
                    ->whereNull('file_path')
                    ->whereNull('image_path')
                    ->latest('submitted_at');

                $q->chunkById(1000, function ($rows) use ($out, $fmt) {
                    foreach ($rows as $s) {
                        [$studentName, $studentNo, $sectionT, $assignT, $submitted] = $fmt($s);
                        fputcsv($out, [
                            $studentName,
                            $studentNo,
                            $sectionT,
                            $assignT,
                            $submitted,
                            (string) $s->content,
                            $s->score ?? '',
                        ]);
                    }
                }, 'id');
            } elseif ($scope === 'ai') {
                // -------- AI CSV --------
                fputcsv($out, ['Student', 'Sdt-No.', 'Section', 'Assignment', 'Result', 'Submitted at', 'Attachment', 'Feedback']);

                $q = $this->baseQueryWithFilters($filters)
                    ->where(fn($qq) => $qq->whereNotNull('file_path')->orWhereNotNull('image_path'))
                    ->latest('submitted_at');

                $q->chunkById(1000, function ($rows) use ($out, $fmt) {
                    foreach ($rows as $s) {
                        [$studentName, $studentNo, $sectionT, $assignT, $submitted] = $fmt($s);

                        $resultLabel = $this->codeLabel($s->code);
                        $attachment  = $s->file_path
                            ? asset('storage/' . $s->file_path)
                            : ($s->image_path ? asset('storage/' . $s->image_path) : '');

                        fputcsv($out, [
                            $studentName,
                            $studentNo,
                            $sectionT,
                            $assignT,
                            $resultLabel,
                            $submitted,
                            $attachment,
                            (string) $s->feedback,
                        ]);
                    }
                }, 'id');
            } else {
                // -------- ALL (unified) --------
                fputcsv($out, ['Type', 'Student', 'Sdt-No.', 'Section', 'Assignment', 'Result', 'Submitted at', 'Attachment', 'Text', 'Feedback']);

                // AI first
                $qAi = $this->baseQueryWithFilters($filters)
                    ->where(fn($qq) => $qq->whereNotNull('file_path')->orWhereNotNull('image_path'))
                    ->latest('submitted_at');

                $qAi->chunkById(1000, function ($rows) use ($out, $fmt) {
                    foreach ($rows as $s) {
                        [$studentName, $studentNo, $sectionT, $assignT, $submitted] = $fmt($s);
                        $resultLabel = $this->codeLabel($s->code);
                        $attachment  = $s->file_path
                            ? asset('storage/' . $s->file_path)
                            : ($s->image_path ? asset('storage/' . $s->image_path) : '');
                        fputcsv($out, [
                            'AI',
                            $studentName,
                            $studentNo,
                            $sectionT,
                            $assignT,
                            $resultLabel,
                            $submitted,
                            $attachment,
                            '',
                            (string) $s->feedback
                        ]);
                    }
                }, 'id');

                // Then TEXT
                $qText = $this->baseQueryWithFilters($filters)
                    ->whereNotNull('content')
                    ->whereNull('file_path')
                    ->whereNull('image_path')
                    ->latest('submitted_at');

                $qText->chunkById(1000, function ($rows) use ($out, $fmt) {
                    foreach ($rows as $s) {
                        [$studentName, $studentNo, $sectionT, $assignT, $submitted] = $fmt($s);
                        fputcsv($out, [
                            'TEXT',
                            $studentName,
                            $studentNo,
                            $sectionT,
                            $assignT,
                            'N/A',
                            $submitted,
                            '',
                            (string) $s->content,
                            (string) $s->feedback
                        ]);
                    }
                }, 'id');
            }

            fclose($out);
        }, 200, $headers);
    }
}
