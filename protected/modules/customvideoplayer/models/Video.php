<?php
namespace humhub\modules\customvideoplayer\models;

use Yii;
use humhub\components\ActiveRecord;

class Video extends ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_ERROR = 3;

    public static function tableName()
    {
        return 'customvideoplayer_video';
    }

    public function rules()
    {
        return [
            [['file_id', 'original_filename'], 'required'],
            [['file_id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['duration', 'file_size'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['original_filename'], 'string', 'max' => 255],
        ];
    }

    public function getFile()
    {
        return $this->hasOne(\humhub\modules\file\models\File::class, ['id' => 'file_id']);
    }

    public function getVariants()
    {
        return $this->hasMany(VideoVariant::class, ['video_id' => 'id']);
    }

    public function getThumbnailUrl()
    {
        $thumbnailPath = '/uploads/customvideoplayer/' . $this->id . '/thumbnail.jpg';
        if (file_exists(Yii::getAlias('@webroot') . $thumbnailPath)) {
            return $thumbnailPath;
        }
        return null;
    }
}
