<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #D9E1F2;
        }
    </style>
</head>
<body>
    <h3><?= $title ?></h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Author</th>
                <th>Tahun</th>
                <th>Kategori</th>
                <th>Rak</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $i => $book): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $book['title'] ?></td>
                <td><?= $book['author'] ?></td>
                <td><?= $book['year'] ?></td>
                <td><?= $book['category'] ?></td>
                <td><?= $book['rack'] ?></td>
                <td><?= $book['quantity'] ?? 0 ?></td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
