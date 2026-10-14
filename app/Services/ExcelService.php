<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelService
{
    /**
     * Buat file xlsx dari header + baris data lalu kirim sebagai download.
     */
    public function download(string $filename, array $headers, array $rows, string $sheetTitle = 'Data', array $notes = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($sheetTitle, 0, 31));

        $rowStart = 1;
        foreach ($notes as $note) {
            $sheet->setCellValue([1, $rowStart], $note);
            $sheet->getStyle([1, $rowStart])->getFont()->setItalic(true)->getColor()->setRGB('555555');
            $rowStart++;
        }
        if ($notes) {
            $rowStart++;
        }

        foreach (array_values($headers) as $i => $h) {
            $sheet->setCellValue([$i + 1, $rowStart], $h);
        }
        $lastCol = max(1, count($headers));
        $headerRange = [1, $rowStart, $lastCol, $rowStart];
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3F7D5A');

        $r = $rowStart + 1;
        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $val) {
                $sheet->setCellValueExplicit(
                    [$i + 1, $r],
                    $val,
                    is_numeric($val) && ! (is_string($val) && preg_match('/^0\d+/', $val)) && strlen((string) $val) < 12
                        ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                        : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
            $r++;
        }

        for ($i = 1; $i <= $lastCol; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Baca file Excel/CSV menjadi array asosiatif berdasarkan baris header.
     * Header dinormalisasi: huruf kecil, spasi -> underscore.
     * Baris catatan sebelum header (yang tidak memuat kolom wajib) dilewati.
     */
    public function read(UploadedFile $file, array $requiredColumns): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $headerIndex = null;
        $headers = [];
        foreach ($rows as $idx => $row) {
            $norm = array_map(fn ($h) => str_replace(' ', '_', strtolower(trim((string) $h))), $row);
            if (empty(array_diff($requiredColumns, $norm))) {
                $headerIndex = $idx;
                $headers = $norm;
                break;
            }
        }

        if ($headerIndex === null) {
            throw new \RuntimeException('Format kolom tidak sesuai. Kolom wajib: ' . implode(', ', $requiredColumns));
        }

        $data = [];
        foreach (array_slice($rows, $headerIndex + 1) as $row) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $item = [];
            foreach ($headers as $i => $h) {
                if ($h !== '') {
                    $item[$h] = isset($row[$i]) ? trim((string) $row[$i]) : '';
                }
            }
            $data[] = $item;
        }

        return $data;
    }
}
