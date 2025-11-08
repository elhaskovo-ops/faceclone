<?php
namespace humhub\modules\customvideoplayer\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\file\models\File;
use humhub\modules\customvideoplayer\models\Video;
use yii\web\NotFoundHttpException;

class VideoController extends Controller
{
    /**
     * Get video data for player
     */
    public function actionGetData($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $file = File::findOne($id);
            if (!$file) {
                throw new NotFoundHttpException('File not found');
            }
            
            // Find video record
            $video = Video::find()->where(['file_id' => $file->id])->one();
            
            if (!$video) {
                // Create video record if doesn't exist
                $video = new Video();
                $video->file_id = $file->id;
                $video->original_filename = $file->file_name;
                $video->file_size = $file->size;
                $video->created_by = Yii::$app->user->id;
                $video->status = Video::STATUS_COMPLETED;
                $video->save();
            }
            
            // Get video variants
            $variants = [];
            if ($video->status === Video::STATUS_COMPLETED) {
                foreach ($video->variants as $variant) {
                    $variants[] = [
                        'quality' => $variant->quality,
                        'label' => $variant->getQualityLabel(),
                        'url' => $variant->getUrl(),
                        'width' => $variant->width,
                        'height' => $variant->height,
                        'bitrate' => $variant->bitrate
                    ];
                }
            } else {
                // Fallback to original file
                $variants[] = [
                    'quality' => 360,
                    'label' => Yii::t('CustomVideoPlayerModule.module', '360p (Standard)'),
                    'url' => $file->getUrl(),
                    'width' => 640,
                    'height' => 360,
                    'bitrate' => '800k'
                ];
            }
            
            // Subtitles data
            $subtitles = $this->getSubtitles($video);
            
            return [
                'success' => true,
                'data' => [
                    'id' => $video->id,
                    'title' => $file->file_name,
                    'status' => $video->status,
                    'variants' => $variants,
                    'subtitles' => $subtitles,
                    'thumbnail' => $video->getThumbnailUrl(),
                    'duration' => $video->duration
                ]
            ];
            
        } catch (\Exception $e) {
            Yii::error('Error getting video data: ' . $e->getMessage(), 'customvideoplayer');
            
            return [
                'success' => false,
                'error' => Yii::t('CustomVideoPlayerModule.module', 'Failed to load video data'),
                'fallback' => [
                    'url' => File::findOne($id)->getUrl(),
                    'title' => File::findOne($id)->file_name
                ]
            ];
        }
    }
    
    /**
     * Get available subtitles for video
     */
    private function getSubtitles($video)
    {
        // In a real implementation, this would fetch from database or file system
        return [
            [
                'lang' => 'bg',
                'label' => Yii::t('CustomVideoPlayerModule.module', 'Bulgarian'),
                'url' => '/uploads/customvideoplayer/' . $video->id . '/subtitles/bg.vtt'
            ],
            [
                'lang' => 'en', 
                'label' => Yii::t('CustomVideoPlayerModule.module', 'English'),
                'url' => '/uploads/customvideoplayer/' . $video->id . '/subtitles/en.vtt'
            ]
        ];
    }
    
    /**
     * Stream video variant
     */
    public function actionStream($id, $quality = null)
    {
        $video = Video::findOne($id);
        if (!$video) {
            throw new NotFoundHttpException('Video not found');
        }
        
        if ($quality && $video->status === Video::STATUS_COMPLETED) {
            $variant = $video->getVariants()->where(['quality' => $quality])->one();
            if ($variant) {
                return $this->streamFile($variant->getFullPath(), $video->original_filename);
            }
        }
        
        // Fallback to original file
        $file = $video->file;
        return $this->streamFile($file->store->get($file), $file->file_name);
    }
    
    /**
     * Helper method to stream files
     */
    private function streamFile($filePath, $filename)
    {
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('File not found');
        }
        
        $fileSize = filesize($filePath);
        $modified = filemtime($filePath);
        
        // Set headers
        header('Content-Type: video/mp4');
        header('Content-Length: ' . $fileSize);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT');
        header('Accept-Ranges: bytes');
        
        // Handle range requests for seeking
        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->handleRangeRequest($filePath, $fileSize);
        } else {
            readfile($filePath);
        }
        
        exit;
    }
    
    /**
     * Handle HTTP range requests for video seeking
     */
    private function handleRangeRequest($filePath, $fileSize)
    {
        $range = $_SERVER['HTTP_RANGE'];
        $range = str_replace('bytes=', '', $range);
        list($start, $end) = explode('-', $range);
        
        $start = intval($start);
        $end = $end ? intval($end) : $fileSize - 1;
        $length = $end - $start + 1;
        
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$fileSize");
        header('Content-Length: ' . $length);
        
        $fp = fopen($filePath, 'rb');
        fseek($fp, $start);
        
        $buffer = 1024 * 8;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
    }
}
