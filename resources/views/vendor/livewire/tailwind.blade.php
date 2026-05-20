<div>
    @if ($paginator->hasPages())
        @php(isset($this->numberOfPaginatorsRendered[$paginator->getPageName()]) ? $this->numberOfPaginatorsRendered[$paginator->getPageName()]++ : $this->numberOfPaginatorsRendered[$paginator->getPageName()] = 1)
        <nav role="navigation" aria-label="Pagination Navigation" style="display:flex;align-items:center;gap:0.25rem;">

            {{-- Prev --}}
            @if ($paginator->onFirstPage())
            <span style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:0.375rem;border:1px solid var(--card-border);color:var(--text-dim);cursor:not-allowed;opacity:0.4;">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </span>
            @else
            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:0.375rem;border:1px solid var(--card-border);background:transparent;color:var(--text-muted);cursor:pointer;transition:all .15s;"
                    onmouseover="this.style.background='#1c2d4a';this.style.color='var(--text)';"
                    onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)';">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </button>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;font-size:0.75rem;color:var(--text-dim);">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                    <span wire:key="paginator-{{ $paginator->getPageName() }}-{{ $this->numberOfPaginatorsRendered[$paginator->getPageName()] }}-page{{ $page }}">
                        @if ($page == $paginator->currentPage())
                        <span aria-current="page" style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:0.375rem;border:1px solid #FDB813;background:#FDB813;color:#071020;font-size:0.75rem;font-weight:700;">{{ $page }}</span>
                        @else
                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:0.375rem;border:1px solid var(--card-border);background:transparent;color:var(--text-muted);font-size:0.75rem;font-weight:500;cursor:pointer;transition:all .15s;"
                                onmouseover="this.style.background='#1c2d4a';this.style.color='var(--text)';"
                                onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)';">{{ $page }}</button>
                        @endif
                    </span>
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:0.375rem;border:1px solid var(--card-border);background:transparent;color:var(--text-muted);cursor:pointer;transition:all .15s;"
                    onmouseover="this.style.background='#1c2d4a';this.style.color='var(--text)';"
                    onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)';">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            </button>
            @else
            <span style="display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:0.375rem;border:1px solid var(--card-border);color:var(--text-dim);cursor:not-allowed;opacity:0.4;">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            </span>
            @endif

        </nav>
    @endif
</div>
