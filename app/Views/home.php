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

<?php if (session()->getFlashdata('warning')) : ?>
    <div class="alert alert-warning">
        <?= esc(session()->getFlashdata('warning')) ?>
    </div>
<?php endif; ?>

<h1>Welcome aboard, <?= session()->get('name') ?>!</h1>

<div id="chat-box">
    <?php if (!empty($messages)) : ?>
        <?php foreach ($messages as $msg) :
            $isOwn = session()->get('user_id') === $msg['user_id']; // compare by user_id
        ?>
            <div class="chat-message <?= $isOwn ? 'own' : 'other' ?>">
                <div class="chat-content">
                    <?php if (!$isOwn) : ?>
                        <strong><?= esc($msg['user_name']) ?>: </strong>
                    <?php endif; ?>
                    <?= esc($msg['message']) ?>
                    <span class="timestamp"><?= esc($msg['created_at']) ?></span>

                    <?php if (!empty($msg['files']) && is_array($msg['files'])) : ?>
                        <div class="chat-files">
                            <?php foreach ($msg['files'] as $file) : ?>
                                <?php if (str_starts_with($file['file_type'], 'image/')) : ?>
                                    <div><img src="<?= esc($file['url']) ?>" width="150"></div>
                                <?php else : ?>
                                    <div><a href="<?= esc($file['url']) ?>" target="_blank"><?= esc($file['file_name']) ?></a></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No messages yet.</p>
    <?php endif; ?>
</div>

<p id="typingIndicator" style="font-size: 12px; color: gray;"></p>

<form id="sendMessageForm" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label for="fileInput" class="file-label" title="Attach files">📎</label>
    <input type="file" name="files[]" id="fileInput" multiple>
    <input type="text" name="message" id="messageInput" placeholder="Type a message...">
    <button type="submit" id="sendButton">Send</button>
</form>

<div id="filePreviewContainer"></div>

<script>
    const socket = io("http://localhost:3000");
    const typingIndicator = $("#typingIndicator");
    let typingTimeout;

    const $messageInput = $("#messageInput");
    const $fileInput = $("#fileInput");
    const $filePreviewContainer = $("#filePreviewContainer"); // container for previews

    // Track selected files
    let selectedFiles = [];

    // File preview
    $fileInput.on("change", function() {
        const files = Array.from(this.files);

        files.forEach(file => {
            // Avoid duplicates
            if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);

                const $preview = $("<div class='file-preview'></div>");
                const $removeBtn = $("<div class='remove-file'>×</div>");

                $removeBtn.on("click", function() {
                    selectedFiles = selectedFiles.filter(f => f !== file);
                    $preview.remove();
                });

                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $preview.append(`<img src="${e.target.result}">`);
                    };
                    reader.readAsDataURL(file);
                } else {
                    $preview.append(`<span>${file.name}</span>`);
                }

                $preview.append($removeBtn);
                $filePreviewContainer.append($preview);
            }
        });

        // Clear input so same file can be re-selected
        $fileInput.val("");
        focusMessageInput();
    });

    // Always keep focus on message input
    function focusMessageInput() {
        setTimeout(() => $messageInput.focus(), 0);
    }
    $fileInput.on("click", focusMessageInput);

    // Socket connections
    socket.on("connect", () => console.log("Connected with ID:", socket.id));
    socket.on("newMessage", data => appendMessage(data));
    socket.on("typing", data => typingIndicator.text(`${data.user_name} is typing...`));
    socket.on("stopTyping", () => typingIndicator.text(""));

    // Append messages
    function appendMessage(data) {
        const $chatBox = $("#chat-box");
        $chatBox.find("p:contains('No messages yet.')").remove();

        // Determine alignment
        const isOwn = <?= session()->get('user_id') ?> === data.user_id;

        // Create container div
        const $msgDiv = $("<div>").addClass("chat-message " + (isOwn ? "own" : "other"));
        const $content = $("<div>").addClass("chat-content");

        if (!isOwn) {
            $content.append(`<strong>${data.user_name}: </strong>`);
        }

        $content.append(data.message ?? "");
        $content.append(`<span class="timestamp">${data.created_at}</span>`);

        // Files
        if (Array.isArray(data.files)) {
            const $filesDiv = $("<div>").addClass("chat-files");
            data.files.forEach(file => {
                if (file.file_type?.startsWith("image/")) {
                    $filesDiv.append(`<div><img src="${file.url}" width="150"></div>`);
                } else {
                    $filesDiv.append(`<div><a href="${file.url}" target="_blank">${file.file_name}</a></div>`);
                }
            });
            $content.append($filesDiv);
        }

        $msgDiv.append($content);
        $chatBox.append($msgDiv);
        $chatBox.scrollTop($chatBox[0].scrollHeight);
    }


    // Typing indicator
    $messageInput.on("input", function() {
        socket.emit("typing", {
            user_name: "<?= session()->get('name') ?>"
        });
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => socket.emit("stopTyping"), 1500);
    });

    // Send message + files
    $("#sendMessageForm").submit(function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('message', $messageInput.val());
        selectedFiles.forEach(f => formData.append('files[]', f));

        const csrfInput = $('input[name^="csrf_"]');
        formData.append(csrfInput.attr('name'), csrfInput.val());

        $.ajax({
            url: "<?= site_url('sendMessage') ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: response => {
                if (response.csrf_hash) csrfInput.val(response.csrf_hash);
                if (response.status === 'warning') return showAlert(response.message, 'warning');
                if (response.status === 'error') return showAlert(Object.values(response.errors).join("\n"), 'danger');

                // Clear input and previews
                $messageInput.val("");
                selectedFiles = [];
                $filePreviewContainer.empty();
                showAlert(response.message, 'success');
            },
            error: xhr => showAlert(xhr.status === 403 ? "CSRF token mismatch. Refresh the page." : "Something went wrong!", 'danger')
        });
    });

    // Alert function
    function showAlert(message, type = 'success') {
        const $alert = $(`<div class="ajax-alert ${type}" id="ajaxAlert">${message}</div>`);
        $("body").append($alert);
        setTimeout(() => $alert.fadeOut(500, () => $alert.remove()), 3000);
    }

    // Auto scroll on load
    $(function() {
        $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
    });
</script>

<?= $this->endSection() ?>