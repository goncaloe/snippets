<?php

use app\components\Migration;

class m170824_102030_install extends Migration
{

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('tags', [
            'id' => $this->string(32)->notNull(),
            'position' => $this->smallInteger(8)->unsigned()->defaultValue(0),
            'PRIMARY KEY (id)',
        ]);
        $this->createIndex('index_2', 'tags', ['position'], false);

        $this->createTable('snippets', [
            'id' => $this->string(32)->notNull(),
            'name' => $this->string(128),
            'framework' => $this->string(24),
            'created_at' => $this->smallInteger(11)->unsigned()->defaultValue(0),
            'PRIMARY KEY (id)',
        ]);
        $this->createIndex('index_2', 'snippets', ['framework'], false);
        $this->createIndex('index_3', 'snippets', ['created_at'], false);

        $this->createTable('snippet_tags', [
            'snippet_id' => $this->string(32)->notNull(),
            'tag_id' => $this->string(32)->notNull(),
            'PRIMARY KEY (snippet_id, tag_id)',
        ]);
        $this->createIndex('index_2', 'snippet_tags', ['tag_id'], false);

    }

    public function down()
    {
        echo "m170824_102030_install cannot be reverted.\n";

        return false;
    }
}
