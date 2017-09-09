<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property string $id
 * @property string $color
 * @property int $position
 */
class Tag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['position'], 'integer'],
            [['id', 'color'], 'string', 'max' => 32],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'color' => 'Color',
            'position' => 'Position',
        ];
    }
}
