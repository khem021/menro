<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Barangay;
use App\Models\Cluster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ClusterConfig extends Component
{
    private const PERIODS = ['daily', 'weekly', 'monthly', 'annual'];
    private const PALETTE = ['#3987e5', '#d95926', '#199e70', '#c98500', '#d55181', '#9085e9'];

    public string $activeCluster  = 'all';
    public array  $newBarangayName = [];
    public string $chartPeriod     = 'monthly';
    public ?int   $editingClusterId = null;
    public string $editClusterName  = '';

    public function mount(): void
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }
    }

    public function setCluster(string $c): void
    {
        $this->activeCluster = $c;
    }

    public function setChartPeriod(string $period): void
    {
        if (!in_array($period, self::PERIODS, true)) {
            return;
        }

        $this->chartPeriod = $period;
        $this->emit('cluster-chart-updated', $this->chartPayload());
    }

    public function addCluster(): void
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $cluster = Cluster::create(['name' => null]);
        $cluster->update(['name' => 'Cluster ' . $cluster->id]);

        logAudit('create', 'Cluster', $cluster->id, null, $cluster->toArray());

        $this->activeCluster = (string) $cluster->id;
        session()->flash('success', 'Cluster added.');
    }

    public function addToCluster(int $clusterId): void
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $name = trim($this->newBarangayName[$clusterId] ?? '');
        if (!$name) {
            return;
        }

        $barangay = Barangay::whereRaw('LOWER(barangay_name) = ?', [strtolower($name)])->first();

        if ($barangay) {
            $old = $barangay->cluster;
            $barangay->update(['cluster' => $clusterId]);
            logAudit('update', 'Barangay', $barangay->barangay_id, ['cluster' => $old], ['cluster' => $clusterId]);
        }

        $this->newBarangayName[$clusterId] = '';
    }

    public function startRename(int $clusterId): void
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $cluster = Cluster::find($clusterId);
        if (!$cluster) {
            return;
        }

        $this->editingClusterId = $clusterId;
        $this->editClusterName  = $cluster->name ?? ('Cluster ' . $cluster->id);
    }

    public function cancelRename(): void
    {
        $this->editingClusterId = null;
        $this->editClusterName  = '';
    }

    public function saveRename(int $clusterId): void
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $cluster = Cluster::find($clusterId);
        if (!$cluster) {
            $this->cancelRename();
            return;
        }

        $name = trim($this->editClusterName);
        if ($name === '') {
            $this->cancelRename();
            return;
        }

        $old = $cluster->name;
        if ($old !== $name) {
            $cluster->update(['name' => $name]);
            logAudit('update', 'Cluster', $cluster->id, ['name' => $old], ['name' => $name]);
        }

        $this->cancelRename();
    }

    public function deleteCluster(int $clusterId): void
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $cluster = Cluster::find($clusterId);
        if (!$cluster) {
            return;
        }

        Barangay::where('cluster', $clusterId)->update(['cluster' => null]);

        $old = $cluster->toArray();
        $cluster->delete();

        logAudit('delete', 'Cluster', $clusterId, $old, null);

        if ($this->activeCluster === (string) $clusterId) {
            $this->activeCluster = 'all';
        }

        session()->flash('success', 'Cluster deleted.');
    }

    public function removeFromCluster(int $barangayId): void
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $b = Barangay::find($barangayId);
        if ($b) {
            $old = $b->cluster;
            $b->update(['cluster' => null]);
            logAudit('update', 'Barangay', $barangayId, ['cluster' => $old], ['cluster' => null]);
        }
    }

    public function render()
    {
        $clusterModels = Cluster::orderBy('id')->get();
        $topColors     = $this->paletteFor($clusterModels->pluck('id'));

        $clusters = [];
        foreach ($clusterModels as $cl) {
            $clusters[$cl->id] = Barangay::where('cluster', $cl->id)->orderBy('barangay_name')->get();
        }

        $allBarangays = Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name']);

        return view('livewire.dashboard.cluster-config', compact(
            'clusters', 'clusterModels', 'allBarangays', 'topColors'
        ))
            ->with('chartData', $this->chartPayload($clusterModels, $topColors))
            ->extends('layouts.app');
    }

    private function paletteFor(Collection $clusterIds): array
    {
        $map = [];
        foreach ($clusterIds->values() as $i => $id) {
            $map[$id] = self::PALETTE[$i % count(self::PALETTE)];
        }
        return $map;
    }

    private function periodRange(): array
    {
        return match ($this->chartPeriod) {
            'daily'  => [now()->startOfDay()->toDateString(), now()->endOfDay()->toDateString()],
            'weekly' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'annual' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }

    private function chartPayload(?Collection $clusterModels = null, ?array $topColors = null): array
    {
        $clusterModels ??= Cluster::orderBy('id')->get();
        $topColors     ??= $this->paletteFor($clusterModels->pluck('id'));

        [$start, $end] = $this->periodRange();

        $totals = DB::table('waste_entries as we')
            ->join('waste_generators as wg', 'we.generator_id', '=', 'wg.generator_id')
            ->join('barangays as b', 'wg.barangay_id', '=', 'b.barangay_id')
            ->whereNotNull('b.cluster')
            ->whereBetween('we.entry_date', [$start, $end])
            ->groupBy('b.cluster')
            ->select('b.cluster', DB::raw('SUM(we.quantity) as total'))
            ->pluck('total', 'cluster');

        return [
            'period' => $this->chartPeriod,
            'labels' => $clusterModels->map(fn ($c) => $c->name ?: ('Cluster ' . $c->id))->values()->all(),
            'totals' => $clusterModels->map(fn ($c) => (float) ($totals[$c->id] ?? 0))->values()->all(),
            'colors' => $clusterModels->map(fn ($c) => $topColors[$c->id])->values()->all(),
        ];
    }
}
