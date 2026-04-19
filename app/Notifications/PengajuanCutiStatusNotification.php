<?php

namespace App\Notifications;

use App\Models\PengajuanCuti;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanCutiStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public PengajuanCuti $pengajuanCuti)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = match ($this->pengajuanCuti->status_pengajuan) {
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default => 'Menunggu',
        };

        $jenisCuti = match ($this->pengajuanCuti->jenis_cuti) {
            'tahunan' => 'Cuti Tahunan',
            'sakit' => 'Cuti Sakit',
            'melahirkan' => 'Cuti Melahirkan',
            'penting' => 'Cuti Alasan Penting',
            default => 'Cuti Lainnya',
        };

        $periode = optional($this->pengajuanCuti->tanggal_mulai)->format('d/m/Y')
            . ' - ' . optional($this->pengajuanCuti->tanggal_selesai)->format('d/m/Y');

        $mail = (new MailMessage)
            ->subject('Update Pengajuan Cuti - ' . $statusLabel)
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pengguna') . '!')
            ->line('Status pengajuan cuti Anda telah diperbarui.')
            ->line('Status: ' . $statusLabel)
            ->line('Jenis Cuti: ' . $jenisCuti)
            ->line('Periode: ' . $periode)
            ->line('Jumlah Hari: ' . (int) $this->pengajuanCuti->jumlah_hari . ' hari');

        if (filled($this->pengajuanCuti->catatan_approver)) {
            $mail->line('Catatan Approver: ' . $this->pengajuanCuti->catatan_approver);
        }

        return $mail->salutation('Terima kasih, ' . config('app.name'));
    }
}
