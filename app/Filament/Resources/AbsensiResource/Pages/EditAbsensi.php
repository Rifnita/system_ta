<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use App\Models\PengaturanAbsensi;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAbsensi extends EditRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $user = Auth::user();
        $isCheckoutFlow = $record->canCheckoutBy($user);

        if ($isCheckoutFlow) {
            $pengaturan = PengaturanAbsensi::getAktif();

            if ($pengaturan?->wajib_foto && empty($data['foto_keluar'])) {
                Notification::make()
                    ->title('Foto Wajib')
                    ->body('Anda harus mengambil foto untuk absen keluar.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            if ($pengaturan?->wajib_lokasi && (empty($data['latitude_keluar']) || empty($data['longitude_keluar']))) {
                Notification::make()
                    ->title('Lokasi Wajib')
                    ->body('Anda harus mengaktifkan GPS untuk absen keluar.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            if ($pengaturan?->wajib_lokasi && !empty($data['latitude_keluar']) && !empty($data['longitude_keluar'])) {
                $validasi = $record->validasiJarakDariKantor(
                    (float) $data['latitude_keluar'],
                    (float) $data['longitude_keluar'],
                );

                if (! $validasi['valid']) {
                    Notification::make()
                        ->title('Lokasi Tidak Valid')
                        ->body($validasi['message'])
                        ->danger()
                        ->send();
                    $this->halt();
                }
            }

            $risk = $record->evaluasiRisikoGps(
                isset($data['akurasi_gps_keluar']) ? (float) $data['akurasi_gps_keluar'] : null,
                (bool) ($data['mock_location_detected_keluar'] ?? false),
                isset($data['latitude_keluar']) ? (float) $data['latitude_keluar'] : null,
                isset($data['longitude_keluar']) ? (float) $data['longitude_keluar'] : null,
            );

            if ($risk['blocked']) {
                Notification::make()
                    ->title('Risiko GPS Terlalu Tinggi')
                    ->body('Absen keluar ditolak. Indikasi spoofing lokasi terdeteksi.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            if ($risk['level'] === 'sedang') {
                Notification::make()
                    ->title('Peringatan GPS')
                    ->body('Sistem mendeteksi anomali GPS pada absen keluar.')
                    ->warning()
                    ->send();
            }

            return [
                'jam_keluar' => now()->format('H:i:s'),
                'foto_keluar' => $data['foto_keluar'] ?? null,
                'latitude_keluar' => $data['latitude_keluar'] ?? null,
                'longitude_keluar' => $data['longitude_keluar'] ?? null,
                'akurasi_gps_keluar' => $data['akurasi_gps_keluar'] ?? null,
                'mock_location_detected_keluar' => (bool) ($data['mock_location_detected_keluar'] ?? false),
                'ip_address_keluar' => request()->ip(),
                'user_agent_keluar' => request()->userAgent(),
                'device_id_keluar' => md5((string) request()->userAgent() . request()->ip()),
                'keterangan' => $data['keterangan'] ?? $record->keterangan,
            ];
        }

        // Mode administrasi: batasi update ke field non-kritikal.
        return [
            'keterangan' => $data['keterangan'] ?? $record->keterangan,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        $record = $this->getRecord();

        if ($record->sudahAbsenKeluar()) {
            return Notification::make()
                ->success()
                ->title('Absen Keluar Berhasil')
                ->body('Absensi keluar Anda berhasil dicatat pada ' . now()->format('H:i') . '.');
        }

        return Notification::make()
            ->success()
            ->title('Data Absensi Diperbarui')
            ->body('Perubahan data absensi berhasil disimpan.');
    }
}
