<?php

namespace App\Http\Controllers;

use App\Services\FeeIntegrityService;

/**
 * Reports fee data that needs a human decision.
 *
 * Read-only by design. The conditions it lists cannot be fixed by code without
 * inventing an accounting ruling, so the page states the position and leaves the
 * history untouched.
 */
class FeeIntegrityController extends Controller
{
    public function __construct()
    {
        // Financial positions of named students, so the same permission as the
        // registers it summarises.
        $this->middleware('can:fees.view');
    }

    public function index()
    {
        $findings = app(FeeIntegrityService::class)->findings();

        return view('fee_management.integrity.index', compact('findings'));
    }
}
