<?php
namespace humhub\modules\customvideoplayer\models;

use Yii;
use humhub\components\ActiveRecord;

class VideoVariant extends ActiveRecord
{
    public static function tableName()
    {
        return 'customvideoplayer_video_variant';
    }

    public function rules()
    {
        return [
            [['video_id', 'quality', 'file_path'], 'required'],
            [['video_id', 'width', 'height'], 'integer'],
            [['file_size', 'duration'], 'number'],
            [['quality', 'file_path', 'mime_type', 'bitrate'], 'string', 'max' => 255],
        ];
    }

    public function getVideo()
    {
        return $this->hasOne(Video::class, ['id' => 'video_id']);
    }

    public function getQualityLabel()
    {
        $labels = [
            144 => '144p',
            240 => '240p', 
            360 => '360p',
            480 => '480p',
            720 => '720p HD',
            1080 => '1080p Full HD'
        ];
        return $labels[$this->quality] ?? $this->quality . 'p';
    }

    public function getUrl()
    {
        return Yii::getAlias('@web') . $this->file_path;
    }
}
