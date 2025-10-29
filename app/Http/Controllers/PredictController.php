<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Submission;
use App\Models\Assignment;

class PredictController extends Controller
{
    public function uploadForm(Request $request)
    {
        $assignment = null;

        if ($request->filled('assignment')) {
            $assignmentId = (int) $request->query('assignment');
            $assignment   = \App\Models\Assignment::findOrFail($assignmentId);

            // لو الواجب نص فقط -> وجهي مباشرة لصفحة التسليم النصي
            if (($assignment->submission_type ?? 'both') === 'text') {
                return redirect()->route('assignments.text.submit', $assignment->id);
            }
        }

        // خلاف ذلك اعرض صفحة الرفع
        return view('upload', compact('assignment'));
    }


    public function predict(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'assignment_id' => ['nullable', 'integer', 'exists:assignments,id'],
        ]);

        $assignmentId = $request->input('assignment_id');
        $hasAssignment = !empty($assignmentId);
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        Log::info('PredictController@predict called', [
            'user_id' => $userId,
            'has_assignment' => $hasAssignment,
            'assignment_id' => $assignmentId,
            'remote_addr' => $request->ip(),
        ]);

        $file = $request->file('image');

        try {
            $serviceUrl = env('PYTHON_SERVICE_URL', 'http://127.0.0.1:5000/api/predict');

            $response = Http::timeout(60)
                ->attach('image', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($serviceUrl);

            Log::info('Model service response', ['status' => $response->status(), 'body' => $response->body()]);

            if (! $response->successful()) {
                Log::error('Model service returned non-2xx', ['status' => $response->status()]);
                return back()->withErrors(['error' => 'Prediction service error.'])->withInput();
            }

            $data = $response->json();
            if (! is_array($data) || ! data_get($data, 'ok')) {
                $apiError = data_get($data, 'error', 'Prediction failed.');
                Log::warning('Model returned ok=false', ['response' => $data]);
                return back()->withErrors(['error' => $apiError])->withInput();
            }

            // خزن الملف في storage/public/uploads
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = now()->format('Ymd_His_') . Str::random(8) . '.' . $ext;
            $path = $file->storeAs('uploads', $filename, 'public');

            // خزّن بالـ session عشان صفحة النتائج تعرضها
            $request->session()->put('aiResult', [
                'code' => data_get($data, 'code'),
                'label' => data_get($data, 'label'),
                'feedback' => data_get($data, 'feedback'),
                'raw' => $data,
            ]);
            $request->session()->put('uploadedImage', $path);

            // إذا هنالك assignment & user => احفظ Submission
            if ($hasAssignment && $userId) {
                $assignment = Assignment::find($assignmentId);
                if ($assignment) {
                    $submission = Submission::create([
                        'assignment_id' => $assignment->id,
                        'student_id'    => $userId,
                        'file_path'     => $path,
                        'image_path'    => $path,
                        'submitted_at'  => now(),
                        'label'         => data_get($data, 'label'),
                        'feedback'      => data_get($data, 'feedback'),
                        'code'          => data_get($data, 'code'),
                    ]);
                    Log::info('Submission saved', ['submission_id' => $submission->id]);
                } else {
                    Log::warning('Assignment not found, skipping submission save', ['assignment_id' => $assignmentId]);
                }
            }

            return redirect()->route('result');
        } catch (\Throwable $e) {
            Log::error('Predict exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Server error during prediction.'])->withInput();
        }
    }

    public function showResult(Request $request)
    {
        $aiResult = $request->session()->get('aiResult');
        $uploadedImage = $request->session()->get('uploadedImage')
            ? asset('storage/' . $request->session()->get('uploadedImage'))
            : null;

        return view('result', compact('aiResult', 'uploadedImage'));
    }
}
