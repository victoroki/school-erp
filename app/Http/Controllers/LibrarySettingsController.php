<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Models\Setting;
use App\Services\LibrarySettings;
use Illuminate\Http\Request;
use Flash;

class LibrarySettingsController extends AppBaseController
{
    public function __construct()
    {
        // Reading the settings is part of managing the library, so there is no
        // separate view permission for a page that is two number fields.
        $this->middleware('can:library.manage');
    }

    public function edit()
    {
        return view('library.settings', [
            'settings' => LibrarySettings::all(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // A fine of zero is legitimate (a school that does not charge
            // fines), so this is not `min:1`.
            'fine_per_day' => 'required|numeric|min:0|max:100000',
            'loan_period_days' => 'required|integer|min:1|max:365',
        ], [
            'fine_per_day.min' => 'The fine per day cannot be negative. Enter 0 if no fine is charged.',
            'loan_period_days.min' => 'The loan period must be at least 1 day.',
        ]);

        // The Setting model memoizes reads for the request, and this request
        // only needs to persist — but clear it anyway so a later read in the
        // same request (e.g. an audit log rendering a fine) sees the new value.
        Setting::put(LibrarySettings::FINE_PER_DAY, $validated['fine_per_day'], 'number');
        Setting::put(LibrarySettings::LOAN_PERIOD_DAYS, $validated['loan_period_days'], 'number');
        Setting::forgetResolved();

        Flash::success('Library settings saved. New loans use the updated period, and overdue books are fined at the new rate.');

        return redirect(route('library.settings.edit'));
    }
}
