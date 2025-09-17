<?= $this->extend('layout') ?>

<?= $this->section('title') ?>
Home
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h2>Home</h2>
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
<form action="logout" method="post">
    <?= csrf_field() ?>
    <button type="submit">Logout</button>
</form>
<h1>
    Welcome aboard, <?= session()->get('name') ?>!
</h1>
<form action="<?= site_url('sendMessage') ?>" method="post">
    <?= csrf_field() ?>
    <label for="message"><input type="text" name="message" placeholder="Send a message..."></label>
    <button type="submit" style="width: 50%;">Send</button>
</form>
<?php if (!empty($messages)) : ?>
    <?php foreach ($messages as $msg) : ?>
        <p>
            <strong><?= esc($msg['user_name']) ?>: </strong>
            <?= esc($msg['message']) ?>
            <span style="color: #6c757d; font-size: 12px;"><?= esc($msg['created_at']) ?></span>
        </p>
    <?php endforeach; ?>
<?php else : ?>
    <p>No messages yet.</p>
<?php endif; ?>
<?= $this->endSection() ?>