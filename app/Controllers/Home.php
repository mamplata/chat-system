<?php

namespace App\Controllers;

use App\Models\ChatFileModel;
use App\Models\ChatModel;
use GuzzleHttp\Client;

class Home extends BaseController
{
    protected $chatModel;
    protected $chatFileModel;
    protected $session;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->chatFileModel = new ChatFileModel();
        helper(['file', 'broadcast']);

        $this->session = session();
    }

    public function index(): string
    {
        // Fetch messages with user info
        $messages = $this->chatModel
            ->select('chats.*, users.name as user_name')
            ->join('users', 'users.id = chats.user_id')
            ->orderBy('chats.created_at', 'ASC')
            ->findAll();

        // Attach files with signed links (safe with try/catch)
        foreach ($messages as &$msg) {
            try {
                $msg['files'] = generateSignedFileLinks($msg['id'], $this->chatFileModel);
            } catch (\Throwable $e) {
                log_message('error', "File link generation failed for chat {$msg['id']}: " . $e->getMessage());
                $msg['files'] = []; // fallback to empty files
            }
        }

        return view('home', ['messages' => $messages]);
    }

    public function sendMessage()
    {
        $user_id = session()->get('user_id');
        $data = $this->request->getPost();
        $message = isset($data['message']) ? trim($data['message']) : '';
        $uploadedFiles = $this->request->getFiles();

        // Check if any actual file is uploaded
        $hasFiles = false;
        if (!empty($uploadedFiles['files'])) {
            foreach ($uploadedFiles['files'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $hasFiles = true;
                    break;
                }
            }
        }

        // Prevent completely empty submissions
        if ($message === '' && !$hasFiles) {
            return $this->response->setJSON([
                'status' => 'warning',
                'message' => 'Cannot send empty message.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Validate message if present
        if ($message !== '') {
            if (!$this->validate(['message' => 'max_length[100]'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }

        // Insert chat record
        $chatId = $this->chatModel->insert([
            'user_id' => $user_id,
            'message' => $message !== '' ? $message : null,
            'created_at' => date('Y-m-d H:i:s')
        ], true);

        // Handle files
        $files = [];
        if ($hasFiles) {
            handleChatFiles($uploadedFiles, $chatId, $this->chatFileModel);
            $files = generateSignedFileLinks($chatId, $this->chatFileModel);
        }

        // Prepare payload
        $payload = [
            'message'   => $message !== '' ? $message : null,
            'files'     => $files,
            'user_id'   => $user_id,
            'user_name' => session()->get('name'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Broadcast to Node.js
        broadcastToNode($payload);

        // Return success response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Message sent successfully...',
            'payload' => $payload,
            'csrf_hash' => csrf_hash()
        ]);
    }
}
