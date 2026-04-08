<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Data Pengembalian Buku</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    th { background-color: #f2f2f2; }
    h2 { text-align: center; }

    /* Warna baris */
    .late { background-color: #f8d7da; }     /* merah muda = terlambat */
    .ontime { background-color: #d4edda; }   /* hijau muda = tepat waktu */
  </style>
</head>
<body>
  <h2>Laporan Data Pengembalian Buku</h2>
  <p>Tanggal Cetak: <?= date('d-m-Y H:i:s'); ?></p>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Peminjam</th>
        <th>Judul Buku</th>
        <th>Jumlah</th>
        <th>Tanggal Pinjam</th>
        <th>Tanggal Kembali</th>
        <th>Tenggat</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $no = 1;
        foreach ($loans as $loan):
          $returnDate = strtotime($loan['return_date']);
          $dueDate = strtotime($loan['due_date']);
          $rowClass = ($returnDate > $dueDate) ? 'late' : 'ontime';
      ?>
        <tr class="<?= $rowClass; ?>">
          <td><?= $no++; ?></td>
          <td><?= esc($loan['member_name']); ?></td>
          <td><?= esc($loan['title']); ?></td>
          <td><?= esc($loan['quantity']); ?></td>
          <td><?= date('d/m/Y', strtotime($loan['loan_date'])); ?></td>
          <td><?= date('d/m/Y', $returnDate); ?></td>
          <td><?= date('d/m/Y', $dueDate); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
