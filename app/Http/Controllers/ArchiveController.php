<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function download(Report $report, string $format)
    {
        if (!isAdmin()) {
            abort(403, 'Access denied.');
        }

        if (!$report->period) {
            abort(404, 'Not an archived report.');
        }

        $path = $format === 'pdf' ? $report->pdf_path : $report->file_path;

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404, 'Archived file not found.');
        }

        return Storage::disk('local')->download($path, basename($path));
    }
}
