<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\Ortu;
use App\Models\KepalaSekolah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Mulai menanam data SMPN 3 Siak Kecil...');

        // ==========================================================
        // 1. DATA MATA PELAJARAN (MAPEL)
        // ==========================================================
        $mapels = [
            ['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam'],
            ['kode' => 'MTK', 'nama' => 'Matematika'],
            ['kode' => 'IND', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'SBD', 'nama' => 'Seni Budaya'],
            ['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam'],
            ['kode' => 'PJK', 'nama' => 'PJOK'],
            ['kode' => 'IPS', 'nama' => 'Ilmu Pengetahuan Sosial'],
            ['kode' => 'PKN', 'nama' => 'PKN'],
            ['kode' => 'BMR', 'nama' => 'Budaya Melayu Riau'],
            ['kode' => 'ING', 'nama' => 'Bahasa Inggris'],
            ['kode' => 'TIK', 'nama' => 'Informatika / TIK'],
        ];

        foreach ($mapels as $m) {
            Mapel::create([
                'kode_mapel' => $m['kode'],
                'nama_mapel' => $m['nama'],
            ]);
        }
        $this->command->info('✅ Data Mapel Selesai!');

        // ==========================================================
        // 2. ADMIN & KEPALA SEKOLAH
        // ==========================================================

        // Admin TU
        User::create([
            'name' => 'Administrator TU',
            'username' => 'admin',
            'email' => 'admin@smpn3siakkecil.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Kepala Sekolah (Rusiono, S.Pd)
        $userKepsek = User::create([
            'name' => 'Rusiono, S.Pd',
            'username' => '197306041999031010',
            'password' => Hash::make('123456'),
            'role' => 'kepsek',
        ]);

        // Tabel Profil Kepala Sekolah
        KepalaSekolah::create([
            'user_id' => $userKepsek->id,
            'nip' => '197306041999031010',
            'nama_lengkap' => 'Rusiono, S.Pd',
            'no_hp' => '081200000000',
            'jenis_kelamin' => 'L', // Wajib diisi (Enum L/P)
            'alamat' => 'Rumah Dinas',
        ]);

        // ==========================================================
        // 3. DATA GURU
        // ==========================================================
        $daftarGuru = [
            ['nip' => '197201042006042005', 'nama' => 'Nurhayati, S.Ag.', 'mapel' => 'PAI', 'jk' => 'P'],
            ['nip' => '198510192011022002', 'nama' => 'Triyana, S.Pd.I.', 'mapel' => 'Matematika', 'jk' => 'P'],
            ['nip' => '197708092012122002', 'nama' => 'Suharni, S.Ag.', 'mapel' => 'Bahasa Indonesia', 'jk' => 'P'],
            ['nip' => '198304052012122002', 'nama' => 'Islami, S.Pd.I.', 'mapel' => 'Seni Budaya', 'jk' => 'L'],
            ['nip' => '197905272014072004', 'nama' => 'Sri Putri Hirawani Hasyim, S.E.', 'mapel' => 'IPA', 'jk' => 'P'],
            ['nip' => '198912272017082002', 'nama' => 'Natalina Siburian, S.Pd', 'mapel' => 'Bahasa Indonesia', 'jk' => 'P'],
            ['nip' => '196909152021211003', 'nama' => 'Abdul Jalal, S.Ag.', 'mapel' => 'PJOK', 'jk' => 'L'],
            ['nip' => '197908272021212004', 'nama' => 'Zaimar, S.Pd.', 'mapel' => 'IPS', 'jk' => 'P'],
            ['nip' => '199505122025212062', 'nama' => 'Desi Nurdahlia, S.Sos', 'mapel' => 'PKN, BMR', 'jk' => 'P'],
            ['nip' => 'GURU-01',            'nama' => 'Mega Erita Harahap, S.Pd', 'mapel' => 'IPS, BMR', 'jk' => 'P'],
            ['nip' => '199911132024212010', 'nama' => 'Novi Karollina, S.Pd', 'mapel' => 'IPA, TIK', 'jk' => 'P'],
            ['nip' => 'GURU-02',            'nama' => 'Nurrohimah, S. Pd. I', 'mapel' => 'Bahasa Inggris', 'jk' => 'P'],
        ];

        foreach ($daftarGuru as $g) {
            $user = User::create([
                'name' => $g['nama'],
                'username' => $g['nip'],
                'password' => Hash::make('123456'),
                'role' => 'guru',
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $g['nip'],
                'nama_lengkap' => $g['nama'],
                'jenis_kelamin' => $g['jk'],
                'no_hp' => '0812xxxxxxxx',
                'alamat' => 'Siak Kecil',
            ]);
        }
        $this->command->info('✅ Data Guru Selesai!');

        // ==========================================================
        // 4. DATA KELAS
        // ==========================================================

        $guruWalas1 = Guru::where('nama_lengkap', 'like', '%Sri Putri%')->first();
        $guruWalas2 = Guru::where('nama_lengkap', 'like', '%Suharni%')->first();

        // Update Role User Guru jadi 'walas'
        if ($guruWalas1) $guruWalas1->user->update(['role' => 'walas']);
        if ($guruWalas2) $guruWalas2->user->update(['role' => 'walas']);

        $kelas1 = Kelas::create([
            'nama_kelas' => 'IX-1',
            'tingkat' => 'IX',
            'lokal' => '1',
            'wali_kelas_id' => $guruWalas1?->id,
            'tahun_ajaran' => '2025/2026'
        ]);

        $kelas2 = Kelas::create([
            'nama_kelas' => 'IX-2',
            'tingkat' => 'IX',
            'lokal' => '2',
            'wali_kelas_id' => $guruWalas2?->id,
            'tahun_ajaran' => '2025/2026'
        ]);

        $this->command->info('✅ Data Kelas Selesai!');

        // ==========================================================
        // 5. DATA SISWA & ORTU
        // ==========================================================

        $dataSiswa = [
            ['nama' => 'Ahmad Muaraf', 'nisn' => '1403123110090001', 'jk' => 'L'],
            ['nama' => 'Aldi Rian Pratama', 'nisn' => '1304092311100001', 'jk' => 'L'],
            ['nama' => 'Andini Aulia', 'nisn' => '1403125106100001', 'jk' => 'P'],
            ['nama' => 'Fahri Pratama', 'nisn' => '1403121203110001', 'jk' => 'L'],
            ['nama' => 'Ibnu Kurniawan', 'nisn' => '1403120208110001', 'jk' => 'L'],
            ['nama' => 'Lukman Ramadhan', 'nisn' => '1403122208100001', 'jk' => 'L'],
            ['nama' => 'Maharani.P.S', 'nisn' => '3505145503100002', 'jk' => 'P'],
            ['nama' => 'Makholory.Asy.S', 'nisn' => '1471087110100008', 'jk' => 'L'],
            ['nama' => 'Masdiyah', 'nisn' => '1403124105110003', 'jk' => 'P'],
            ['nama' => 'Muhabban Sobirin.S', 'nisn' => '1277043110100001', 'jk' => 'L'],
            ['nama' => 'M. Khoirunnasikhin', 'nisn' => '1403122602110001', 'jk' => 'L'],
            ['nama' => 'Reysa', 'nisn' => '1403125603110001', 'jk' => 'P'],
            ['nama' => 'Riyan Iqbal', 'nisn' => '1403121305110001', 'jk' => 'L'],
            ['nama' => 'Rianti Salsabila.P', 'nisn' => '1403126809110001', 'jk' => 'P'],
            ['nama' => 'Sutriani', 'nisn' => '1403125806110002', 'jk' => 'P'],
            ['nama' => 'Syifa Rama.F', 'nisn' => '1403125002100001', 'jk' => 'P'],
            ['nama' => 'Yati Arani', 'nisn' => '1403124606110001', 'jk' => 'P'],
            ['nama' => 'Zainu Mualif', 'nisn' => '1403122104110001', 'jk' => 'L'],
            ['nama' => 'Zalfa Dea.A', 'nisn' => '1403125501110001', 'jk' => 'P'],
        ];

        foreach ($dataSiswa as $s) {
            $siswaBaru = Siswa::create([
                'kelas_id' => $kelas2->id,
                'nisn' => $s['nisn'],
                'nama_lengkap' => $s['nama'],
                'jenis_kelamin' => $s['jk'],
                'alamat' => 'Siak Kecil',
                'foto' => null,
            ]);

            // FIX: Sesuaikan kolom dengan migration ortus
            Ortu::create([
                'siswa_id' => $siswaBaru->id,
                'nama_ayah' => 'Ayah ' . explode(' ', $s['nama'])[0],
                'nama_ibu' => 'Ibu ' . explode(' ', $s['nama'])[0],
                'no_hp_wa' => '081234567890',
            ]);
        }
        $this->command->info('✅ Data Siswa & Ortu Kelas IX-2 Selesai!');
    }
}
