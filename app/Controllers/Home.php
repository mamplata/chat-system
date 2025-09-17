<?php

namespace App\Controllers;

use App\Models\ChatModel;

class Home extends BaseController
{
    protected $chatModel;
    protected $session;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->session = session();
    }

    public function index(): string
    {
        $data['messages'] = $this->chatModel
            ->select('chats.*, users.name as user_name')
            ->join('users', 'users.id = chats.user_id')
            ->orderBy('chats.created_at', 'ASC')
            ->findAll();

        return view('home', $data);
    }

    public function sendMessage()
    {

        $user_id = session()->get('user_id');
        $data = $this->request->getPost();

        $rules = [
            'message' => 'required|min_length[1]|max_length[100]'
        ];

        if (!$this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        $this->chatModel->save([
            'user_id' => $user_id,
            'message' => $validData['message']
        ]);

        return redirect()
            ->back()
            ->with('success', 'Message sent successfully...');
    }
}
