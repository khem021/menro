<div>
    @section('title', 'Barangay Clusters — MENRO')
    @section('page-title', 'Barangay Clusters')
    @section('page-subtitle')
        <a href="{{ route('barangays.index') }}">Barangays</a>
        <span class="sep">›</span>
        Clusters
    @endsection

    @if(session('success'))
    <div class="flash-success" style="flex-shrink:0;margin-bottom:0.75rem;"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>
    @endif

    <div style="display:flex;flex-direction:column;gap:0.75rem;">

        <div style="display:flex;align-items:flex-start;justify-content:flex-end;gap:1rem;">
            <button wire:click="addCluster" class="btn-primary" style="flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Cluster
            </button>
        </div>

        {{-- View Tabs --}}
        <div style="display:flex;align-items:center;gap:0.375rem;flex-wrap:wrap;">
            <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);margin-right:0.25rem;">VIEW:</span>
            <button wire:click="setCluster('all')"
                    style="padding:0.2rem 0.625rem;border-radius:999px;font-size:0.6875rem;font-weight:600;cursor:pointer;transition:all .15s;border:1px solid;
                        {{ $activeCluster === 'all'
                            ? 'background:var(--accent);color:#071020;border-color:var(--accent);'
                            : 'background:transparent;color:var(--text-muted);border-color:var(--card-border);' }}">
                All Clusters
            </button>
            @foreach($clusterModels as $cl)
            <button wire:key="tab-{{ $cl->id }}"
                    wire:click="setCluster('{{ $cl->id }}')"
                    style="padding:0.2rem 0.625rem;border-radius:999px;font-size:0.6875rem;font-weight:600;cursor:pointer;transition:all .15s;border:1px solid;
                        {{ (string)$activeCluster === (string)$cl->id
                            ? 'background:var(--accent);color:#071020;border-color:var(--accent);'
                            : 'background:transparent;color:var(--text-muted);border-color:var(--card-border);' }}">
                {{ $cl->name ?: ('Cluster ' . $cl->id) }}
            </button>
            @endforeach
        </div>

        @php
            $showClusters = $activeCluster === 'all' ? $clusterModels->pluck('id')->all() : [(int)$activeCluster];
            $unassigned   = $allBarangays->count() - collect($clusters)->sum(fn($c) => $c->count());
        @endphp

        @if($clusterModels->isEmpty())
        <div class="card" style="padding:1.5rem;text-align:center;font-size:0.8125rem;color:var(--text-muted);">
            No clusters yet. Click <strong style="color:var(--accent)">Add Cluster</strong> to create the first one.
        </div>
        @else
        <div style="display:grid;gap:0.75rem;grid-template-columns:{{ count($showClusters) === 1 ? '1fr' : 'repeat(auto-fit,minmax(240px,1fr))' }};">
            @foreach($showClusters as $c)
            @php $clModel = $clusterModels->firstWhere('id', $c); @endphp
            <div class="card" wire:key="cluster-card-{{ $c }}" style="
                border-top:2px solid {{ $topColors[$c] }};
                padding:0.875rem;
                display:flex;flex-direction:column;gap:0.5rem;
            ">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                    @if($editingClusterId === $c)
                    <input type="text"
                           wire:model.defer="editClusterName"
                           wire:keydown.enter="saveRename({{ $c }})"
                           wire:keydown.escape="cancelRename"
                           autofocus
                           style="flex:1;min-width:0;border-radius:0.25rem;background:var(--input-bg);border:1px solid var(--accent);color:var(--text);font-size:0.6875rem;font-weight:700;letter-spacing:0.02em;padding:0.15rem 0.4rem;outline:none;" />
                    <div style="display:flex;align-items:center;gap:0.25rem;flex-shrink:0;">
                        <button wire:click="saveRename({{ $c }})" style="background:none;border:none;cursor:pointer;color:var(--success-text);padding:0;line-height:1;" title="Save">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button wire:click="cancelRename" style="background:none;border:none;cursor:pointer;color:var(--text-dim);padding:0;line-height:1;" title="Cancel">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @else
                    <span style="font-size:0.6875rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $clModel->name ?: ('Cluster ' . $c) }}</span>
                    <div style="display:flex;align-items:center;gap:0.375rem;flex-shrink:0;">
                        <span style="font-size:0.6rem;font-weight:600;padding:0.1rem 0.5rem;border-radius:999px;background:var(--card-border);color:var(--text-muted);">{{ $clusters[$c]->count() }} barangay{{ $clusters[$c]->count() === 1 ? '' : 's' }}</span>
                        <button wire:click="startRename({{ $c }})"
                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);padding:0;line-height:1;transition:color .15s;"
                                onmouseover="this.style.color='var(--accent-text)'" onmouseout="this.style.color='var(--text-dim)'"
                                title="Rename cluster">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7m-1.5-9.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button wire:click="deleteCluster({{ $c }})"
                                onclick="confirm('Delete {{ $clModel->name ?: ('Cluster ' . $c) }}? Its barangays will become unassigned.') || event.stopImmediatePropagation()"
                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);padding:0;line-height:1;transition:color .15s;"
                                onmouseover="this.style.color='var(--danger-text)'" onmouseout="this.style.color='var(--text-dim)'"
                                title="Delete cluster">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v13a1 1 0 01-1 1H8a1 1 0 01-1-1V7h10z"/></svg>
                        </button>
                    </div>
                    @endif
                </div>

                {{-- Tags --}}
                <div style="display:flex;flex-wrap:wrap;gap:0.3rem;align-content:flex-start;min-height:2rem;">
                    @forelse($clusters[$c] as $b)
                    <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.6875rem;font-weight:500;background:var(--card-border);color:var(--text-muted);border:1px solid var(--chip-border);">
                        {{ $b->barangay_name }}
                        <button wire:click="removeFromCluster({{ $b->barangay_id }})"
                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);font-size:0.8125rem;line-height:1;padding:0;transition:color .15s;"
                                onmouseover="this.style.color='var(--danger-text)'" onmouseout="this.style.color='var(--text-dim)'"
                                title="Remove from cluster">×</button>
                    </span>
                    @empty
                    <span style="font-size:0.6875rem;color:var(--text-dim);font-style:italic;">No barangays assigned</span>
                    @endforelse
                </div>

                {{-- Add input --}}
                <div style="display:flex;gap:0.3rem;">
                    <input type="text"
                           wire:model.defer="newBarangayName.{{ $c }}"
                           wire:keydown.enter="addToCluster({{ $c }})"
                           list="bl-{{ $c }}"
                           style="flex:1;border-radius:0.375rem;background:var(--input-bg);border:1px solid var(--card-border);color:var(--text);font-size:0.6875rem;padding:0.35rem 0.6rem;outline:none;min-width:0;"
                           placeholder="Add barangay…" />
                    <datalist id="bl-{{ $c }}">
                        @foreach($allBarangays as $ab)
                        <option value="{{ $ab->barangay_name }}">
                        @endforeach
                    </datalist>
                    <button wire:click="addToCluster({{ $c }})"
                            style="padding:0.35rem 0.7rem;border-radius:0.375rem;font-size:0.6875rem;font-weight:600;background:linear-gradient(135deg,#b8860b,#FDB813);color:#071020;border:none;cursor:pointer;white-space:nowrap;transition:opacity .15s;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        + Add
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        @if($unassigned > 0)
        <div style="font-size:0.75rem;color:var(--text-dim);">
            {{ $unassigned }} barangay{{ $unassigned === 1 ? '' : 's' }} not assigned to any cluster.
        </div>
        @endif
        @endif

        {{-- ── Waste collection by cluster ── --}}
        @if($clusterModels->isNotEmpty())
        <div class="card" wire:key="cluster-waste-chart-card" style="padding:0.875rem 1rem;display:flex;flex-direction:column;gap:0.75rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                <span style="font-size:0.6875rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Waste Collection by Cluster</span>
                <div style="display:flex;align-items:center;gap:0.3rem;">
                    @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'annual' => 'Annual'] as $key => $label)
                    <button wire:click="setChartPeriod('{{ $key }}')"
                            style="padding:0.2rem 0.625rem;border-radius:999px;font-size:0.6875rem;font-weight:600;cursor:pointer;transition:all .15s;border:1px solid;
                                {{ $chartPeriod === $key
                                    ? 'background:var(--accent);color:#071020;border-color:var(--accent);'
                                    : 'background:transparent;color:var(--text-muted);border-color:var(--card-border);' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div wire:ignore wire:key="cluster-chart-box" class="cluster-chart-box">
                <canvas id="clusterWasteChart"></canvas>
            </div>
        </div>
        @endif

    </div>

    @push('styles')
    <style>
        /* Chart.js sizes the canvas itself — the box only fixes the area it may use. */
        .cluster-chart-box {
            position: relative;
            width: 100%;
            min-width: 0;
            height: 260px;
            overflow: hidden;
        }
        .cluster-chart-box canvas { display: block; }
    </style>
    @endpush

    @push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        const canvas = document.getElementById('clusterWasteChart');
        if (!canvas) return;

        // A stale instance would fight this one over the canvas size.
        const stale = Chart.getChart(canvas);
        if (stale) stale.destroy();

        function themeColors() {
            const cs = getComputedStyle(document.documentElement);
            return {
                grid:  cs.getPropertyValue('--grid-line').trim(),
                label: cs.getPropertyValue('--text-muted').trim(),
            };
        }
        const gridColor  = themeColors().grid;
        const labelColor = themeColors().label;
        const initial    = @json($chartData);

        const chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: initial.labels.length ? initial.labels : ['—'],
                datasets: [{
                    label: 'kg',
                    data: initial.totals.length ? initial.totals : [0],
                    backgroundColor: initial.colors.length ? initial.colors : ['#3987e5'],
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 96,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' ' + c.parsed.y.toLocaleString() + ' kg' } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: labelColor, font: { size: 9 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: {
                            color: labelColor,
                            font: { size: 9 },
                            callback: v => v.toLocaleString(),
                        },
                    },
                }
            }
        });

        // A Livewire re-render can strip the width/height Chart.js wrote onto the
        // canvas, leaving it at the 300x150 default inside a full-width box. Measure
        // the box and put the canvas back whenever the two drift apart.
        const box = canvas.parentElement;
        function syncSize() {
            if (!box.clientWidth || !box.clientHeight) return;
            if (canvas.clientWidth === box.clientWidth && canvas.clientHeight === box.clientHeight) return;

            canvas.removeAttribute('width');
            canvas.removeAttribute('height');
            canvas.style.width  = '';
            canvas.style.height = '';
            chart.resize(box.clientWidth, box.clientHeight);
        }

        Livewire.on('cluster-chart-updated', payload => {
            chart.data.labels = payload.labels.length ? payload.labels : ['—'];
            chart.data.datasets[0].data = payload.totals.length ? payload.totals : [0];
            chart.data.datasets[0].backgroundColor = payload.colors.length ? payload.colors : ['#3987e5'];
            syncSize();
            chart.update();
        });

        document.addEventListener('menro:theme-changed', () => {
            const c = themeColors();
            chart.options.scales.x.ticks.color = c.label;
            chart.options.scales.y.ticks.color = c.label;
            chart.options.scales.y.grid.color  = c.grid;
            syncSize();
            chart.update();
        });

        if (window.ResizeObserver) {
            new ResizeObserver(syncSize).observe(box);
        }
        window.addEventListener('resize', syncSize);

        // The morph lands after the hook, so re-check on the next frame too.
        Livewire.hook('message.processed', () => {
            syncSize();
            requestAnimationFrame(syncSize);
        });
    });
    </script>
    @endpush
</div>
