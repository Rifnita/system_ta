<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Filter Data
        </x-slot>
        
        <x-slot name="description">
            Pilih filter untuk menampilkan data laporan mingguan
        </x-slot>
        
        <form wire:submit="$refresh" class="space-y-4">
            {{ $this->form }}
            
            <div style="padding-top: 2rem;">
                <x-filament::button type="submit">
                    Terapkan Filter
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>


