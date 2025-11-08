<?php
namespace humhub\modules\customvideoplayer\models;

use Yii;

class AdminSettings extends \yii\base\Model
{
    public $ffmpegPath;
    public $ffprobePath;
    public $enableAdaptiveQuality;
    public $enableSubtitles;
    public $enablePictureInPicture;
    public $maxVideoSize;
    public $allowedFormats;

    public function init()
    {
        parent::init();
        $module = Yii::$app->getModule('customvideoplayer');
        $this->ffmpegPath = $module->settings->get('ffmpegPath', 'ffmpeg');
        $this->ffprobePath = $module->settings->get('ffprobePath', 'ffprobe');
        $this->enableAdaptiveQuality = $module->settings->get('enableAdaptiveQuality', 1);
        $this->enableSubtitles = $module->settings->get('enableSubtitles', 1);
        $this->enablePictureInPicture = $module->settings->get('enablePictureInPicture', 1);
        $this->maxVideoSize = $module->settings->get('maxVideoSize', 500);
        $this->allowedFormats = $module->settings->get('allowedFormats', 'mp4,avi,mov,mkv,webm');
    }

    public function rules()
    {
        return [
            [['ffmpegPath', 'ffprobePath', 'allowedFormats'], 'string'],
            [['enableAdaptiveQuality', 'enableSubtitles', 'enablePictureInPicture'], 'boolean'],
            [['maxVideoSize'], 'integer', 'min' => 1, 'max' => 2048],
            [['ffmpegPath', 'ffprobePath', 'maxVideoSize', 'allowedFormats'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ffmpegPath' => Yii::t('customvideoplayer', 'FFmpeg Path'),
            'ffprobePath' => Yii::t('customvideoplayer', 'FFprobe Path'),
            'enableAdaptiveQuality' => Yii::t('customvideoplayer', 'Enable Adaptive Quality'),
            'enableSubtitles' => Yii::t('customvideoplayer', 'Enable Subtitles'),
            'enablePictureInPicture' => Yii::t('customvideoplayer', 'Enable Picture-in-Picture'),
            'maxVideoSize' => Yii::t('customvideoplayer', 'Maximum Video Size (MB)'),
            'allowedFormats' => Yii::t('customvideoplayer', 'Allowed Video Formats'),
        ];
    }

    public function attributeHints()
    {
        return [
            'ffmpegPath' => Yii::t('customvideoplayer', 'Path to FFmpeg executable (e.g., /usr/bin/ffmpeg or ffmpeg if in PATH)'),
            'ffprobePath' => Yii::t('customvideoplayer', 'Path to FFprobe executable (e.g., /usr/bin/ffprobe or ffprobe if in PATH)'),
            'maxVideoSize' => Yii::t('customvideoplayer', 'Maximum file size for video uploads in megabytes'),
            'allowedFormats' => Yii::t('customvideoplayer', 'Comma-separated list of allowed video formats'),
        ];
    }

    public function save()
    {
        $module = Yii::$app->getModule('customvideoplayer');
        $module->settings->set('ffmpegPath', $this->ffmpegPath);
        $module->settings->set('ffprobePath', $this->ffprobePath);
        $module->settings->set('enableAdaptiveQuality', $this->enableAdaptiveQuality);
        $module->settings->set('enableSubtitles', $this->enableSubtitles);
        $module->settings->set('enablePictureInPicture', $this->enablePictureInPicture);
        $module->settings->set('maxVideoSize', $this->maxVideoSize);
        $module->settings->set('allowedFormats', $this->allowedFormats);
        return true;
    }
}
