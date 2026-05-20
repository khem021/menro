<div style="height:100%;display:flex;flex-direction:column;min-height:0;">

    {{-- View Tabs --}}
    <div style="display:flex;align-items:center;gap:0.375rem;margin-bottom:0.5rem;flex-shrink:0;">
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

    {{-- Cluster Config Card --}}
    <div class="card" style="padding:0.75rem;flex:1;min-height:0;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;flex-shrink:0;">
            <div style="font-size:0.6875rem;font-weight:700;color:var(--text);">Cluster Configuration</div>
            <div style="font-size:0.6rem;color:var(--text-dim);">Group barangays into clusters</div>
        </div>

        @php
            $topColors = [1 => '#34d399', 2 => '#FDB813', 3 => '#60a5fa'];
            $showClusters = $activeCluster === 'all' ? [1, 2, 3] : [(int)$activeCluster];
        @endphp

        <div style="flex:1;min-height:0;overflow-y:auto;scrollbar-width:none;
                    display:grid;gap:0.5rem;
                    grid-template-columns:{{ count($showClusters) === 1 ? '1fr' : 'repeat(3,1fr)' }};">
            @foreach($showClusters as $c)
            <div style="
                border-radius:0.5rem;
                border:1px solid var(--card-border);
                border-top:2px solid {{ $topColors[$c] }};
                background:#0a1628;
                padding:0.625rem;
                display:flex;flex-direction:column;gap:0.375rem;
                overflow:hidden;
            ">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                    <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-muted);">Cluster {{ $c }}</span>
                    <span style="font-size:0.6rem;font-weight:600;padding:0.1rem 0.375rem;border-radius:999px;background:var(--card-border);color:var(--text-muted);">{{ $clusters[$c]->count() }}</span>
                </div>

                {{-- Tags --}}
                <div style="display:flex;flex-wrap:wrap;gap:0.25rem;flex:1;overflow-y:auto;scrollbar-width:none;align-content:flex-start;">
                    @forelse($clusters[$c] as $b)
                    <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.15rem 0.5rem;border-radius:999px;font-size:0.6rem;font-weight:500;background:#1c2d4a;color:var(--text-muted);border:1px solid #253d5e;">
                        {{ $b->barangay_name }}
                        @if(canAccess('System Administrator', 'MENRO Officer'))
                        <button wire:click="removeFromCluster({{ $b->barangay_id }})"
                                style="background:none;border:none;cursor:pointer;color:var(--text-dim);font-size:0.75rem;line-height:1;padding:0;margin-left:1px;transition:color .15s;"
                                onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--text-dim)'"
                                title="Remove">×</button>
                        @endif
                    </span>
                    @empty
                    <span style="font-size:0.6rem;color:var(--text-dim);font-style:italic;">No barangays assigned</span>
                    @endforelse
                </div>

                {{-- Add input --}}
                @if(canAccess('System Administrator', 'MENRO Officer'))
                <div style="display:flex;gap:0.25rem;flex-shrink:0;">
                    <input type="text"
                           wire:model.defer="new{{ $c }}"
                           list="bl-{{ $c }}"
                           style="flex:1;border-radius:0.375rem;background:#071020;border:1px solid var(--card-border);color:var(--text);font-size:0.6rem;padding:0.25rem 0.5rem;outline:none;min-width:0;"
                           placeholder="Add barangay..." />
                    <datalist id="bl-{{ $c }}">
                        @foreach($allBarangays as $ab)
                        <option value="{{ $ab->barangay_name }}">
                        @endforeach
                    </datalist>
                    <button wire:click="addToCluster({{ $c }})"
                            style="padding:0.25rem 0.5rem;border-radius:0.375rem;font-size:0.6rem;font-weight:600;background:linear-gradient(135deg,#b8860b,#FDB813);color:#071020;border:none;cursor:pointer;white-space:nowrap;transition:opacity .15s;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        + Add
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
