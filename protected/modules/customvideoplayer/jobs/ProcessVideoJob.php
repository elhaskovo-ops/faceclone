<?php
namespace humhub\modules\customvideoplayer\jobs;

use Yii;
use humhub\modules\queue\ActiveJob;
use humhub\modules\customvideoplayer\models\Video;
use humhub\modules\customvideoplayer\components\VideoProcessor;

class ProcessVideoJob extends ActiveJob
{
    public $videoId;
    public $fileId;

    public function run()
    {
        $video = Video::findOne($this->videoId);
        if (!$video) {
            return;
        }

        $file = $video->file;
        if (!$file) {
            return;
        }

        $processor = new VideoProcessor();
        $processor->processVideo($video, $file);
    }
}
