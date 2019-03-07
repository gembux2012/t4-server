<?php

namespace App\Modules\Gallery\Migrations;

use T4\Orm\Migration;

class m_1547702600_CreateAlbum
    extends Migration
{

    public function up()
    {
        $this->createTable('albums', [
            'title' => ['type' => 'string'],
            'published' => ['type' => 'datetime'],
            'url' => ['type' => 'string'],
            '__cover_id' => ['type' => 'bigint unsigned','default' => 'NULL'],
        ], [
            'cover' => ['columns' => ['__cover_id'],]
        ], [
            'tree'
        ]);

        $this->db->execute('ALTER TABLE `albums` CHANGE `__prt` `__prt` BIGINT NULL DEFAULT NULL;');
    }

    public function down()
    {
        $this->dropTable('albums');
    }
}