<?php

namespace App\Filament\Resources\PengajuanCutis\Pages;

use App\Filament\Resources\PengajuanCutis\PengajuanCutiResource;
use App\Models\PengajuanCuti;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EditPengajuanCuti extends EditRecord
{
    protected static string $resource = PengajuanCutiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => $this->canDeleteRecord()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $isAdmin = $this->isAdmin();

        if (! $isAdmin && $record->status_pengajuan !== 'menunggu') {
            Notification::make()
                ->title('Tidak Dapat Diedit')
                ->body('Pengajuan yang sudah diproses tidak bisa diubah.')
                ->danger()
                ->send();
            $this->halt();
        }

        $data['jumlah_hari'] = $this->calculateLeaveDays($data['tanggal_mulai'], $data['tanggal_selesai']);

        $this->ensureNoDateOverlap(
            (int) $record->user_id,
            (string) $data['tanggal_mulai'],
            (string) $data['tanggal_selesai'],
            (int) $record->id,
        );

        if (! $isAdmin) {
            $data['status_pengajuan'] = $record->status_pengajuan;
            $data['catatan_approver'] = $record->catatan_approver;
            $data['disetujui_oleh'] = $record->disetujui_oleh;
            $data['disetujui_pada'] = $record->disetujui_pada;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private function ensureNoDateOverlap(int $userId, string $startDate, string $endDate, int $ignoreId): void
    {
        $overlapExists = PengajuanCuti::query()
            ->where('user_id', $userId)
            ->where('id', '!=', $ignoreId)
            ->whereIn('status_pengajuan', ['menunggu', 'disetujui'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('tanggal_mulai', [$startDate, $endDate])
                    ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                    ->orWhere(function ($inner) use ($startDate, $endDate) {
                        $inner->where('tanggal_mulai', '<=', $startDate)
                            ->where('tanggal_selesai', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlapExists) {
            Notification::make()
                ->title('Tanggal Bertabrakan')
                ->body('Anda sudah memiliki pengajuan cuti pada rentang tanggal tersebut.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    private function calculateLeaveDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return (int) ($start->diffInDays($end) + 1);
    }

    private function canDeleteRecord(): bool
    {
        $record = $this->getRecord();

        return $this->isAdmin() || ((int) $record->user_id === (int) Auth::id() && $record->status_pengajuan === 'menunggu');
    }

    private function isAdmin(): bool
    {
        $user = Auth::user();

        return (bool) ($user && method_exists($user, 'hasRole')
            && ($user->hasRole('super_admin') || $user->hasRole('admin')));
    }
}
