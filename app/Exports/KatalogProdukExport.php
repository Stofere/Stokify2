<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class KatalogProdukExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected Collection $groupedProduk;
    protected string $namaKategori;
    protected string $tanggal;

    public function __construct(Collection $groupedProduk, string $namaKategori, string $tanggal)
    {
        $this->groupedProduk = $groupedProduk;
        $this->namaKategori = $namaKategori;
        $this->tanggal = $tanggal;
    }

    public function collection(): Collection
    {
        $rows = collect();
        $nomor = 1;

        foreach ($this->groupedProduk as $kategori => $produks) {
            // Baris header kategori
            $rows->push(['' , "KATEGORI: {$kategori}", '', '', '']);

            foreach ($produks as $prod) {
                $stok = $prod->lacak_stok
                    ? (fmod($prod->stok_saat_ini, 1) == 0 ? (int)$prod->stok_saat_ini : $prod->stok_saat_ini) . ' ' . $prod->satuan
                    : 'Unlimited';

                $rows->push([
                    $nomor++,
                    $prod->kode_barang,
                    $prod->nama_produk,
                    $stok,
                    $prod->lokasi ?? '-',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Kode / SKU', 'Nama Barang', 'Stok', 'Lokasi'];
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 18, 'C' => 40, 'D' => 15, 'E' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', "LAPORAN KATALOG & STOK FISIK GUDANG — Filter: {$this->namaKategori} | Cetak: {$this->tanggal}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Geser heading ke baris 2
        $sheet->insertNewRowBefore(2, 1);

        // Style heading row (baris 2 setelah insert)
        $headingStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2C3E50']],
        ];

        // Style baris kategori (scan semua baris)
        $highestRow = $sheet->getHighestRow();
        for ($row = 3; $row <= $highestRow; $row++) {
            $cellB = $sheet->getCell("B{$row}")->getValue();
            if ($cellB && str_starts_with((string)$cellB, 'KATEGORI:')) {
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", $cellB);
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '2980B9'], 'size' => 10],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F4F8']],
                ]);
            }
        }

        return [2 => $headingStyle];
    }

    public function title(): string
    {
        return 'Katalog Produk';
    }
}
