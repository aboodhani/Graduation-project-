<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\PredictController;
use App\Http\Controllers\StudentHistoryController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\UploadGatewayController;

// Doctor controllers
use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorWorkController;
use App\Http\Controllers\Doctor\DoctorSubmissionController;
use App\Http\Controllers\Doctor\SectionController;
use App\Http\Controllers\Doctor\SectionAssignmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Homepage
// Route::get('/', fn () => view('welcome'))->name('home');

// Public AI upload endpoints
Route::get('/upload',  [PredictController::class, 'uploadForm'])->name('upload');
Route::post('/predict', action: [PredictController::class, 'predict'])->name('predict');
Route::get('/result',  [PredictController::class, 'showResult'])->name('result');

// Protected routes (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => view('welcome'))->name('home');
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Student pages
    Route::get('/history', [StudentHistoryController::class, 'index'])->name('student.history');
    Route::get('/assignments', [StudentAssignmentController::class, 'index'])->name('student.assignments');

    // Show single assignment (student view)
    // URL: GET /assignments/{assignment}
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])
        ->name('assignments.show');

    // Student submits assignment (uploads image / file)
    // URL: POST /assignments/{assignment}/submit
    Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])
        ->name('assignments.submit');

    // Text-only flow
    Route::get('/assignments/{assignment}/text-submit', [SubmissionController::class, 'createText'])
        ->name('assignments.text.submit');
    Route::post('/assignments/{assignment}/text-submit', [SubmissionController::class, 'storeText'])
        ->name('assignments.text.store');

    // Grading / View / Download
    Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])
        ->name('submissions.grade');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])
        ->name('submissions.show');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])
        ->name('submissions.download');


    // (Optional) if you kept older name elsewhere, you can add a tiny redirect:
    // Route::post('/submit/{assignment}', [SubmissionController::class, 'store'])->name('submissions.store');
    // But avoid duplicate paths/names — prefer 'assignments.submit' as canonical.
});

// Doctor area (must be authenticated AND role:doctor)
Route::middleware(['auth', 'role:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {

        // Dashboard: /doctor
        Route::get('/', [DoctorDashboardController::class, 'index'])->name('dashboard');

        // Work list (submissions)
        Route::get('work', [DoctorWorkController::class, 'index'])->name('work');
        Route::get('/work/export', [DoctorWorkController::class, 'export'])
            ->name('work.export');

        // Sections: list + create + show
        // GET  /doctor/sections           -> index (list + create UI)
        // POST /doctor/sections           -> store (create)
        // GET  /doctor/sections/{section} -> show detail (students + assignments)
        Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
        Route::post('sections', [SectionController::class, 'store'])->name('sections.store');
        Route::get('sections/{section}', [SectionController::class, 'show'])->name('sections.show');

        // Create assignment for a section (doctor action)
        // POST /doctor/sections/{section}/assignments
        Route::post('sections/{section}/assignments', [SectionAssignmentController::class, 'store'])
            ->name('sections.assignments.store');

        // Doctor grades a submission (doctor action)
        Route::post('submissions/{submission}/grade', [DoctorSubmissionController::class, 'grade'])
            ->name('submissions.grade');

        Route::get('/submissions', [\App\Http\Controllers\Doctor\DoctorSubmissionController::class, 'index'])
            ->name('doctor.submissions.index');
        Route::get('/submissions', [DoctorSubmissionController::class, 'index'])->name('submissions.index');
        Route::post('/submissions/{submission}/grade', [DoctorSubmissionController::class, 'grade'])->name('submissions.grade');
        Route::get('/submissions', [DoctorSubmissionController::class, 'index'])
            ->name('submissions.index');

        Route::post('/submissions/{submission}/grade', [DoctorSubmissionController::class, 'grade'])
            ->name('submissions.grade');


        // Update & Delete assignment
        Route::patch('sections/{section}/assignments/{assignment}', [SectionAssignmentController::class, 'update'])
            ->name('sections.assignments.update');

        Route::delete('sections/{section}/assignments/{assignment}', [SectionAssignmentController::class, 'destroy'])
            ->name('sections.assignments.destroy');
            Route::patch('sections/{section}',  [SectionController::class, 'update'])->name('sections.update');
        Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
    });


// Auth scaffolding (login/register/etc)
require __DIR__ . '/auth.php';
