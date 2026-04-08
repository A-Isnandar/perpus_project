<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $title ?? 'Laporan Denda' ?></title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 20px; }
        .no-data { text-align: center; margin-top: 50px; font-style: italic; }
    </style>
</head>
<body>
    <h2><?= $title ?? 'Laporan Denda' ?></h2>

    <?php if (!empty($message)): ?>
        <p class="no-data"><?= esc($message) ?></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Tgl Pengembalian</th>
                    <th>Denda Dibayar</th>
                    <th>Jumlah Denda</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($fines as $fine): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc(trim(($fine['first_name'] ?? '') . ' ' . ($fine['last_name'] ?? ''))) ?: '-' ?></td>
                    <td><?= esc($fine['title'] ?? '-') ?></td>
                    <td><?= !empty($fine['return_date']) ? date('d/m/Y', strtotime($fine['return_date'])) : '-' ?></td>
                    <td>Rp<?= number_format($fine['amount_paid'] ?? 0, 0, ',', '.') ?></td>
                    <td>Rp<?= number_format($fine['fine_amount'] ?? 0, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
