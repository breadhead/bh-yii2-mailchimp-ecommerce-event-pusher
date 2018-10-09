<?php

namespace breadhead\mailchimp\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the models class for table "mailchimp_event".
 *
 * @property integer $id
 * @property string $entity_id
 * @property string $entity_type
 * @property string $data
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string event_type
 */
class MailchimpEventModel extends \yii\db\ActiveRecord
{

    const NEW = 10;
    const DONE = 0;

    public static function tableName()
    {
        return 'mailchimp_event';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_id' => 'Entity ID',
            'entity_type' => 'Entity Type',
            'data' => 'Data',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
