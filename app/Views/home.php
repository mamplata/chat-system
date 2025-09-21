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

<h1>Welcome aboard, <?= session()->get('name') ?>!</h1>

<div id="chat-box">
    <?php if (!empty($messages)) : ?>
        <?php foreach ($messages as $msg) : ?>
            <p>
                <strong><?= esc($msg['user_name']) ?>: </strong>
                <?= esc($msg['message']) ?>
                <span style="color: #6c757d; font-size: 12px;"><?= esc($msg['created_at']) ?></span>

                <?php if (!empty($msg['files']) && is_array($msg['files'])) : ?>
                    <br>
                    <?php foreach ($msg['files'] as $file) : ?>
                        <?php if (str_starts_with($file['file_type'], 'image/')) : ?>
            <div><img src="<?= esc($file['url']) ?>" width="150"></div>
        <?php else : ?>
            <div><a href="<?= esc($file['url']) ?>" target="_blank"><?= esc($file['file_name']) ?></a></div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</p>
<?php endforeach; ?>
<?php else : ?>
    <p>No messages yet.</p>
<?php endif; ?>
</div>

<p id="typingIndicator" style="font-size: 12px; color: gray;"></p>

<!-- Separate forms for text and files -->
<form id="sendMessageForm">
    <?= csrf_field() ?>
    <input type="text" name="message" id="messageInput" placeholder="Send a message...">
    <button type="submit" style="width: 50%;">Send Message</button>
</form>

<form id="sendFileForm" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="file" name="files[]" id="fileInput" multiple>
    <button type="submit" style="width: 50%;">Send Files</button>
</form>

<script>
    const socket = io("http://localhost:3000");
    const typingIndicator = $("#typingIndicator");
    let typingTimeout;

    // Socket connections
    socket.on("connect", () => console.log("Connected with ID: ", socket.id));

    // Receive new messages
    socket.on("newMessage", data => appendMessage(data, 'message'));
    socket.on("newFile", data => appendMessage(data, 'files'));

    // Function to append messages or files
    function appendMessage(data, type) {
        const chatBox = $("#chat-box");
        chatBox.find("p:contains('No messages yet.')").remove();

        let filesHtml = "";
        if (type === 'files' && Array.isArray(data.files) && data.files.length > 0) {
            data.files.forEach(file => {
                if (file.file_type && file.file_type.startsWith("image/")) {
                    filesHtml += `<div><img src="${file.url}" width="150"></div>`;
                } else {
                    filesHtml += `<div><a href="${file.url}" target="_blank">${file.file_name}</a></div>`;
                }
            });
        }

        const messageHTML = `<p>
        <strong>${data.user_name}: </strong>${data.message ?? ""}
        <span style="color: #6c757d; font-size: 12px;">${data.created_at}</span>
        ${filesHtml}
    </p>`;

        chatBox.append(messageHTML);
        chatBox.scrollTop(chatBox[0].scrollHeight);
    }

    // Typing indicator
    $("#messageInput").on("input", function() {
        socket.emit("typing", {
            user_name: "<?= session()->get('name') ?>"
        });
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => socket.emit("stopTyping"), 1500);
    });
    socket.on("typing", data => typingIndicator.text(`${data.user_name} is typing...`));
    socket.on("stopTyping", () => typingIndicator.text(""));

    // Send text message
    $("#sendMessageForm").submit(function(e) {
        e.preventDefault();
        const message = $("#messageInput").val();
        if (!message.trim()) return;

        $.post("<?= site_url('sendMessage') ?>", {
            message: message,
            <?= csrf_token() ?>: "<?= csrf_hash() ?>"
        }, () => $("#messageInput").val(""));
    });

    // Send files
    $("#sendFileForm").submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "<?= site_url('sendFile') ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: () => $("#fileInput").val("")
        });
    });

    // Auto scroll on load
    $(document).ready(() => {
        const chatBox = $("#chat-box");
        chatBox.scrollTop(chatBox[0].scrollHeight);
    });
</script>
<?= $this->endSection() ?>