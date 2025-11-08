<?php

namespace humhub\modules\customvideoplayer\assets;

use yii\web\AssetBundle;

class CustomVideoPlayerAsset extends AssetBundle
{
    public $sourcePath = '@customvideoplayer/resources';
    
    public $css = [
        'https://vjs.zencdn.net/7.20.3/video-js.css',
        'css/custom-videoplayer.css'
    ];
    
    public $js = [
        'https://vjs.zencdn.net/7.20.3/video.min.js',
        'js/custom-videoplayer.js'
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
    ];
}
