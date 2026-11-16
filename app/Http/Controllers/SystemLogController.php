<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class SystemLogController extends AppBaseController
{
    private const TAIL_BYTES = 512 * 1024;

    private const PER_PAGE = 50;

    public function __construct()
    {
        // Application error logs can leak credentials, stack traces and
        // infrastructure details, so they are reserved for the platform
        // Owner by role — never school administrators.
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if ($user && $user->isOwner()) {
                return $next($request);
            }

            abort(403);
        });
    }

    /**
     * Show the latest entries from the application log, newest first.
     */
    public function index(Request $request)
    {
        $level = $request->input('level');
        $search = trim((string) $request->input('q', ''));

        $file = storage_path('logs/laravel.log');
        $entries = is_file($file) && is_readable($file)
            ? $this->parseTail($file)
            : collect();

        // Only offer levels that actually appear in the log.
        $levels = $entries->pluck('level')->unique()->sort()->values();

        if ($level) {
            $entries = $entries->where('level', $level)->values();
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $entries = $entries->filter(
                fn (array $e) => str_contains(mb_strtolower($e['message']), $needle)
                    || str_contains(mb_strtolower($e['env']), $needle)
            )->values();
        }

        $entries = $entries->reverse()->values();

        $page = max(1, (int) $request->input('page', 1));
        $logs = new LengthAwarePaginator(
            $entries->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $entries->count(),
            self::PER_PAGE,
            $page,
            ['path' => url('system-logs'), 'query' => $request->query()]
        );

        return view('system_logs.index', [
            'logs'       => $logs,
            'levels'     => $levels,
            'level'      => $level,
            'search'     => $search,
            'totalShown' => $entries->count(),
            'logExists'  => is_file($file),
        ]);
    }

    /**
     * Parse the last chunk of the log file into structured entries.
     * Laravel writes one entry per header line; stack trace lines follow
     * until the next "[yyyy-mm-dd ..." header.
     */
    private function parseTail(string $path): Collection
    {
        $size = filesize($path);
        $handle = fopen($path, 'rb');

        try {
            fseek($handle, max(0, $size - self::TAIL_BYTES));

            // Discard the first (possibly truncated) line unless we are at BOF.
            fgets($handle);

            $entries = collect();
            $current = null;
            $header = '/^\[(\d{4}-\d{2}-\d{2}[^\]]*)\]\s+([\w\-]+)\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY):\s*(.*)$/';

            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line, "\r\n");

                if (preg_match($header, $line, $m)) {
                    if ($current !== null) {
                        $entries->push($current);
                    }

                    $current = [
                        'datetime' => $m[1],
                        'env'      => $m[2],
                        'level'    => ucfirst(strtolower($m[3])),
                        'message'  => $m[4],
                        'stack'    => '',
                    ];

                    continue;
                }

                if ($current !== null) {
                    $current['stack'] .= ($current['stack'] === '' ? '' : "\n") . $line;
                }
            }

            if ($current !== null) {
                $entries->push($current);
            }

            return $entries;
        } finally {
            fclose($handle);
        }
    }
}
