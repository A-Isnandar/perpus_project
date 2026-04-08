<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Peminjaman Belum Dikembalikan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        td.center { text-align: center; }

        /* warna baris telat */
        .overdue {
            background-color: #ffcccc !important; /* merah muda */
        }
    </style>
</head>
<body>

<h2>Data Peminjaman Belum Dikembalikan</h2>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Peminjam</th>
            <th>Judul Buku</th>
            <th>Jumlah</th>
            <th>Tanggal Pinjam</th>
            <th>Tenggat</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1; 
        $today = date('Y-m-d');
        foreach ($loans as $loan): 
            $isOverdue = ($loan['due_date'] < $today); 
        ?>
        <tr class="<?= $isOverdue ? 'overdue' : '' ?>">
            <td class="center"><?= $no++; ?></td>
            <td><?= $loan['member_name'] ?? $loan['first_name'] . ' ' . $loan['last_name']; ?></td>
            <td><?= $loan['title']; ?></td>
            <td class="center"><?= $loan['quantity']; ?></td>
            <td><?= date('d-m-Y', strtotime($loan['loan_date'])); ?></td>
            <td><?= date('d-m-Y', strtotime($loan['due_date'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p style="margin-top:20px; font-size:11px;">Dicetak pada: <?= date('d-m-Y H:i:s'); ?></p>

</body>
</html>
