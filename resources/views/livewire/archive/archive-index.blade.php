<div>
    @section('title', 'Archive — MENRO')
    @section('page-title', 'Archive')

    <div class="mob-page" style="height:calc(100vh - 72px - 3.5rem);display:flex;flex-direction:column;overflow:hidden;">

        {{-- Filter bar --}}
        <div class="filter-bar flex-shrink-0">
            <div class="filter-row">
                <div class="filter-group mob-fgroup"><span class="filter-label">Report Type</span>
                    <select wire:model="report_type" class="form-select w-48">
                        <option value="">All Types</option>
                        <option value="monthly_waste">Monthly Waste Report</option>
                        <option value="collection_summary">Collection Summary Report</option>
                    </select>
                </div>
                <div class="filter-group mob-fgroup"><span class="filter-label">Period</span>
                    <select wire:model="period" class="form-select w-36">
                        <option value="">All Periods</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="filter-group mob-fgroup"><span class="filter-label">From</span><input type="date" wire:model="date_from" class="form-input w-36" /></div>
                <div class="filter-group mob-fgroup"><span class="filter-label">To</span><input type="date" wire:model="date_to" class="form-input w-36" /></div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="page-card tbl-card flex-1 min-h-0 flex flex-col overflow-hidden">
            <div class="flex-1 min-h-0 overflow-y-auto mob-tbl-inner" style="scrollbar-width:none;-ms-overflow-style:none;">
                <table class="w-full">
                    <thead class="sticky top-0 z-10" style="background:var(--card-bg)"><tr>
                        <th class="table-header">Report Type</th>
                        <th class="table-header">Period</th>
                        <th class="table-header">Period Covered</th>
                        <th class="table-header">Generated At</th>
                        <th class="table-header">Download</th>
                    </tr></thead>
                    <tbody>
                        @forelse($archives as $archive)
                        <tr class="table-row">
                            <td class="table-cell text-sm font-medium" style="color:var(--text)">
                                {{ $archive->report_type === 'monthly_waste' ? 'Monthly Waste Report' : 'Collection Summary Report' }}
                            </td>
                            <td class="table-cell">
                                @php $pc = match($archive->period) {'daily'=>'badge-blue','weekly'=>'badge-violet','monthly'=>'badge-green','yearly'=>'badge-orange',default=>'badge-gray'}; @endphp
                                <span class="{{ $pc }}">{{ ucfirst($archive->period) }}</span>
                            </td>
                            <td class="table-cell text-sm">
                                {{ $archive->period_start?->format('M d, Y') }} – {{ $archive->period_end?->format('M d, Y') }}
                            </td>
                            <td class="table-cell">
                                <p class="text-xs font-medium" style="color:var(--text)">{{ $archive->generated_at->format('M d, Y') }}</p>
                                <p class="text-xs" style="color:var(--text-dim)">{{ $archive->generated_at->format('H:i:s') }}</p>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    @if($archive->file_path)
                                    <a href="{{ route('archive.download', ['report' => $archive->report_id, 'format' => 'xlsx']) }}" class="btn-sm btn-sm-green">Excel</a>
                                    @endif
                                    @if($archive->pdf_path)
                                    <a href="{{ route('archive.download', ['report' => $archive->report_id, 'format' => 'pdf']) }}" class="btn-sm btn-sm-blue">PDF</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background:var(--card-border)">
                                    <svg class="w-6 h-6" style="color:var(--text-dim)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0l-2 7H6l-2-7m16 0H4"/></svg>
                                </div>
                                <p class="text-sm font-semibold" style="color:var(--text)">No archived reports found</p>
                                <p class="text-xs" style="color:var(--text-dim)">Waste collection reports are archived automatically on their daily/weekly/monthly/yearly schedule.</p>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($archives->hasPages())
            <div class="px-5 py-3 flex-shrink-0 flex items-center justify-between" style="border-top:1px solid var(--card-border)">
                <p class="text-xs" style="color:var(--text-dim)">{{ $archives->firstItem() }}–{{ $archives->lastItem() }} of {{ $archives->total() }} entries</p>
                {{ $archives->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
