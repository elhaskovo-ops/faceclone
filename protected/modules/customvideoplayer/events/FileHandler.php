<?php
namespace humhub\modules\customvideoplayer\events;

use Yii;
use humhub\modules\file\models\File;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\customvideoplayer\models\Video;
use humhub\modules\customvideoplayer\assets\VideoPlayerAssets;

class FileHandler extends \yii\base\Component
{
    /**
     * Handle file insertion - process video files
     */
    public static function onFileInsert($event)
    {
        $file = $event->sender;
        
        if (strpos($file->mime_type, 'video/') === 0) {
            Yii::info('Processing video file: ' . $file->id, 'customvideoplayer');
            self::processVideoFile($file);
        }
    }

    /**
     * Replace default video preview with our player
     */
    public static function onFilePreviewInit($event)
{
    // Пробвайте да достъпите файла чрез 'file' атрибут, ако съществува
    $preview = $event->sender;
    $file = isset($preview->file) ? $preview->file : null;

    // Ако това не работи, опитайте да получите файла от контекста на виджета
    if (!$file && method_exists($preview, 'getFile')) {
        $file = $preview->getFile();
    }

    // Проверете дали файлът е валиден и видео
    if ($file && strpos($file->mime_type, 'video/') === 0) {
        VideoPlayerAssets::register(Yii::$app->view);
        $event->result = Yii::$app->controller->renderPartial(
            '@customvideoplayer/views/file/_videoPreview.php',
            ['file' => $file] // Подайте файла на view-то
        );
    }
}

    /**
     * Process video file and create variants
     */
    private static function processVideoFile($file)
    {
        // Check if video already exists
        $existingVideo = Video::find()->where(['file_id' => $file->id])->one();
        if ($existingVideo) {
            return;
        }

        $video = new Video();
        $video->file_id = $file->id;
        $video->original_filename = $file->file_name;
        $video->file_size = $file->size;
        $video->created_by = Yii::$app->user->id;
        $video->status = Video::STATUS_PENDING;
        
        if ($video->save()) {
            Yii::info('Created video record: ' . $video->id, 'customvideoplayer');
            
            // Start background processing
            if (Yii::$app->has('queue')) {
                Yii::$app->queue->push(new \humhub\modules\customvideoplayer\jobs\ProcessVideoJob([
                    'videoId' => $video->id,
                    'fileId' => $file->id
                ]));
            } else {
                // Fallback: process immediately
                $processor = new \humhub\modules\customvideoplayer\components\VideoProcessor();
                $processor->processVideo($video, $file);
            }
        }
    }
}
