<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;

class UploadGatewayController extends Controller
{
    public function handle(Request $request)
    {
        $id = (int) $request->query('assignment');
        abort_if(!$id, 404);

        $assignment = Assignment::findOrFail($id);

        if (($assignment->submission_type ?? 'both') === 'text') {
            return redirect()->route('assignments.text.submit', $assignment->id);
        }

        // Fall back to your existing upload page (the one PredictController showed)
        return app(\App\Http\Controllers\PredictController::class)->uploadForm($request);
        // or: return view('upload', ['assignment' => $assignment]);
    }
}
