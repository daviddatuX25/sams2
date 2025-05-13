<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1>Profile</h1>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Photo -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Profile Photo</div>
                <div class="card-body text-center">
                    <img src="<?= site_url(session()->get('profile_picture') ?? 'assets/img/profile.png') ?>" alt="Profile Picture" class="rounded-circle mb-3" width="150" height="150" id="profilePreview">
                    <form action="<?= site_url(session()->get('role') . '/profile') ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_photo">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Upload Photo</label>
                            <input type="file" class="form-control <?= $validation->hasError('profile_picture') ? 'is-invalid' : '' ?>" id="profile_picture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                            <div class="invalid-feedback">
                                <?= $validation->getError('profile_picture') ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Photo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Profile Details</div>
                <div class="card-body">
                    <form action="<?= site_url(session()->get('role') . '/profile') ?>" method="post">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <input type="text" class="form-control <?= $validation->hasError('user_key') ? 'is-invalid' : '' ?>" id="user_key" name="user_key" value="<?= esc($user['user_key']) ?>" aria-describedby="user_key_error" required>
                            <div id="user_key_error" class="invalid-feedback">
                                <?= $validation->getError('user_key') ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control <?= $validation->hasError('first_name') ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= esc($user['first_name']) ?>" aria-describedby="first_name_error" required>
                                <div id="first_name_error" class="invalid-feedback">
                                    <?= $validation->getError('first_name') ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control <?= $validation->hasError('last_name') ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= esc($user['last_name']) ?>" aria-describedby="last_name_error" required>
                                <div id="last_name_error" class="invalid-feedback">
                                    <?= $validation->getError('last_name') ?>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control <?= $validation->hasError('middle_name') ? 'is-invalid' : '' ?>" id="middle_name" name="middle_name" value="<?= esc($user['middle_name'] ?? '') ?>" aria-describedby="middle_name_error">
                            <div id="middle_name_error" class="invalid-feedback">
                                <?= $validation->getError('middle_name') ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="birthday" class="form-label">Birthday</label>
                            <input type="date" class="form-control <?= $validation->hasError('birthday') ? 'is-invalid' : '' ?>" id="birthday" name="birthday" value="<?= esc($user['birthday'] ?? '') ?>" aria-describedby="birthday_error">
                            <div id="birthday_error" class="invalid-feedback">
                                <?= $validation->getError('birthday') ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-control <?= $validation->hasError('gender') ? 'is-invalid' : '' ?>" id="gender" name="gender" aria-describedby="gender_error" required>
                                <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <div id="gender_error" class="invalid-feedback">
                                <?= $validation->getError('gender') ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control <?= $validation->hasError('bio') ? 'is-invalid' : '' ?>" id="bio" name="bio" aria-describedby="bio_error"><?= esc($user['bio'] ?? '') ?></textarea>
                            <div id="bio_error" class="invalid-feedback">
                                <?= $validation->getError('bio') ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form action="<?= site_url(session()->get('role') . '/profile') ?>" method="post">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Old Password</label>
                            <input type="password" class="form-control <?= $validation->hasError('old_password') ? 'is-invalid' : '' ?>" id="old_password" name="old_password" aria-describedby="old_password_error" required>
                            <div id="old_password_error" class="invalid-feedback">
                                <?= $validation->getError('old_password') ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control <?= $validation->hasError('new_password') ? 'is-invalid' : '' ?>" id="new_password" name="new_password" aria-describedby="new_password_error" required>
                            <div id="new_password_error" class="invalid-feedback">
                                <?= $validation->getError('new_password') ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control <?= $validation->hasError('confirm_password') ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" aria-describedby="confirm_password_error" required>
                            <div id="confirm_password_error" class="invalid-feedback">
                                <?= $validation->getError('confirm_password') ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('profilePreview');
        output.src = reader.result;
    };
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

<style>
    .rounded-circle {
        border: 2px solid #3A98B9;
        object-fit: cover;
    }
    .invalid-feedback {
        font-size: 0.875rem;
    }
</style>
<?php $this->endSection(); ?>