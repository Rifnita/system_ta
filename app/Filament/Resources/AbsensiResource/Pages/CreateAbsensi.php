<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use App\Models\Absensi;
use App\Models\PengaturanAbsensi;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreateAbsensi extends CreateRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validasi: Cek apakah sudah absen hari ini
        if (Absensi::sudahAbsenHariIni(Auth::id())) {
            Notification::make()
                ->title('Gagal Absen')
                ->body('Anda sudah melakukan absensi hari ini.')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // Cek pengaturan absensi
        $pengaturan = PengaturanAbsensi::first();
        
        if (!$pengaturan) {
            // Jika belum ada pengaturan, set default tanpa validasi ketat
            $data['keterlambatan_menit'] = 0;
            
            // Set tracking data
            $data['ip_address_masuk'] = request()->ip();
            $data['user_agent_masuk'] = request()->userAgent();
            $data['device_id_masuk'] = md5(request()->userAgent() . request()->ip());
            
            return $data;
        }
        
        // Validasi foto (hanya jika wajib)
        if ($pengaturan->wajib_foto && empty($data['foto_masuk'])) {
            Notification::make()
                ->title('Foto Wajib')
                ->body('Anda harus mengambil foto untuk absen masuk.')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // Validasi GPS (hanya jika wajib)
        if ($pengaturan->wajib_lokasi && (empty($data['latitude_masuk']) || empty($data['longitude_masuk']))) {
            Notification::make()
                ->title('Lokasi Wajib')
                ->body('Anda harus mengaktifkan GPS untuk absen.')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // Deteksi mock location (warning saja, tidak block)
        if (!empty($data['mock_location_detected_masuk']) && $data['mock_location_detected_masuk']) {
            Notification::make()
                ->title('Peringatan')
                ->body('Sistem mendeteksi kemungkinan penggunaan fake GPS.')
                ->warning()
                ->send();
        }

        // Validasi jarak dari kantor (hanya jika wajib lokasi)
        if ($pengaturan->wajib_lokasi && !empty($data['latitude_masuk']) && !empty($data['longitude_masuk'])) {
            $absensi = new Absensi();
            $validasi = $absensi->validasiJarakDariKantor(
                $data['latitude_masuk'],
                $data['longitude_masuk']
            );

            if (!$validasi['valid']) {
                Notification::make()
                    ->title('Lokasi Tidak Valid')
                    ->body($validasi['message'])
                    ->danger()
                    ->send();
                
                $this->halt();
            }
        }

        // Hitung keterlambatan - simple, hanya bandingkan jam
        try {
            // Parse jam standar (misalnya 08:00:00)
            list($jamStd, $menitStd) = explode(':', $pengaturan->jam_masuk_standar);
            
            // Parse jam masuk aktual (dari form, misalnya 08:15:00) 
            list($jamAktual, $menitAktual) = explode(':', $data['jam_masuk']);
            
            // Hitung selisih dalam menit
            $selisih = (($jamAktual * 60) + $menitAktual) - (($jamStd * 60) + $menitStd);
            
            $data['keterlambatan_menit'] = $selisih > 0 ? $selisih : 0;
        } catch (\Exception $e) {
            // Jika gagal, set 0
            $data['keterlambatan_menit'] = 0;
        }

        // Set tracking data
        $data['ip_address_masuk'] = request()->ip();
        $data['user_agent_masuk'] = request()->userAgent();
        $data['device_id_masuk'] = md5(request()->userAgent() . request()->ip());

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Absen Masuk Berhasil')
            ->body('Anda telah berhasil melakukan absensi masuk.')
            ->success()
            ->send();
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->getRecord();
        
        $message = 'Absen masuk berhasil pada ' . now()->format('H:i');
        
        if ($record->keterlambatan_menit > 0) {
            $message .= '. Anda terlambat ' . $record->keterlambatan_menit . ' menit.';
        } else {
            $message .= '. Tepat waktu!';
        }

        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body($message);
    }
}
