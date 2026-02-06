<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Data
            </x-slot>
            <x-slot name="description">
                Pilih filter untuk menampilkan data laporan mingguan
            </x-slot>

            <form wire:submit="$refresh" class="space-y-4">
                {{ $this->form }}
                
                <div>
                    <x-filament::button type="submit" color="primary">
                        Terapkan Filter
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Stats Cards --}}
        @livewire(\App\Filament\Widgets\RekapMingguanStatsWidget::class, [
            'proyekId' => $proyekId,
            'tahun' => $tahun,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
        ])

        {{-- Charts Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @livewire(\App\Filament\Widgets\KualitasLaporanChartWidget::class, [
                'proyekId' => $proyekId,
                'tahun' => $tahun,
                'tanggalMulai' => $tanggalMulai,
                'tanggalAkhir' => $tanggalAkhir,
            ])

            @livewire(\App\Filament\Widgets\ProgressTrendChartWidget::class, [
                'proyekId' => $proyekId,
                'tahun' => $tahun,
                'tanggalMulai' => $tanggalMulai,
                'tanggalAkhir' => $tanggalAkhir,
            ])
        </div>

        {{-- Top Projects Table --}}
        @livewire(\App\Filament\Widgets\TopProyekProgressWidget::class, [
            'proyekId' => $proyekId,
            'tahun' => $tahun,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
        ])
    </div>
</x-filament-panels::page>


