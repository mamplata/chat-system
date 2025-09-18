<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title'); ?></title>
    <script src="https://cdn.socket.io/4.8.1/socket.io.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<style>
    * {
        box-sizing: border-box;
    }

    button {
        background-color: blue;
        border: none;
        color: #fff;
        border-radius: 10px;
        padding: 5px;
        width: 100%;
    }

    input {
        margin-bottom: 5px;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    h2 {
        text-align: center;
    }

    #chat-box {
        max-height: 50vh;
        /* half the viewport height */
        overflow-y: auto;
    }
</style>

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