<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title'); ?></title>
    <link rel="stylesheet" href="<?= base_url('css/index.css') ?>">
    <script src="https://cdn.socket.io/4.8.1/socket.io.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <header>Chat Application</header>

    <main>
        <?= $this->renderSection('content', false); ?>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?></p>
    </footer>
</body>

</html>