<?= $this->extend('layout') ?>

<?= $this->section('title') ?>
Login
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h2>Login</h2>
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

<form action="/login" method="post">
    <?= csrf_field() ?>
    <label for="email">Email: <input type="email" name="email" placeholder="Email..."></label><br>
    <label for="password">Password: <input type="password" name="password" placeholder="Password..."></label><br>
    <button type="submit">Login</button>
</form><br>
<a href="<?= site_url('register') ?>">Register here</a>
<?= $this->endSection() ?>