<?php

namespace App\Filament\Resources\PengajuanCutis\Pages;

use App\Filament\Resources\PengajuanCutis\PengajuanCutiResource;
use App\Models\PengajuanCuti;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CreatePengajuanCuti extends CreateRecord
{
    protected static string $resource = PengajuanCutiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status_pengajuan'] = 'menunggu';
        $data['jumlah_hari'] = $this->calculateLeaveDays($data['tanggal_mulai'], $data['tanggal_selesai']);

        $this->ensureNoDateOverlap(
            (int) $data['user_id'],
            (string) $data['tanggal_mulai'],
            (string) $data['tanggal_selesai'],
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengajuan Cuti Terkirim')
            ->body('Pengajuan cuti Anda berhasil dibuat dan menunggu persetujuan.');
    }

    private function ensureNoDateOverlap(int $userId, string $startDate, string $endDate): void
    {
        $overlapExists = PengajuanCuti::query()
            ->where('user_id', $userId)
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
}
