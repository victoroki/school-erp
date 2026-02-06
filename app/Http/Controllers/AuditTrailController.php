<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AuditTrailController extends AppBaseController
{
    public function index(Request $request)
    {
        $logs = AuditTrail::with('user')->latest()->paginate(50);
        return view('audit_trail.index', compact('logs'));
    }
}
