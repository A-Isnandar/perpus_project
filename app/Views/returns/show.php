<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Detail Pengembalian</title>
<style>
  #qr-code {
    background-image: url(<?= !empty($loan['qr_code']) ? base_url(LOANS_QR_CODE_URI . $loan['qr_code']) : ''; ?>);
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    max-width: 500px;
    height: 300px;
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use CodeIgniter\I18n\Time;

$now = Time::now(locale: 'id');
$loanDate   = Time::parse($loan['loan_date'], locale: 'id');
$dueDate    = Time::parse($loan['due_date'], locale: 'id');
$returnDate = Time::parse($loan['return_date'], locale: 'id');

$isLate    = $returnDate->isAfter($dueDate);
$totalFine = $loan['fine_amount'] ?? 0;
$paid      = $loan['amount_paid'] ?? 0;
$remaining = $totalFine - $paid;
$isPaid    = $remaining <= 0;
?>

<?php if (session()->getFlashdata('msg')) : ?>
  <div class="pb-2">
    <div class="alert <?= (session()->getFlashdata('error') ?? false) ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
      <?= session()->getFlashdata('msg') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between mb-4">
      <div>
        <a href="<?= base_url('admin/returns'); ?>" class="btn btn-outline-primary">
          <i class="ti ti-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="d-flex gap-2 justify-content-end gap-2">
        <form action="<?= base_url("admin/returns/{$loan['uid']}"); ?>" method="post">
          <?= csrf_field(); ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-danger mb-2" onclick="return confirm('Are you sure?');">
            <i class="ti ti-x"></i> Batalkan pengembalian
          </button>
        </form>
      </div>
    </div>

    <h5 class="card-title fw-semibold mb-4">Detail Pengembalian</h5>

    <?php
    $memberData = [
      'Nama Lengkap'  => [$loan['first_name'] . ' ' . $loan['last_name']],
      'kelas'         => $loan['class'] ?? '-',
      'Email'         => $loan['email'] ?? '-',
      'Alamat'        => $loan['address'] ?? '-',
    ];

    $bookData = [
      'Judul buku' => [$loan['book_title']],
      'Pengarang'  => $loan['author'] ?? '-',
      'Penerbit'   => $loan['publisher'] ?? '-',
      'Rak'        => $loan['rack_name'] ?? '-',
    ];
    ?>

    <div class="row mb-3">
      <!-- member data -->
      <div class="col-12 col-md-6 d-flex flex-wrap">
        <div class="mb-4">
          <table>
            <?php foreach ($memberData as $key => $value) : ?>
              <tr>
                <td><h5><b><?= $key; ?></b></h5></td>
                <td style="width:15px" class="text-center"><h5><b>:</b></h5></td>
                <td><h5><b><?= is_array($value) ? $value[0] : $value; ?></b></h5></td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>

      <!-- book data -->
      <div class="col-12 col-md-6 d-flex flex-wrap">
        <div class="mb-4">
          <table>
            <?php foreach ($bookData as $key => $value) : ?>
              <tr>
                <td><h5><b><?= $key; ?></b></h5></td>
                <td style="width:15px" class="text-center"><h5><b>:</b></h5></td>
                <td><h5><b><?= is_array($value) ? $value[0] : $value; ?></b></h5></td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 col-lg-8">
    <div class="row">
      <!-- quantity -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="card" style="height: 180px;">
          <div class="card-body text-center">
            <h2><i class="ti ti-book"></i></h2>
            <h5>Jumlah buku dipinjam</h5>
            <h4><?= $loan['quantity']; ?></h4>
          </div>
        </div>
      </div>

      <!-- loan date -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="card" style="height: 180px;">
          <div class="card-body text-center">
            <h2><i class="ti ti-calendar-check"></i></h2>
            <h5>Waktu pinjam</h5>
            <div><?= $loanDate->toLocalizedString('d MMMM y'); ?></div>
            <?= $loanDate->toLocalizedString('HH:mm:ss'); ?>
          </div>
        </div>
      </div>

      <!-- due date -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="card" style="height: 180px;">
          <div class="card-body text-center">
            <h2><i class="ti ti-calendar-due"></i></h2>
            <h5>Batas waktu pengembalian</h5>
            <h4><?= $dueDate->toLocalizedString('d MMMM y'); ?></h4>
          </div>
        </div>
      </div>

      <!-- return date -->
      <div class="col-12 col-sm-6 col-xl-4">
        <div class="card" style="height: 180px;">
          <div class="card-body text-center">
            <h2><i class="ti ti-calendar-check"></i></h2>
            <h5>Tanggal pengembalian</h5>
            <div><?= $returnDate->toLocalizedString('d MMMM y'); ?></div>
            <?= $returnDate->toLocalizedString('HH:mm:ss'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- qr code & fines -->
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-body text-center">
        <p class="mb-4" style="line-break: anywhere;">UID: <?= $loan['uid']; ?></p>
        <div id="qr-code" class="m-auto d-flex"></div>
        <?php if (empty($loan['qr_code']) || !file_exists(LOANS_QR_CODE_PATH . $loan['qr_code'])) : ?>
          <a href="<?= base_url("admin/returns/{$loan['uid']}?update-qr-code=true"); ?>" class="btn btn-outline-primary mt-2 w-100">Generate QR Code</a>
        <?php endif; ?>

        <hr>
        <h5>Denda & Status</h5>
        <p>Total denda: Rp<?= number_format($totalFine,0,',','.') ?></p>
        <p>Telah dibayar: Rp<?= number_format($paid,0,',','.') ?></p>
        <p>Sisa bayar: Rp<?= number_format($remaining,0,',','.') ?></p>
        <p>Status:
          <?php if($isPaid): ?>
            <span class="badge bg-success">Lunas</span>
          <?php else: ?>
            <span class="badge bg-danger">Menunggak</span>
          <?php endif; ?>
        </p>
        <?php if(!$isPaid): ?>
          <a href="<?= base_url("admin/fines/pay/{$loan['uid']}"); ?>" class="btn btn-warning w-100">Bayar Denda</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
