<div>
    @section('title', 'Barangay Clusters — MENRO')
    @section('page-title', 'Barangay Clusters')
    @section('page-subtitle')
        <a href="{{ route('barangays.index') }}">Barangays</a>
        <span class="sep">›</span>
        Clusters
    @endsection

    <div style="display:flex;flex-direction:column;gap:0.75rem;">

        <div style="font-size:0.8125rem;color:var(--text-muted);max-width:60ch;">
            Group barangays into three collection clusters. Clusters drive the
            <a href="{{ route('dashboard') }}" style="color:var(--accent);text-decoration:none;">dashboard</a>
            charts and collection routing. Changes take effect immediately.
        </div>

        {{-- View Tabs --}}
        <div style="display:flex;align-items:center;gap:0.375rem;flex-wrap:wrap;">
            <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim);margin-right:0.25rem;">VIEW:</span>
            @foreach(['all' => 'All Clusters', '1' => 'Cluster 1', '2' => 'Cluster 2', '3' => 'Cluster 3'] as $key => $label)
            <button wire:click="setCluster('{{ $key }}')"
                    style="padding:0.2rem 0.625rem;border-radius:999px;font-size:0.6875rem;font-weight:600;cursor:pointer;transition:all .15s;border:1px solid;
                        {{ $activeCluster === $key
                            ? 'background:var(--accent);color:#071020;border-color:var(--accent);'
                            : 'background:transparent;color:var(--text-muted);border-color:var(--card-border);' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>

        @php
            $topColors = [1 => '#34d399', 2 => '#FDB813', 3 => '#60a5fa'];
            $showClusters = $activeCluster === 'all' ? [1, 2, 3] : [(int)$activeCluster];
            $unassigned = $allBarangays->count() - collect($clusters)->sum(fn($c) => $c->count());
        @endphp

        <div style="display:grid;gap:0.75rem;grid-template-columns:{{ count($showClusters) === 1 ? '1fr' : 'repeat(auto-fit,minmax(240px,1fr))' }};">
            @foreach($showClusters as $c)
            <div class="card" style="
                border-top:2px solid {{ $topColors[$c] }};
                padding:0.875rem;
                display:flex;flex-direction:column;gap:0.5rem;
            ">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:0.6875rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Cluster {{ $c }}</span>
                    <span style="font-size:0.6rem;font-weight:600;padding:0.1rem 0.5rem;border-radius:999px;background:var(--card-border);color:var(--text-muted);">{{ $clusters[$c]->count() }} barangay{{ $clusters[$c]->count() === 1 ? '' : 's' }}</span>
                </div>

                {{-- Tags --}}
                <div style="display:flex;flex-wrap:wrap;gap:0.3rem;align-content:flex-start;min-height:2rem;">
                    @forelse($clusters[$c] as $b)
                    <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.6875rem;font-weight:500;background:#1c2d4a;color:var(--text-muted);border:1px solid #253d5e;">
                        {{ $b->barangay_name }}
                        <button wire:click="removeFromCluster({{ $b->barangay_id }})"
                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);font-size:0.8125rem;line-height:1;padding:0;transition:color .15s;"
                                onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--text-dim)'"
                                title="Remove from cluster">×</button>
                    </span>
                    @empty
                    <span style="font-size:0.6875rem;color:var(--text-dim);font-style:italic;">No barangays assigned</span>
                    @endforelse
                </div>

                {{-- Add input --}}
                <div style="display:flex;gap:0.3rem;">
                    <input type="text"
                           wire:model.defer="new{{ $c }}"
                           wire:keydown.enter="addToCluster({{ $c }})"
                           list="bl-{{ $c }}"
                           style="flex:1;border-radius:0.375rem;background:#071020;border:1px solid var(--card-border);color:var(--text);font-size:0.6875rem;padding:0.35rem 0.6rem;outline:none;min-width:0;"
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

    </div>
</div>
