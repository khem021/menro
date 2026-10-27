<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Support\Archivable;
use App\Support\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function download(Report $report, string $format)
    {
        if (!canAccess(...ROLES_STAFF())) {
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

    /**
     * A printable list of the archived records of one type.
     *
     * Renders the same `reports.print` view the report module uses, which takes
     * a title plus headers and rows and calls window.print() itself - so this
     * needs no new PDF code and comes out looking like every other printout.
     */
    public function printDeleted(Request $request)
    {
        if (!canAccess(...ROLES_STAFF())) {
            abort(403, 'Access denied.');
        }

        $type   = (string) $request->query('type', '');
        $config = Archivable::get($type);

        if (!$config) {
            abort(404, 'Unknown record type.');
        }

        $search = trim((string) $request->query('search', ''));
        $from   = $request->query('from') ?: null;
        $to     = $request->query('to') ?: null;

        $rows = $config['model']::onlyTrashed()
            ->when($search && $config['search'], fn($q) =>
                $q->whereRaw('LOWER(' . $config['search'] . ') LIKE ?', [Like::contains($search)]))
            ->when($from, fn($q) => $q->whereDate('deleted_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('deleted_at', '<=', $to))
            ->orderByDesc('deleted_at')
            ->get()
            ->map(function ($r) use ($config) {
                $row = [(string) $r->{$config['title']}];

                foreach (array_keys($config['columns']) as $col) {
                    $row[] = (string) ($r->{$col} ?? '');
                }

                $row[] = $r->deleted_at?->format('M d, Y H:i') ?? '';

                return $row;
            })
            ->all();

        return view('reports.print', [
            'title'       => 'Archived ' . $config['label'],
            'headers'     => array_merge(['Record'], array_values($config['columns']), ['Deleted']),
            'rows'        => $rows,
            'from'        => $from,
            'to'          => $to,
            'colorKeyCol' => null,
        ]);
    }
}
