<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Pengembalian</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use CodeIgniter\I18n\Time;

if (session()->getFlashdata('msg')) : ?>
  <div class="pb-2">
    <div class="alert <?= (session()->getFlashdata('error') ?? false) ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
      <?= session()->getFlashdata('msg') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <div class="row mb-2">
      <div class="col-12 col-lg-5">
        <h5 class="card-title fw-semibold mb-4">Data Pengembalian</h5>
      </div>

      <div class="col-12 col-lg-7">
        <div class="d-flex gap-2 justify-content-md-end flex-wrap">
          <form action="" method="get" class="d-flex">
            <div class="input-group mb-3">
              <input type="text" class="form-control" name="search" value="<?= esc($search ?? ''); ?>" placeholder="Cari nama atau judul buku">
              <button class="btn btn-outline-secondary" type="submit" id="searchButton">Cari</button>
            </div>
          </form>

          <a href="<?= base_url('admin/returns/new/search'); ?>" class="btn btn-primary py-2">
            <i class="ti ti-plus"></i> Pengembalian Baru
          </a>

          <!-- ✅ Tombol Export PDF dan Excel -->
          <a href="<?= base_url('admin/returns/exportPdf'); ?>" class="btn btn-danger py-2">
            <i class="ti ti-file-text"></i> Export PDF
          </a>

          <a href="<?= base_url('admin/returns/exportExcel'); ?>" class="btn btn-success py-2">
            <i class="ti ti-file-spreadsheet"></i> Export Excel
          </a>
        </div>
      </div>
    </div>

    <div class="overflow-x-scroll">
      <table class="table table-hover table-striped">
        <thead class="table-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Nama Peminjam</th>
            <th scope="col">Judul Buku</th>
            <th scope="col" class="text-center">Jumlah</th>
            <th scope="col">Tgl Pinjam</th>
            <th scope="col">Tenggat</th>
            <th scope="col">Tgl Pengembalian</th>
            <th scope="col" class="text-center">Status</th>
            <th scope="col" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="table-group-divider">
          <?php
          $i = 1 + ($itemPerPage * ($currentPage - 1));
          $now = Time::now('Asia/Jakarta', 'id_ID');
          ?>

          <?php if (empty($loans)) : ?>
            <tr>
              <td class="text-center" colspan="9"><b>Tidak ada data pengembalian</b></td>
            </tr>
          <?php else: ?>
            <?php foreach ($loans as $loan): ?>
              <?php
              $loanCreateDate = Time::parse($loan['loan_date'], 'Asia/Jakarta', 'id_ID');
              $loanDueDate    = Time::parse($loan['due_date'], 'Asia/Jakarta', 'id_ID');
              $loanReturnDate = Time::parse($loan['return_date'], 'Asia/Jakarta', 'id_ID');
              $isLate = $loanReturnDate->isAfter($loanDueDate);
              ?>
              <tr>
                <th scope="row"><?= $i++; ?></th>
                <td>
                  <a href="<?= base_url("admin/members/{$loan['first_name']}"); ?>" class="text-primary text-decoration-underline">
                    <b><?= esc($loan['first_name'] . ' ' . $loan['last_name']); ?></b>
                  </a>
                </td>
                <td>
                  <p class="text-primary-emphasis text-decoration-underline"><b><?= esc($loan['title']); ?></b></p>
                </td>
                <td class="text-center"><?= esc($loan['quantity']); ?></td>
                <td>
                  <b><?= $loanCreateDate->toLocalizedString('dd/MM/yyyy'); ?></b><br>
                  <small><?= $loanCreateDate->toLocalizedString('HH:mm:ss'); ?></small>
                </td>
                <td><b><?= $loanDueDate->toLocalizedString('dd/MM/yyyy'); ?></b></td>
                <td class="<?= $isLate ? 'text-danger' : ''; ?>">
                  <b><?= $loanReturnDate->toLocalizedString('dd/MM/yyyy'); ?></b><br>
                  <small><?= $loanReturnDate->toLocalizedString('HH:mm:ss'); ?></small>
                </td>
                <td class="text-center">
                  <span class="badge bg-success rounded-3 fw-semibold">Selesai</span>
                </td>
                <td class="text-center">
                  <a href="<?= base_url("admin/returns/{$loan['uid']}"); ?>" class="btn btn-primary btn-sm">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
      <span>Menampilkan <?= count($loans) ?> data</span>
      <?= $pager->links('returns', 'my_pager'); ?>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
