<?php

namespace App\Filament\Widgets;

use App\Models\LaporanMingguan;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopProyekProgressWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Top 5 Proyek (Progress Tertinggi)';
    }

    public ?int $proyekId = null;
    public ?int $tahun = null;
    public ?string $tanggalMulai = null;
    public ?string $tanggalAkhir = null;

    public function table(Table $table): Table
    {
        // Get top 5 projects with highest progress using subquery to avoid GROUP BY issues
        $subquery = LaporanMingguan::query()
            ->select('proyek_id', DB::raw('MAX(persentase_penyelesaian) as max_progress'))
            ->when($this->proyekId, fn (Builder $q) => $q->where('proyek_id', $this->proyekId))
            ->when($this->tahun, fn (Builder $q) => $q->where('tahun', $this->tahun))
            ->when($this->tanggalMulai, fn (Builder $q) => $q->whereDate('tanggal_mulai', '>=', $this->tanggalMulai))
            ->when($this->tanggalAkhir, fn (Builder $q) => $q->whereDate('tanggal_akhir', '<=', $this->tanggalAkhir))
            ->groupBy('proyek_id')
            ->orderByDesc('max_progress')
            ->limit(5)
            ->get();

        // Get the full laporan records based on the subquery results
        $proyekIds = $subquery->pluck('proyek_id')->toArray();
        
        $query = LaporanMingguan::query()
            ->whereIn('proyek_id', $proyekIds)
            ->with('proyek')
            ->orderByDesc('persentase_penyelesaian')
            ->limit(5);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('proyek.kode_proyek')
                    ->label('Kode')
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('proyek.nama_proyek')
                    ->label('Nama Proyek')
                    ->weight('bold')
                    ->wrap(),
                
                TextColumn::make('proyek.pemilik_proyek')
                    ->label('Pemilik')
                    ->toggleable(),
                
                TextColumn::make('persentase_penyelesaian')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                
                TextColumn::make('proyek.status')
                    ->label('Status Proyek')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'ditunda' => 'warning',
                        'selesai' => 'info',
                        'batal' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->paginated(false);
    }
}
