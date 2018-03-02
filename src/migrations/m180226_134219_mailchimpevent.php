<?php

use yii\db\Migration;

/**
 * Class m180226_134219_mailchimpevent
 */
class m180226_134219_mailchimpevent extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%mailchimp_event}}', [
            'id' => $this->primaryKey(),
            'entity_id' => $this->string(),
            'entity_type' => $this->string(),
            'event_type' => $this->string(),
            'data' => $this->text(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
       $this->dropTable('{{%mailchimp_event}}');
    }
}
