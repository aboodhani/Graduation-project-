<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class StudentHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show authenticated student's submissions (history).
     * Accepts optional query param ?filter=over|under|optimal
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // القيم المقبولة للفلتر من الـ URL
        $filter = $request->query('filter'); // over | under | optimal

        // بنبني الـ query الأساسي
        $query = Submission::with(['assignment', 'section'])
            ->where('student_id', $user->id)
            ->orderByDesc('submitted_at');

        // لو فيه فلتر، نطبقه مع دعم للاحتمالات المختلفة في حقل label
        if ($filter) {
            $filter = strtolower($filter);

            // نتحوّل لقيم قد تظهر في العمود label
            $labelCandidates = match($filter) {
                'over' => ['over', 'over_extended', 'over-extended', 'over extended', 'over-extended'],
                'under' => ['under', 'under_extended', 'under-extended', 'under extended'],
                'optimal' => ['optimal', 'optimal', 'ok', 'good'],
                default => []
            };

            if (!empty($labelCandidates)) {
                $query->where(function($q) use ($labelCandidates) {
                    foreach ($labelCandidates as $candidate) {
                        // نستخدم LIKE ليشمل الصيغ المختلفة (Case-insensitive on MySQL by default)
                        $q->orWhere('label', 'like', "%{$candidate}%");
                    }
                });
            }
        }

        // Pagination — عدل الرقم حسب حاجتك
        $perPage = 12;
        $subs = $query->paginate($perPage)->withQueryString();

        // مرّر المتغيرات إلى ال view
        return view('history', [
            'subs' => $subs,
            'filter' => $filter,
        ]);
    }
}
