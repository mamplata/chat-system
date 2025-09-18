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
<div id="chat-box">
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
</div>
<p id="typingIndicator" style="font-size: 12px; color: gray;"></p>
<form id="sendMessageForm" method="post">
    <?= csrf_field() ?>
    <label for="message"><input type="text" name="message" id="messageInput" placeholder="Send a message..."></label>
    <button type="submit" style="width: 50%;">Send</button>
</form>
<script>
    const socket = io("http://localhost:3000");
    const typingIndicator = $("#typingIndicator");
    let typingTimeout;

    socket.on("connect", () => {
        console.log("ID: ", socket.id);
    });

    // Listen for new messages from Node.js
    socket.on("newMessage", function(data) {
        const chatBox = $("#chat-box");
        const messageHTML = `<p>
          <strong>${data.user_name}: </strong>${data.message}
          <span style="color: #6c757d; font-size: 12px;">${data.created_at}</span>
      </p>`;
        chatBox.append(messageHTML);

        // Scroll to the bottom automatically
        chatBox.scrollTop(chatBox[0].scrollHeight);
    });

    // Listen for typing indicator
    socket.on("typing", (data) => {
        typingIndicator.text(`${data.user_name} is typing...`);
    });
    socket.on("stopTyping", () => {
        typingIndicator.text("");
    });

    // Emit typing events
    $("#messageInput").on("input", function() {
        socket.emit("typing", {
            user_name: "<?= session()->get('name') ?>"
        });

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            socket.emit("stopTyping");
        }, 1500);
    });

    // Send message via AJAX instead of page reload
    $("form#sendMessageForm").submit(function(e) {
        e.preventDefault(); // stop form from submitting normally
        const message = $("input[name='message']").val();
        if (message.trim() === "") return;

        $.post("<?= site_url('sendMessage') ?>", {
            message: message,
            <?= csrf_token() ?>: "<?= csrf_hash() ?>"
        }, function(response) {
            $("input[name='message']").val(""); // clear input
        });
    });

    $(document).ready(function() {
        const chatBox = $("#chat-box");
        chatBox.scrollTop(chatBox[0].scrollHeight);
    });
</script>
<?= $this->endSection() ?>