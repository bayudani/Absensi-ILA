<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Tabel Users (Untuk Login NIP)
        Schema::table('users', function (Blueprint $table) {
            // Kita pakai 'username' untuk menampung NIP atau Username Admin
            $table->string('username')->unique()->nullable()->after('name'); 
            $table->enum('role', ['admin', 'guru', 'walas', 'kepsek'])->default('guru')->after('password');
            // Email kita bikin nullable karena login pake NIP
            $table->string('email')->nullable()->change();
        });

        // 2. Master: GURU (Data Diri Pengajar)
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Akun Login
            $table->string('nip')->unique();
            $table->string('nama_lengkap');
            $table->string('no_hp')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('alamat')->nullable();
            $table->string('spesialisasi')->nullable(); // Mata Pelajaran yang diajar
            $table->timestamps();
        });
        Schema::create('kepala_sekolah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Akun Login
            $table->string('nip')->unique();
            $table->string('nama_lengkap');
            $table->string('no_hp')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('alamat')->nullable();
            $table->timestamps();
        });

        // 3. Master: KELAS
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas'); // Contoh: X-1
            $table->string('tingkat'); // X, XI, XII
            $table->string('lokal'); // 1, 2
            // Wali Kelas (Relasi ke Guru, nullable jika belum diset)
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->string('tahun_ajaran')->default('2024/2025');
            $table->timestamps();
        });

        // 4. Master: SISWA
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique();
            $table->string('nama_lengkap');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('restrict');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            
            // DATA ORANG TUA (Disatukan atau dipisah, opsi ini dipisah tabel tapi relasi kuat)
            // Tapi agar performa cepat saat kirim WA massal, kita taruh no_hp_ortu di sini juga oke, 
            // tapi best practice tetap normalisasi tabel ortu terpisah.
            $table->timestamps();
        });

        // 5. Master: ORANG TUA (Hanya Data Kontak, Tidak Login)
        Schema::create('ortus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('no_hp_wa'); // Kunci untuk fitur notifikasi WA
            $table->timestamps();
        });

        // 6. Master: MATA PELAJARAN
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel')->unique(); // MTK-W
            $table->string('nama_mapel');
            $table->timestamps();
        });

        // 7. Akademik: JADWAL PELAJARAN
        // Ini inti dari sistem Moving Class (Absen per mapel)
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('mapel_id')->constrained('mapel')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->string('hari'); // Senin, Selasa...
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        // 8. Transaksi: ABSENSI
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwals')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['H', 'S', 'I', 'A']); // Hadir, Sakit, Izin, Alpha
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
        Schema::dropIfExists('jadwals');
        Schema::dropIfExists('mapel');
        Schema::dropIfExists('ortus');
        Schema::dropIfExists('siswas');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('gurus');
        
        // Rollback kolom users manual jika perlu
    }
};