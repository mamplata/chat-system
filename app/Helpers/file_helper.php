<?php

if (!function_exists('handleChatFiles')) {
    /**
     * Save uploaded files and link them to a chat.
     */
    function handleChatFiles($files, $chatId, $chatFileModel)
    {
        if (!$files || !isset($files['files'])) {
            return [];
        }

        foreach ($files['files'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(WRITEPATH . 'uploads', $newName);

                $chatFileModel->insert([
                    'chat_id'   => $chatId,
                    'file_name' => $file->getClientName(),
                    'file_path' => 'uploads/' . $newName,
                    'file_type' => $file->getClientMimeType(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}

if (!function_exists('generateSignedFileLinks')) {
    /**
     * Generate signed file URLs for secure download.
     */
    function generateSignedFileLinks($chatId, $chatFileModel)
    {
        $secret = 'my-super-secret-key';
        $expiry = time() + 3600; // valid for 1 hour

        $files = $chatFileModel->where('chat_id', $chatId)->findAll();

        return array_map(function ($f) use ($secret, $expiry) {
            $filename = basename($f['file_path']);
            $data = $filename . '|' . $expiry;
            $token = hash_hmac('sha256', $data, $secret);

            return [
                'file_name' => $f['file_name'],
                'file_type' => $f['file_type'],
                'url'       => site_url("download/{$filename}?expiry={$expiry}&token={$token}")
            ];
        }, $files);
    }
}
