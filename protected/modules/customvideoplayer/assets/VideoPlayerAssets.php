<?php
namespace humhub\modules\customvideoplayer\assets;

use yii\web\AssetBundle;

class VideoPlayerAssets extends AssetBundle
{
    public $sourcePath = '@customvideoplayer/resources';

    public $css = [
        'css/adaptive-video-player.css',
    ];

    public $js = [
        'js/adaptive-video-player.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
