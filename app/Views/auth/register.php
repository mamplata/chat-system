<?= $this->extend('layout') ?>

<?= $this->section('title') ?>
Register
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h2>Register</h2>
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')) : ?>
    <ul>
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <li><?= esc($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form action="/register" method="post">
    <?= csrf_field() ?>
    <label for="name">Name: <input type="text" name="name" placeholder="Name..."></label><br>
    <label for="email">Email: <input type="email" name="email" placeholder="Email..."></label><br>
    <label for="password">Password: <input type="password" name="password" placeholder="Password..."></label><br>
    <label for="confirm_password">Confirm: <input type="password" name="confirm_password" placeholder="Confirm..."></label><br><br>
    <button type="submit">Register</button>
</form><br>
<a href="<?= site_url('login') ?>">Go to Login</a>

<?= $this->endSection() ?>