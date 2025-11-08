<?php
namespace humhub\modules\customvideoplayer\components;

use Yii;
use yii\base\Component;
use humhub\modules\customvideoplayer\models\Video;
use humhub\modules\customvideoplayer\models\VideoVariant;

class VideoProcessor extends Component
{
    public $qualities = [
        144 => ['width' => 256, 'height' => 144, 'bitrate' => '200k'],
        240 => ['width' => 426, 'height' => 240, 'bitrate' => '400k'],
        360 => ['width' => 640, 'height' => 360, 'bitrate' => '800k'],
        480 => ['width' => 854, 'height' => 480, 'bitrate' => '1200k'],
        720 => ['width' => 1280, 'height' => 720, 'bitrate' => '2500k'],
        1080 => ['width' => 1920, 'height' => 1080, 'bitrate' => '4500k'],
    ];

    public function processVideo($video, $file)
    {
        try {
            $video->status = Video::STATUS_PROCESSING;
            $video->save();

            // Създаване на директория
            $videoDir = Yii::getAlias('@webroot/uploads/customvideoplayer/' . $video->id);
            if (!file_exists($videoDir)) {
                mkdir($videoDir, 0777, true);
            }

            // Копиране на оригиналния файл
            $originalPath = $videoDir . '/original.' . pathinfo($file->file_name, PATHINFO_EXTENSION);
            copy($file->store->get($file->store->get($file)), $originalPath);

            // Създаване на thumbnail
            $this->createThumbnail($originalPath, $videoDir . '/thumbnail.jpg');

            // Създаване на варианти
            foreach ($this->qualities as $quality => $config) {
                $this->createVideoVariant($video, $originalPath, $quality, $config);
            }

            $video->status = Video::STATUS_COMPLETED;
            $video->save();

        } catch (\Exception $e) {
            $video->status = Video::STATUS_ERROR;
            $video->save();
            Yii::error('Video processing failed: ' . $e->getMessage(), 'customvideoplayer');
        }
    }

    private function createVideoVariant($video, $originalPath, $quality, $config)
    {
        $outputFile = Yii::getAlias('@webroot/uploads/customvideoplayer/' . $video->id . '/variant_' . $quality . 'p.mp4');
        
        $cmd = "ffmpeg -i " . escapeshellarg($originalPath) . 
               " -c:v libx264 -preset medium -crf 23 -maxrate " . $config['bitrate'] .
               " -bufsize " . ($config['bitrate'] * 2) . 
               " -vf scale=" . $config['width'] . ":" . $config['height'] .
               " -c:a aac -b:a 128k -movflags +faststart -y " . 
               escapeshellarg($outputFile) . " 2>&1";

        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputFile)) {
            $variant = new VideoVariant();
            $variant->video_id = $video->id;
            $variant->quality = $quality;
            $variant->width = $config['width'];
            $variant->height = $config['height'];
            $variant->bitrate = $config['bitrate'];
            $variant->file_path = '/uploads/customvideoplayer/' . $video->id . '/variant_' . $quality . 'p.mp4';
            $variant->file_size = filesize($outputFile);
            $variant->mime_type = 'video/mp4';
            $variant->save();
        }
    }

    private function createThumbnail($videoPath, $thumbnailPath)
    {
        $cmd = "ffmpeg -i " . escapeshellarg($videoPath) . 
               " -ss 00:00:01 -vframes 1 -q:v 2 -y " . 
               escapeshellarg($thumbnailPath) . " 2>&1";
        exec($cmd);
    }
}
