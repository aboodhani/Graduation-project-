<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Assignment;
use App\Models\Submission;

class StudentAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show assignments available to the authenticated student
     * (only assignments belonging to sections the student joined).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // جمع ids الأقسام اللي الطالب عضو فيها
        // يفترض أن عندك علاقة sections() في User (belongsToMany)
        $sectionIds = $user->sections()->pluck('sections.id')->toArray();

        // query أساسي: assignments داخل الأقسام دي
        $query = Assignment::with(['section'])
            ->whereIn('section_id', $sectionIds)
            ->orderBy('deadline');

        // pagination
        $perPage = 12;
        $assignments = $query->paginate($perPage)->withQueryString();

        // نحوّل كل assignment لنموذج بسيط يحتوي الحقول اللي تحتاجها view
        $items = $assignments->getCollection()->transform(function ($a) use ($user) {

            // آخر تسليم (لو موجود) للطالب الحالي
            $latest = Submission::where('assignment_id', $a->id)
                ->where('student_id', $user->id)
                ->orderByDesc('submitted_at')
                ->first();

            // حالة مبسطة:
            // graded  -> لو آخر تسليم موجود و grade ليس NULL
            // submitted -> لو آخر تسليم موجود و لم يتم التقييم بعد
            // overdue -> لو تجاوز الموعد و لم يوجد تسليم أو لم يقم بتسليمه
            // pending -> غير ذلك
            $now = Carbon::now();
            $deadlineObj = $a->deadline ? Carbon::parse($a->deadline) : null;

            if ($latest && !is_null($latest->grade)) {
                $status = 'graded';
            } elseif ($latest) {
                $status = 'submitted';
            } elseif ($deadlineObj && $deadlineObj->lessThan($now)) {
                $status = 'overdue';
            } else {
                $status = 'pending';
            }

            return (object)[
                'id' => $a->id,
                'title' => $a->title,
                'description' => $a->description,
                'deadline' => $a->deadline,
                'deadline_obj' => $deadlineObj,
                'section' => $a->section,
                'latest_submission' => $latest,
                'status_code' => $status,
                'grade' => $latest?->grade,
                'created_at' => $a->created_at,
            ];
        });

        // استبدل الـ collection بالـ paginator مع الـ items المحوّلة
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $assignments->total(),
            $assignments->perPage(),
            $assignments->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // مرّر الـ paginator إلى view (هو يتصرف مثل $assignments)
        return view('assignments', [
            'items' => $paginated,
            'filter' => $request->query('filter'),
        ]);
    }
}
