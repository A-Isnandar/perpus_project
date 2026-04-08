<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('head') ?>
<title>Edit Anggota</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<a href="<?= previous_url() ?>" class="btn btn-outline-primary mb-3">
  <i class="ti ti-arrow-left"></i> Kembali
</a>

<?php if (session()->getFlashdata('msg')) : ?>
  <div class="pb-2">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= session()->getFlashdata('msg') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold">Edit Anggota</h5>

    <form action="<?= base_url('admin/members/' . $member['uid']); ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="_method" value="PUT">

      <!-- NAMA -->
      <div class="row mt-3">

        <div class="col-md-6 mb-3">
          <label class="form-label">Nama Depan</label>
          <input type="text"
                 class="form-control <?= $validation->hasError('first_name') ? 'is-invalid' : '' ?>"
                 name="first_name"
                 value="<?= $oldInput['first_name'] ?? $member['first_name'] ?>"
                 required>
          <div class="invalid-feedback"><?= $validation->getError('first_name') ?></div>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Nama Belakang</label>
          <input type="text"
                 class="form-control <?= $validation->hasError('last_name') ? 'is-invalid' : '' ?>"
                 name="last_name"
                 value="<?= $oldInput['last_name'] ?? $member['last_name'] ?>">
          <div class="invalid-feedback"><?= $validation->getError('last_name') ?></div>
        </div>

      </div>

      <!-- KELAS -->
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Kelas</label>
          <input type="text"
                 class="form-control <?= $validation->hasError('class') ? 'is-invalid' : '' ?>"
                 name="class"
                 value="<?= $oldInput['class'] ?? $member['class'] ?>"
                 placeholder="contoh: XII-RPL 1"
                 required>
          <div class="invalid-feedback"><?= $validation->getError('class') ?></div>
        </div>
      </div>

      <!-- EMAIL -->
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Email</label>
          <input type="email"
                 class="form-control <?= $validation->hasError('email') ? 'is-invalid' : '' ?>"
                 name="email"
                 value="<?= $oldInput['email'] ?? $member['email'] ?>"
                 required>
          <div class="invalid-feedback"><?= $validation->getError('email') ?></div>
        </div>
      </div>

      <!-- ALAMAT -->
      <div class="mb-3">
        <label class="form-label">Alamat</label>
        <textarea
            class="form-control <?= $validation->hasError('address') ? 'is-invalid' : '' ?>"
            name="address"
            required><?= $oldInput['address'] ?? $member['address'] ?></textarea>
        <div class="invalid-feedback"><?= $validation->getError('address') ?></div>
      </div>

      <!-- TANGGAL LAHIR + GENDER -->
      <div class="row">

        <div class="col-md-6 mb-3">
          <label class="form-label">Tanggal Lahir</label>
          <input type="date"
                 class="form-control <?= $validation->hasError('date_of_birth') ? 'is-invalid' : '' ?>"
                 name="date_of_birth"
                 value="<?= $oldInput['date_of_birth'] ?? $member['date_of_birth'] ?>"
                 required>
          <div class="invalid-feedback"><?= $validation->getError('date_of_birth') ?></div>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Jenis Kelamin</label>

          <?php $gender = $oldInput['gender'] ?? $member['gender']; ?>

          <div class="mt-2">
            <div class="form-check form-check-inline">
              <input type="radio"
                     class="form-check-input"
                     name="gender"
                     value="1"
                     <?= ($gender == '1' || $gender == 'Male') ? 'checked' : '' ?>>
              <label class="form-check-label">Laki-laki</label>
            </div>

            <div class="form-check form-check-inline">
              <input type="radio"
                     class="form-check-input"
                     name="gender"
                     value="2"
                     <?= ($gender == '2' || $gender == 'Female') ? 'checked' : '' ?>>
              <label class="form-check-label">Perempuan</label>
            </div>
          </div>

          <div class="invalid-feedback d-block">
            <?= $validation->getError('gender') ?>
          </div>
        </div>

      </div>

      <button type="submit" class="btn btn-primary mt-2">Simpan</button>

    </form>

  </div>
</div>

<?= $this->endSection() ?>
