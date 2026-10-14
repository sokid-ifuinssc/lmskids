<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->unsignedTinyInteger('sks')->default(2);
            $table->string('semester', 20)->nullable();
            $table->timestamps();
        });

        // Bank soal: soal milik mata kuliah, dapat dipakai di banyak ujian
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->enum('tipe', ['pg', 'essay'])->index();
            $table->text('pertanyaan');
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->char('kunci', 1)->nullable();       // A/B/C/D untuk PG
            $table->text('kata_kunci')->nullable();     // dipisah koma untuk essay
            $table->unsignedSmallInteger('bobot')->default(1);
            $table->timestamps();
        });

        Schema::create('ujian', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('judul');
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->enum('jenis', ['CPMK', 'UTS', 'UAS'])->index();
            $table->unsignedTinyInteger('cpmk_ke')->nullable(); // CPMK 1, CPMK 2, ...
            $table->unsignedSmallInteger('durasi')->default(60); // menit
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->boolean('is_active')->default(false);
            $table->boolean('acak_soal')->default(true);
            $table->boolean('tampilkan_nilai')->default(true);
            $table->unsignedTinyInteger('max_pelanggaran')->default(3);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('ujian_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->unique(['ujian_id', 'kelas_id']);
        });

        Schema::create('ujian_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->unique(['ujian_id', 'soal_id']);
        });

        Schema::create('ujian_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('mulai_at');
            $table->dateTime('batas_at');
            $table->dateTime('selesai_at')->nullable();
            $table->enum('status', ['berlangsung', 'selesai'])->default('berlangsung');
            $table->unsignedTinyInteger('pelanggaran')->default(0);
            $table->json('urutan_soal')->nullable();
            $table->decimal('nilai', 6, 2)->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
            $table->unique(['ujian_id', 'user_id']);
        });

        Schema::create('jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_peserta_id')->constrained('ujian_peserta')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->boolean('is_benar')->nullable();
            $table->decimal('persen_kunci', 5, 2)->nullable(); // % kata kunci essay yang cocok
            $table->decimal('skor', 8, 2)->default(0);
            $table->boolean('skor_manual')->default(false);
            $table->timestamps();
            $table->unique(['ujian_peserta_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban');
        Schema::dropIfExists('ujian_peserta');
        Schema::dropIfExists('ujian_soal');
        Schema::dropIfExists('ujian_kelas');
        Schema::dropIfExists('ujian');
        Schema::dropIfExists('soal');
        Schema::dropIfExists('mata_kuliah');
    }
};
