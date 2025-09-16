<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/*
* Register: name, email and password
* Login: email and password
*/

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

    public function showLogin()
    {
        return view('auth/login');
    }

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
        return redirect()->to('/login');
    }

    public function login()
    {
        $data = $this->request->getPost();

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]'
        ];

        if (!$this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        $user = $this->userModel->where('email', $validData['email'])->first();

        if ($user && password_verify($validData['password'], $user['password'])) {

            $this->session->set([
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'isLoggenIn' => true,
            ]);

            $this->session->setFlashdata('success', 'Login user successfully...');
            return redirect()->to('');
        } else {
            return redirect()->back()->with('errors', 'Invalid credentials');
        }
    }

    public function logout()
    {
        $this->session->destroy();

        return redirect()->to('/login');
    }
}
