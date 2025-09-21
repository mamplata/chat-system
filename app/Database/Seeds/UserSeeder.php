<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'     => 'Nana Matsuoka',
                'email'    => 'nana7@shadowverse.com',
                'password' => password_hash('nana1234', PASSWORD_DEFAULT),
            ],
            [
                'name'     => 'Stellar May',
                'email'    => 'stellar@shadowverse.com',
                'password' => password_hash('nana1234', PASSWORD_DEFAULT),
            ],
        ];

        // Insert data into users table
        $this->db->table('users')->insertBatch($data);
    }
}
