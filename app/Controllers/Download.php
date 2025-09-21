<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\ResponseInterface;

class Download extends BaseController
{
    public function file($filename)
    {
        $secret = 'my-super-secret-key';
        $expiry = (int) $this->request->getGet('expiry');
        $token  = (string) $this->request->getGet('token');


        // 1. Check expiry
        if (!$expiry || $expiry < time()) {
            return $this->response->setStatusCode(403)->setBody('Link expired');
        }

        // 2. Verify token
        $data      = $filename . '|' . $expiry;
        $validHash = hash_hmac('sha256', $data, $secret);

        if (!$token || !hash_equals($validHash, $token)) {
            return $this->response->setStatusCode(403)->setBody('Invalid token');
        }

        // 3. Build file path
        $filePath = WRITEPATH . 'uploads/' . $filename;

        if (!is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        // 4. Serve file
        $file = new File($filePath);

        return $this->response
            ->download($filePath, null)
            ->setFileName($file->getBasename());
    }
}
