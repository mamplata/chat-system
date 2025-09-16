<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = session();
    }

    public function showRegister()
    {
        return view('auth/register');
    }

    /*
        Register User
        Valid Email, Password must 8 length long
    */
    public function register()
    {
        $data = $this->request->getPost();

        $rules = [
            'name' => 'required|min_length[2]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'matches[password]'
        ];

        if (!$this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        $validData['password'] = password_hash($validData['password'], PASSWORD_DEFAULT);
        unset($validData['confirm_password']);

        $this->userModel->insert($validData);

        $this->session->setFlashdata('success', 'Register user successfully...');
        return redirect()->to('/register');
    }
}
