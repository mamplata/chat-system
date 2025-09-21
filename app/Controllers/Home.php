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

        $rules = ['message' => 'required|max_length[100]'];

        if (!$this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        $this->chatModel->insert([
            'user_id' => $user_id,
            'message' => $validData['message'],
            'created_at' => date('Y-m-d H:i:s')
        ], true); // we ignore the returned insert ID

        // Prepare payload
        $payload = [
            'message' => $validData['message'],
            'user_id' => $user_id,
            'user_name' => session()->get('name'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Broadcast
        broadcastToNode($payload, 'newMessage');

        return redirect()->back()->with('success', 'Message sent successfully...');
    }

    public function sendFile()
    {
        $user_id = session()->get('user_id');
        $uploadedFiles = $this->request->getFiles();

        if (empty($uploadedFiles) || !isset($uploadedFiles['files'])) {
            return redirect()->back()->with('warning', 'No files uploaded.');
        }

        // Save files
        $chatId = $this->chatModel->insert([
            'user_id' => $user_id,
            'message' => null,
            'created_at' => date('Y-m-d H:i:s')
        ], true);

        handleChatFiles($uploadedFiles, $chatId, $this->chatFileModel);

        // Generate signed links
        $files = generateSignedFileLinks($chatId, $this->chatFileModel);

        // Prepare payload
        $payload = [
            'files' => $files,
            'user_id' => $user_id,
            'user_name' => session()->get('name'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Broadcast
        broadcastToNode($payload, 'newFile');

        return redirect()->back()->with('success', 'Files sent successfully...');
    }
}
