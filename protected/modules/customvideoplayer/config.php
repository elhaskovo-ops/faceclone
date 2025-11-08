<?php
use humhub\modules\file\models\File;

return [
    'id' => 'customvideoplayer',
    'class' => 'humhub\modules\customvideoplayer\Module',
    'namespace' => 'humhub\modules\customvideoplayer',
    'events' => [
        [
            'class' => File::class,
            'event' => File::EVENT_AFTER_INSERT,
            'callback' => ['humhub\modules\customvideoplayer\events\FileHandler', 'onFileInsert']
        ],
        [
            'class' => \humhub\modules\file\widgets\FilePreview::class,
            'event' => \humhub\modules\file\widgets\FilePreview::EVENT_INIT,
            'callback' => ['humhub\modules\customvideoplayer\events\FileHandler', 'onFilePreviewInit']
        ]
    ],
];
