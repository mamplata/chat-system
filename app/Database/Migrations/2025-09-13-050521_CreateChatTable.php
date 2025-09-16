<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
           "chat_id" => [
               "type" => "INT",
               "constraint" => 11,
               "unsigned" => true,
               "auto_increment" => true,
            ],
          "message" => [
             "type" => "VARCHAR",
             "constraint" => 255,
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
        
        $this->forge->addKey("chat_id", true);
        $this->forge->createTable("chats");
        
    }

    public function down()
    {
        $this->forge->dropTable("chats");
    }
}
