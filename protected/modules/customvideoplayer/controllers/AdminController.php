<?php
namespace humhub\modules\customvideoplayer\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\customvideoplayer\models\AdminSettings;

class AdminController extends Controller
{
    public function actionConfig()
    {
        $model = new AdminSettings();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            return $this->redirect(['config']);
        }
        
        return $this->render('config', [
            'model' => $model
        ]);
    }
    
    public function actionIndex()
    {
        return $this->redirect(['config']);
    }
    
    public function actionTestFfmpeg()
    {
        $model = new AdminSettings();
        
        $output = [];
        $returnCode = 0;
        
        // Test FFmpeg
        $ffmpegCommand = $model->ffmpegPath ?: 'ffmpeg';
        exec($ffmpegCommand . ' -version 2>&1', $ffmpegOutput, $ffmpegReturnCode);
        $ffmpegWorking = ($ffmpegReturnCode === 0);
        
        // Test FFprobe  
        $ffprobeCommand = $model->ffprobePath ?: 'ffprobe';
        exec($ffprobeCommand . ' -version 2>&1', $ffprobeOutput, $ffprobeReturnCode);
        $ffprobeWorking = ($ffprobeReturnCode === 0);
        
        // Combine outputs for display
        $output = array_merge($ffmpegOutput, $ffprobeOutput);
        
        return $this->renderPartial('test_ffmpeg', [
            'ffmpegWorking' => $ffmpegWorking,
            'ffprobeWorking' => $ffprobeWorking,
            'output' => $output
        ]);
    }
}
