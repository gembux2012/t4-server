<?php

namespace App\Modules\Gallery\Migrations;

use T4\Orm\Migration;

class m_1547707104_CreateFoto
    extends Migration
{

    public function up()
    {
        $this->createTable('photos', [
            'title' => ['type' => 'text'],
            'image' => ['type' => 'string'],
            'published' => ['type' => 'datetime'],
            '__album_id' => ['type' => 'link'],
        ], [
            'album' => ['columns' => ['__album_id']]
        ]);
    }

    public function down()
    {
        $this->dropTable('photos');
    }
}