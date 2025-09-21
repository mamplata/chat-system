<?php

use GuzzleHttp\Client;

if (!function_exists('broadcastToNode')) {
    /**
     * Broadcast payload to Node.js server.
     *
     * @param array $payload The data to send. Can include 'message' and/or 'files'.
     * @return bool
     */
    function broadcastToNode(array $payload): bool
    {
        $client = new Client();
        $url = 'http://localhost:3000/api/new-message'; // single endpoint now

        try {
            $client->post($url, ['json' => $payload]);
            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to notify Node.js: " . $e->getMessage());
            return false;
        }
    }
}
