<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1>Profile</h1>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?php if (is_array(session()->getFlashdata('error'))): ?>
                <ul>
                    <?php foreach (session()->getFlashdata('error') as $field => $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <?= esc(session()->getFlashdata('error')) ?>
            <?php endif; ?>
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
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
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
                    <form action="<?= site_url(session()->get('role') . '/profile') ?>" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <i class="fas fa-key"></i>
                            <input type="text" class="form-control" id="user_key" name="user_key" value="<?= esc($user['user_key']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= esc($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= esc($user['last_name']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= esc($user['middle_name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="birthday" class="form-label">Birthday</label>
                            <input type="date" class="form-control" id="birthday" name="birthday" value="<?= esc($user['birthday']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio"><?= esc($user['bio']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form action="<?= site_url(session()->get('role') . '/profile') ?>" method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Old Password</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
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
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<style>
    .rounded-circle {
        border: 2px solid #3A98B9;
        object-fit: cover;
    }
</style>
<?php $this->endSection(); ?>