<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatFilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => [
                "type"           => "INT",
                "constraint"     => 11,
                "unsigned"       => true,
                "auto_increment" => true,
            ],
            "chat_id" => [
                "type"       => "INT",
                "constraint" => 11,
                "unsigned"   => true,
            ],
            "file_name" => [
                "type"       => "VARCHAR",
                "constraint" => 255,
            ],
            "file_path" => [
                "type"       => "VARCHAR",
                "constraint" => 255,
            ],
            "file_type" => [
                "type"       => "VARCHAR",
                "constraint" => 50,
            ],
            "created_at" => [
                "type" => "DATETIME",
                "null" => true,
            ],
            "updated_at" => [
                "type" => "DATETIME",
                "null" => true,
            ],
        ]);

        $this->forge->addKey("id", true);
        $this->forge->addForeignKey("chat_id", "chats", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("chat_files");
    }

    public function down()
    {
        $this->forge->dropTable("chat_files");
    }
}
