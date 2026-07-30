<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\Ortu;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Ahmad Muaraf',            'ayah' => 'Marlan'],
            ['nama' => 'Aldi Rian Pratama',       'ayah' => 'DONI TATA'],
            ['nama' => 'Andini Aulia',            'ayah' => 'HARYONO'],
            ['nama' => 'Fahri Pratama',           'ayah' => 'Soiren'],
            ['nama' => 'Ibnu Kurniawan',          'ayah' => 'MISYUDI'],
            ['nama' => 'Lukman Ramadhan',         'ayah' => 'Tukimon'],
            ['nama' => 'Maharani.P.S',            'ayah' => 'BENI SUNARNO'],
            ['nama' => 'Makholory.Asy.S',         'ayah' => 'KHAIRUL MUNTAHA, SE'],
            ['nama' => 'Masdiyah',                'ayah' => 'Sumardi'],
            ['nama' => 'Muhabban Sobirin.S',      'ayah' => 'ADUON SIREGAR'],
            ['nama' => 'M. Khoirunnasikhin',      'ayah' => 'Muhammad Sidik'],
            ['nama' => 'Reysa',                   'ayah' => 'JUNI EFENDI'],
            ['nama' => 'Riyan Iqbal',             'ayah' => 'RUSLAN'],
            ['nama' => 'Rianti Salsabila.P',      'ayah' => 'JON HENDRI'],
            ['nama' => 'Sutriani',                'ayah' => 'Sukani'],
            ['nama' => 'Syifa Rama.F',            'ayah' => 'UCOK IKHSAN'],
            ['nama' => 'Yati Arani',              'ayah' => 'RAMZI'],
            ['nama' => 'Zainu Mualif',            'ayah' => 'Boiman'],
            ['nama' => 'Zalfa Dea.A',             'ayah' => 'IRHAMNI'],
        ];

        $updated = 0;
        foreach ($data as $item) {
            $siswa = Siswa::where('nama_lengkap', $item['nama'])->first();
            if (!$siswa) {
                $this->command->warn("Siswa '{$item['nama']}' tidak ditemukan, dilewati.");
                continue;
            }

            Ortu::updateOrCreate(
                ['siswa_id' => $siswa->id],
                ['nama_ayah' => $item['ayah']]
            );

            $updated++;
        }

        $this->command->info("{$updated} data orang tua berhasil diperbarui.");
    }
}
