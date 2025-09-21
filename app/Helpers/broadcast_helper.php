<?php

use GuzzleHttp\Client;

if (!function_exists('broadcastToNode')) {
    /**
     * Broadcast payload to Node.js server.
     *
     * @param array $payload The data to send.
     * @param string $event   Either 'newMessage' or 'newFile'.
     * @return bool
     */
    function broadcastToNode(array $payload, string $event = 'newMessage'): bool
    {
        $client = new Client();

        // Determine endpoint based on event
        $url = $event === 'newFile'
            ? 'http://localhost:3000/api/new-file'
            : 'http://localhost:3000/api/new-message';

        try {
            $client->post($url, ['json' => $payload]);
            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to notify Node.js for {$event}: " . $e->getMessage());
            return false;
        }
    }
}
