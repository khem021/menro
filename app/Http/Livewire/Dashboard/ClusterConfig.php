<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Barangay;
use Livewire\Component;

class ClusterConfig extends Component
{
    public string $activeCluster = 'all';
    public string $new1 = '';
    public string $new2 = '';
    public string $new3 = '';

    public function setCluster(string $c): void
    {
        $this->activeCluster = $c;
    }

    public function addToCluster(int $cluster): void
    {
        $prop = 'new' . $cluster;
        $name = trim($this->{$prop});

        if (!$name) return;

        $barangay = Barangay::whereRaw('LOWER(barangay_name) = ?', [strtolower($name)])->first();

        if ($barangay) {
            $old = $barangay->cluster;
            $barangay->update(['cluster' => $cluster]);
            logAudit('update', 'Barangay', $barangay->barangay_id, ['cluster' => $old], ['cluster' => $cluster]);
        }

        $this->{$prop} = '';
    }

    public function removeFromCluster(int $barangayId): void
    {
        $b = Barangay::find($barangayId);
        if ($b) {
            $old = $b->cluster;
            $b->update(['cluster' => null]);
            logAudit('update', 'Barangay', $barangayId, ['cluster' => $old], ['cluster' => null]);
        }
    }

    public function render()
    {
        $clusters = [
            1 => Barangay::where('cluster', 1)->orderBy('barangay_name')->get(),
            2 => Barangay::where('cluster', 2)->orderBy('barangay_name')->get(),
            3 => Barangay::where('cluster', 3)->orderBy('barangay_name')->get(),
        ];
        $allBarangays = Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name']);

        return view('livewire.dashboard.cluster-config', compact('clusters', 'allBarangays'));
    }
}
