<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin / Dosen
        User::updateOrCreate(
            ['username' => 'sokidbae'],
            [
                'name' => 'Sokid, M.Kom',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'kelas_id' => null,
            ]
        );

        // Alias akun cadangan
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Sokid, M.Kom',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'kelas_id' => null,
            ]
        );

        // 2. Kelas
        $kelas2A = Kelas::firstOrCreate(['nama' => '2A']);
        $kelas2B = Kelas::firstOrCreate(['nama' => '2B']);
        $kelas3ABC = Kelas::firstOrCreate(['nama' => 'Semester PBA-3ABC']);

        // 3. Mata Kuliah
        $mkCyber = MataKuliah::firstOrCreate(
            ['kode' => 'SNJ625006'],
            ['nama' => 'Cyber Culture ( simulasi )', 'sks' => 2, 'semester' => '3']
        );
        $mkWeb = MataKuliah::firstOrCreate(
            ['kode' => 'MK-PW02'],
            ['nama' => 'Pemrograman Web', 'sks' => 3, 'semester' => '3']
        );
        $mkSD = MataKuliah::firstOrCreate(
            ['kode' => 'SD001'],
            ['nama' => 'Struktur Data', 'sks' => 3, 'semester' => '2']
        );

        // 4. Mahasiswa Contoh (sesuai contoh screenshot)
        $mhsData = [
            ['nim' => '2530801036', 'nama' => 'Ananda Rizqi Kurniawan', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801032', 'nama' => 'Ahmad Syarifudin', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801047', 'nama' => 'Taohid', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801042', 'nama' => 'Kaysan Nawfal Salif Athaullah', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801045', 'nama' => 'Abdi Malika Putra', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801050', 'nama' => 'Muhammad David Rizal Fahrezi', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801029', 'nama' => 'Nadya Athalia Diva', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801048', 'nama' => 'MUHAMAD YUDA FIRDAUS', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801026', 'nama' => 'Fadrial Junyana Putra', 'kelas_id' => $kelas2B->id],
            ['nim' => '2530801035', 'nama' => 'Much. Mentari Irwansyah', 'kelas_id' => $kelas2B->id],
            // Akun test simpel
            ['nim' => 'mhs01', 'nama' => 'Mahasiswa Contoh 1', 'kelas_id' => $kelas2B->id],
        ];

        foreach ($mhsData as $m) {
            User::updateOrCreate(
                ['username' => $m['nim']],
                [
                    'name' => $m['nama'],
                    'password' => Hash::make('password'),
                    'role' => 'mahasiswa',
                    'kelas_id' => $m['kelas_id'],
                ]
            );
        }

        // 5. Soal Pilihan Ganda & Essay untuk Bank Soal
        $soalList = [
            [
                'mata_kuliah_id' => $mkCyber->id,
                'tipe' => 'pg',
                'pertanyaan' => 'Apa yang dimaksud dengan konsep Digital Footprint dalam Cyber Culture?',
                'opsi_a' => 'Jejak rekam data aktivitas pengguna di internet yang dapat dilacak',
                'opsi_b' => 'Kecepatan transfer data pada jaringan serat optik',
                'opsi_c' => 'Ukuran file dokumen digital dalam satuan megabyte',
                'opsi_d' => 'Jenis perangkat keras komputer untuk grafis',
                'kunci' => 'A',
                'kata_kunci' => null,
                'bobot' => 10,
            ],
            [
                'mata_kuliah_id' => $mkCyber->id,
                'tipe' => 'pg',
                'pertanyaan' => 'Prinsip etika utama dalam bermedia digital di era siber yang dikenal dengan Netiquette adalah...',
                'opsi_a' => 'Mengunggah konten sebanyak mungkin setiap jam',
                'opsi_b' => 'Menghormati privasi, bersikap sopan, dan memverifikasi kebenaran informasi',
                'opsi_c' => 'Menggunakan bot untuk meningkatkan engagement media sosial',
                'opsi_d' => 'Menyebarkan opini tanpa perlu menyebutkan sumber kutipan',
                'kunci' => 'B',
                'kata_kunci' => null,
                'bobot' => 10,
            ],
            [
                'mata_kuliah_id' => $mkCyber->id,
                'tipe' => 'pg',
                'pertanyaan' => 'Salah satu ancaman siber berupa upaya penipuan dengan menyamar sebagai institusi terpercaya untuk mencuri data pribadi disebut...',
                'opsi_a' => 'Phishing',
                'opsi_b' => 'Overclocking',
                'opsi_c' => 'Rendering',
                'opsi_d' => 'Debugging',
                'kunci' => 'A',
                'kata_kunci' => null,
                'bobot' => 10,
            ],
            [
                'mata_kuliah_id' => $mkCyber->id,
                'tipe' => 'essay',
                'pertanyaan' => 'Jelaskan dampak positif dan negatif dari transformasi budaya siber terhadap kehidupan sosial generasi muda saat ini!',
                'opsi_a' => null,
                'opsi_b' => null,
                'opsi_c' => null,
                'opsi_d' => null,
                'kunci' => null,
                'kata_kunci' => 'positif, komunikasi, informasi, global, negatif, adiksi, privasi, hoax, sosial',
                'bobot' => 20,
            ],
            [
                'mata_kuliah_id' => $mkCyber->id,
                'tipe' => 'essay',
                'pertanyaan' => 'Uraikan langkah-langkah konkret yang harus dilakukan untuk menjaga keamanan identitas digital (cyber hygiene) pada akun-akun penting!',
                'opsi_a' => null,
                'opsi_b' => null,
                'opsi_c' => null,
                'opsi_d' => null,
                'kunci' => null,
                'kata_kunci' => 'password, 2fa, autentikasi, enkripsi, update, phishing, privasi, backup',
                'bobot' => 20,
            ],
        ];

        $createdSoalIds = [];
        foreach ($soalList as $s) {
            $soal = Soal::create($s);
            $createdSoalIds[] = $soal->id;
        }

        // 6. Buat Ujian Contoh (Cyber Culture - simulasi) sesuai screenshot
        $ujian = Ujian::firstOrCreate(
            ['kode' => 'SNJ625006'],
            [
                'judul' => 'Cyber Culture ( simulasi )',
                'mata_kuliah_id' => $mkCyber->id,
                'jenis' => 'CPMK',
                'cpmk_ke' => 1,
                'durasi' => 60,
                'waktu_mulai' => now()->subDay(),
                'waktu_selesai' => now()->addDays(7),
                'is_active' => true,
                'acak_soal' => true,
                'tampilkan_nilai' => true,
                'max_pelanggaran' => 3,
                'keterangan' => 'Ujian simulasi mata kuliah Cyber Culture. Pastikan koneksi internet stabil dan jangan berpindah tab saat ujian.',
            ]
        );

        // Kaitkan ke kelas 2B & Semester PBA-3ABC
        $ujian->kelas()->syncWithoutDetaching([$kelas2B->id, $kelas3ABC->id]);

        // Kaitkan soal ke ujian
        $ujian->soal()->syncWithoutDetaching($createdSoalIds);
    }
}
