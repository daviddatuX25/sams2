<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1>Profile</h1>
    <div id="ajax-message" class="alert d-none"></div>
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
                    <form id="photo-form" action="<?= site_url($urlRedirect) ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_photo">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Upload Photo</label>
                            <input type="file" class="form-control <?= $validation->hasError('profile_picture') ? 'is-invalid' : '' ?>" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png" onchange="previewImage(event)">
                            <div class="invalid-feedback" id="profile_picture_error">
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
                    <form id="profile-form" action="<?= site_url($urlRedirect) ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <input type="text" class="form-control <?= $validation->hasError('user_key') ? 'is-invalid' : '' ?>" id="user_key" name="user_key" value="<?= esc($user['user_key'] ?? '') ?>" aria-describedby="user_key_error">
                            <div id="user_key_error" class="invalid-feedback">
                                <?= $validation->getError('user_key') ?: 'User key must be 3-50 alphanumeric characters.' ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control <?= $validation->hasError('first_name') ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= esc($user['first_name'] ?? '') ?>" aria-describedby="first_name_error" required>
                                <div id="first_name_error" class="invalid-feedback">
                                    <?= $validation->getError('first_name') ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control <?= $validation->hasError('last_name') ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= esc($user['last_name'] ?? '') ?>" aria-describedby="last_name_error" required>
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
                                <option value="male" <?= isset($user['gender']) && $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= isset($user['gender']) && $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= isset($user['gender']) && $user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
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
                    <form id="password-form" action="<?= site_url($urlRedirect) ?>" method="post">
                        <?= csrf_field() ?>
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
                            <input type="password" class="form-control <?= $validation->hasError('new_password') ? 'is-invalid' : '' ?>" id="new_password" name="new_password" aria-describedby="new_password_error" required minlength="8">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

$(document).ready(function() {
    function showMessage(response, form) {
        $('#ajax-message').removeClass('d-none alert-success alert-danger');
        if (response.success) {
            $('#ajax-message').addClass('alert-success').text(response.message);
            form[0].reset();
            if (response.data && response.data.profile_picture) {
                $('#profilePreview').attr('src', '<?= site_url() ?>' + response.data.profile_picture);
            }
            if (response.user) {
                const user = response.user;
                $('#first_name').val(user.first_name);
                $('#last_name').val(user.last_name);
                $('#middle_name').val(user.middle_name || '');
                $('#birthday').val(user.birthday || '');
                $('#gender').val(user.gender || 'male');
                $('#bio').val(user.bio || '');
                $('#user_key').val(user.user_key);
            }
        } else {
            let message = response.message;
            if (response.error_code === 'user_id_not_found') {
                message = 'User account not found. Please log in again.';
            } else if (response.error_code === 'user_key_exists') {
                message = 'The user key is already taken. Please choose another.';
            } else if (response.error_code === 'no_changes_detected') {
                message = 'No changes were made to your profile.';
            } else if (response.error_code === 'validation_failed' && response.errors.user_key) {
                message = response.errors.user_key;
            } else if (response.error_code === 'incorrect_old_password' || response.error_code === 'incorrect_temporary_password') {
                message = 'The old password is incorrect.';
            } else if (response.error_code === 'invalid_file_type') {
                message = 'Invalid file type. Only JPG, JPEG, PNG are allowed.';
            }
            $('#ajax-message').addClass('alert-danger').text(message);
            if (response.errors) {
                $.each(response.errors, function(field, error) {
                    $(`#${field}_error`).text(error).addClass('invalid-feedback');
                    $(`#${field}`).addClass('is-invalid');
                });
            }
        }
        setTimeout(() => $('#ajax-message').addClass('d-none'), 5000);
    }

    // Profile Form
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const userKey = $('#user_key').val();
        if (!/^[A-Za-z0-9]{3,50}$/.test(userKey)) {
            $('#user_key').addClass('is-invalid');
            $('#user_key_error').text('User key must be 3-50 alphanumeric characters.').addClass('invalid-feedback');
            return;
        }
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showMessage(response, $('#profile-form'));
            },
            error: function() {
                $('#ajax-message').removeClass('d-none').addClass('alert-danger').text('An error occurred.');
                setTimeout(() => $('#ajax-message').addClass('d-none'), 5000);
            }
        });
    });

    // Photo Form
    $('#photo-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const fileInput = $('#profile_picture')[0].files[0];
        if (fileInput && !['image/jpeg', 'image/png'].includes(fileInput.type)) {
            $('#profile_picture').addClass('is-invalid');
            $('#profile_picture_error').text('Only JPG, JPEG, PNG files are allowed.').addClass('invalid-feedback');
            return;
        }
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: new FormData(this),
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                showMessage(response, $('#photo-form'));
            },
            error: function() {
                $('#ajax-message').removeClass('d-none').addClass('alert-danger').text('An error occurred.');
                setTimeout(() => $('#ajax-message').addClass('d-none'), 5000);
            }
        });
    });

    // Password Form
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const newPassword = $('#new_password').val();
        if (newPassword.length < 8) {
            $('#new_password').addClass('is-invalid');
            $('#new_password_error').text('Password must be at least 8 characters long.').addClass('invalid-feedback');
            return;
        }
        if (newPassword !== $('#confirm_password').val()) {
            $('#confirm_password').addClass('is-invalid');
            $('#confirm_password_error').text('Passwords do not match.').addClass('invalid-feedback');
            return;
        }
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showMessage(response, $('#password-form'));
            },
            error: function() {
                $('#ajax-message').removeClass('d-none').addClass('alert-danger').text('An error occurred.');
                setTimeout(() => $('#ajax-message').addClass('d-none'), 5000);
            }
        });
    });
});
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