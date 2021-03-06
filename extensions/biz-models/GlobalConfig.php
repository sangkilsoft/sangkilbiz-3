<?php

namespace biz\models;

use Yii;

/**
 * This is the model class for table "global_config".
 *
 * @property string $config_group
 * @property string $config_name
 * @property string $config_value
 * @property string $description
 * @property string $create_date
 * @property integer $create_by
 * @property string $update_date
 * @property integer $update_by
 */
class GlobalConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'global_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['config_group', 'config_name', 'config_value'], 'required'],
            [['config_value'], 'string'],
            [['config_group'], 'string', 'max' => 16],
            [['config_name'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'config_group' => 'Config Group',
            'config_name' => 'Config Name',
            'config_value' => 'Config Value',
            'description' => 'Description',
            'create_date' => 'Create Date',
            'create_by' => 'Create By',
            'update_date' => 'Update Date',
            'update_by' => 'Update By',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'biz\behaviors\AutoTimestamp',
            'biz\behaviors\AutoUser',
        ];
    }
}