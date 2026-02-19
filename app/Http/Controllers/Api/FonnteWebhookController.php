<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Services\FonnteService;
use Carbon\Carbon;

class FonnteWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Tangkap data dari Fonnte
        // Sender biasanya format 628xxx atau 08xxx tergantung setting device
        $sender = $request->input('sender'); 
        $message = trim($request->input('message')); 
        
        // Normalisasi pesan (huruf besar semua)
        $msgUpper = strtoupper($message);

        // 2. Logika Bot
        if ($msgUpper == 'MENU' || $msgUpper == 'HELP' || $msgUpper == 'HALO') {
            $this->sendMenu($sender);
        } 
        elseif (str_starts_with($msgUpper, 'CEK')) {
            // Format: CEK [NISN] (Contoh: CEK 123456)
            $nisn = trim(str_replace('CEK', '', $msgUpper));
            // Validasi input NISN agar tidak kosong
            if(empty($nisn)) {
                FonnteService::sendMessage($sender, "⚠️ Format salah. Ketik: *CEK [NISN]*\nContoh: _CEK 1234567890_");
            } else {
                $this->cekHarian($sender, $nisn);
            }
        } 
        elseif (str_starts_with($msgUpper, 'REKAP')) {
            // Format: REKAP [NISN] (Contoh: REKAP 123456)
            $nisn = trim(str_replace('REKAP', '', $msgUpper));
            if(empty($nisn)) {
                FonnteService::sendMessage($sender, "⚠️ Format salah. Ketik: *REKAP [NISN]*\nContoh: _REKAP 1234567890_");
            } else {
                $this->cekRekap($sender, $nisn);
            }
        } 
        else {
            // Opsional: Balas menu jika perintah tidak dikenali
            // $this->sendMenu($sender);
        }

        // Return json kosong biar Fonnte tau request sukses
        return response()->json(['status' => true]);
    }

    // --- BALASAN MENU ---
    private function sendMenu($target)
    {
        $reply = "*🤖 E-ABSENSI BOT SMPN 3*\n\n";
        $reply .= "Halo! Nomor Anda terdeteksi: $target\n\n";
        $reply .= "Berikut perintah yang bisa digunakan:\n\n";
        $reply .= "1️⃣ Cek Absen Hari Ini:\n";
        $reply .= "Ketik: *CEK [NISN]*\n";
        $reply .= "Contoh: _CEK 1234567890_\n\n";
        $reply .= "2️⃣ Cek Rekap Bulanan:\n";
        $reply .= "Ketik: *REKAP [NISN]*\n";
        $reply .= "Contoh: _REKAP 1234567890_\n\n";
        $reply .= "🔒 _Fitur ini dilindungi. Hanya nomor HP Wali Murid terdaftar yang bisa mengakses data siswa._";

        FonnteService::sendMessage($target, $reply);
    }

    // --- HELPER: NORMALISASI NOMOR HP ---
    // Mengubah 08xx, +628xx menjadi format standar 628xx untuk perbandingan
    private function normalizePhoneNumber($phone) {
        if (!$phone) return '';

        // Hapus karakter selain angka
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ubah awalan 08... jadi 628...
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }
        
        return $phone;
    }

    // --- HELPER: VALIDASI ORTU ---
    private function validateParent($target, $siswa) {
        // 1. Ambil nomor HP ortu dari database
        $parentPhone = $siswa->ortu->no_hp_wa ?? null;

        if (!$parentPhone) {
            return false; // Data nomor HP ortu belum diinput di database
        }

        // 2. Normalisasi kedua nomor (Database & Pengirim WA)
        $normalizedTarget = $this->normalizePhoneNumber($target);
        $normalizedParent = $this->normalizePhoneNumber($parentPhone);

        // 3. Bandingkan
        return $normalizedTarget === $normalizedParent;
    }

    // --- LOGIKA CEK HARIAN ---
    private function cekHarian($target, $nisn)
    {
        // Eager load relasi ortu untuk pengecekan nomor HP
        $siswa = Siswa::with('ortu')->where('nisn', $nisn)->first();

        if (!$siswa) {
            return FonnteService::sendMessage($target, "❌ Maaf, NISN *$nisn* tidak ditemukan dalam database.");
        }

        // --- VALIDASI KEAMANAN (EXTRA SECURITY) ---
        if (!$this->validateParent($target, $siswa)) {
             return FonnteService::sendMessage($target, "⛔ *AKSES DITOLAK*\n\nNomor WhatsApp Anda ($target) tidak terdaftar sebagai Wali Murid dari *$siswa->nama_lengkap*.\n\nDemi keamanan siswa, data tidak dapat ditampilkan. Silakan hubungi Tata Usaha jika Anda baru mengganti nomor HP.");
        }

        $today = Carbon::today()->toDateString();
        $absensi = Absensi::with(['jadwal.mapel'])
                    ->where('siswa_id', $siswa->id)
                    ->whereDate('tanggal', $today)
                    ->get();

        if ($absensi->isEmpty()) {
            return FonnteService::sendMessage($target, "📅 Data absensi hari ini (" . date('d-m-Y') . ") untuk *$siswa->nama_lengkap* BELUM TERSEDIA. Guru mungkin belum melakukan input data.");
        }

        $reply = "*📊 LAPORAN HARIAN*\n";
        $reply .= "Nama: " . $siswa->nama_lengkap . "\n";
        $reply .= "Tanggal: " . Carbon::parse($today)->locale('id')->translatedFormat('l, d F Y') . "\n\n";
        $reply .= "Detail Mapel:\n";

        foreach ($absensi as $ab) {
            $mapel = $ab->jadwal->mapel->nama_mapel ?? '-';
            $jam = substr($ab->jadwal->jam_mulai, 0, 5);
            
            // Konversi Kode Status
            $statusText = match($ab->status) {
                'H' => '✅ Hadir',
                'S' => '💊 Sakit',
                'I' => '✉️ Izin',
                'A' => '❌ Alpha',
                default => '❓ ?'
            };
            
            $catatan = $ab->keterangan ? " _($ab->keterangan)_" : "";
            $reply .= "- $jam ($mapel): *$statusText*$catatan\n";
        }

        FonnteService::sendMessage($target, $reply);
    }

    // --- LOGIKA REKAP BULANAN ---
    private function cekRekap($target, $nisn)
    {
        $siswa = Siswa::with(['kelas', 'ortu'])->where('nisn', $nisn)->first();

        if (!$siswa) {
            return FonnteService::sendMessage($target, "❌ Maaf, NISN *$nisn* tidak ditemukan.");
        }

        // --- VALIDASI KEAMANAN (EXTRA SECURITY) ---
        if (!$this->validateParent($target, $siswa)) {
             return FonnteService::sendMessage($target, "⛔ *AKSES DITOLAK*\n\nNomor WhatsApp Anda ($target) tidak terdaftar sebagai Wali Murid dari *$siswa->nama_lengkap*.");
        }

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $monthName = Carbon::now()->locale('id')->translatedFormat('F Y');

        $rekap = Absensi::where('siswa_id', $siswa->id)
                    ->whereMonth('tanggal', $currentMonth)
                    ->whereYear('tanggal', $currentYear)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

        $h = $rekap['H'] ?? 0;
        $s = $rekap['S'] ?? 0;
        $i = $rekap['I'] ?? 0;
        $a = $rekap['A'] ?? 0;
        $total = $h + $s + $i + $a;
        
        // Hitung Persentase Kehadiran
        $persentase = $total > 0 ? round(($h / $total) * 100) : 0;

        $reply = "*📈 REKAP ABSENSI BULANAN*\n";
        $reply .= "Periode: *$monthName*\n";
        $reply .= "Nama: " . $siswa->nama_lengkap . "\n";
        $reply .= "Kelas: " . ($siswa->kelas->nama_kelas ?? '-') . "\n\n";
        
        $reply .= "✅ Hadir: $h\n";
        $reply .= "💊 Sakit: $s\n";
        $reply .= "✉️ Izin: $i\n";
        $reply .= "❌ Alpha: $a\n";
        $reply .= "------------------\n";
        $reply .= "Persentase Kehadiran: *$persentase%*\n\n";
        
        if ($a >= 3) {
            $reply .= "⚠️ *PERINGATAN:* Jumlah Alpha sudah mencapai $a kali. Mohon pantauan orang tua.";
        } else {
            $reply .= "Terima kasih telah memantau kehadiran putra/i Anda.";
        }

        FonnteService::sendMessage($target, $reply);
    }
}