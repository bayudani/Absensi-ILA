<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    public static function sendMessage($target, $message)
    {
        $token = env('FONNTE_TOKEN', '');

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Optional jika target sudah format 62
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return ['status' => false, 'reason' => $e->getMessage()];
        }
    }

    public static function createAbsensiMessage($namaSiswa, $mapel, $status, $tanggal, $catatan = null)
    {
        $statusText = [
            'H' => 'HADIR',
            'S' => 'SAKIT',
            'I' => 'IZIN',
            'A' => 'ALPHA (Tanpa Keterangan)'
        ];

        $statusResult = $statusText[$status] ?? 'Tidak Diketahui';

        $msg = "*NOTIFIKASI ABSENSI SMPN 3 SIAK KECIL*\n\n";
        $msg .= "Yth. Orang Tua/Wali,\n";
        $msg .= "Menginfokan kehadiran putra/i Anda pada:\n\n";
        $msg .= "Nama: *" . $namaSiswa . "*\n";
        $msg .= "Mapel: *" . $mapel . "*\n";
        $msg .= "Tanggal: " . $tanggal . "\n";
        $msg .= "Status: *" . $statusResult . "*\n";
        
        // Tambahkan baris catatan jika ada isinya
        if (!empty($catatan)) {
            $msg .= "Catatan: _" . $catatan . "_\n";
        }

        // ---  INFORMASI BOT ---
        // $msg .= "\n--------------------------------\n";
        // $msg .= "💡 *INFO FITUR OTOMATIS*\n";
        // $msg .= "Bapak/Ibu sekarang bisa memantau kehadiran anak secara mandiri dengan membalas pesan ini.\n\n";
        // $msg .= "Ketik perintah berikut:\n";
        // $msg .= "👉 *CEK [NISN]* : Cek absen hari ini\n";
        // $msg .= "👉 *REKAP [NISN]* : Cek rekap bulanan\n";
        // $msg .= "_(Contoh: CEK 1234567890)_\n";
        // $msg .= "--------------------------------\n\n";

        $msg .= "Terima kasih atas perhatiannya.\n";
        $msg .= "-- Sistem Informasi Absensi Terpadu --";

        return $msg;
    }
}