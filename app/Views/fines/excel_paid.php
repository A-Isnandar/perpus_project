<?php
require '../../vendor/autoload.php';
include '../../config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query data denda lunas
$query = "
SELECT 
    members.first_name, members.last_name, books.title, loans.return_date, 
    fines.amount_paid, fines.fine_amount 
FROM fines
JOIN loans ON fines.loan_id = loans.id
JOIN members ON loans.member_id = members.id
JOIN books ON loans.book_id = books.id
WHERE fines.amount_paid >= fines.fine_amount
AND fines.deleted_at IS NULL
";

$result = mysqli_query($conn, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Lunas');

// Header kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Nama Peminjam');
$sheet->setCellValue('C1', 'Judul Buku');
$sheet->setCellValue('D1', 'Tgl Pengembalian');
$sheet->setCellValue('E1', 'Denda Dibayar');
$sheet->setCellValue('F1', 'Jumlah Denda');

// Isi data
$row = 2;
$no = 1;

while ($data = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue("A$row", $no++);
    $sheet->setCellValue("B$row", $data['first_name'] . ' ' . $data['last_name']);
    $sheet->setCellValue("C$row", $data['title']);
    $sheet->setCellValue("D$row", $data['return_date']);
    $sheet->setCellValue("E$row", $data['amount_paid']);
    $sheet->setCellValue("F$row", $data['fine_amount']);
    $row++;
}

// Style lebar kolom
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Download Excel
$filename = 'Laporan_Denda_Lunas_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
