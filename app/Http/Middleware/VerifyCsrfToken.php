<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * Add /predict-test for local testing (remove before production).
     *
     * @var array<int, string>
     */
    protected $except = [
        'predict-test',
    ];
}
