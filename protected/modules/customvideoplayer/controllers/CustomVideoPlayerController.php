<?php

namespace humhub\modules\customvideoplayer\controllers;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;

class CustomVideoPlayerController extends Controller
{
    public function actionGetPlayer()
    {
        $url = Yii::$app->request->get('url');
        $subtitles = Yii::$app->request->get('subtitles', []);
        
        // Валидиране на видео URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new BadRequestHttpException('Невалиден видео URL');
        }
        
        // Валидиране на субтитри
        $validatedSubtitles = [];
        if (!empty($subtitles) && is_array($subtitles)) {
            foreach ($subtitles as $sub) {
                if (isset($sub['src']) && filter_var($sub['src'], FILTER_VALIDATE_URL)) {
                    $validatedSubtitles[] = [
                        'src' => $sub['src'],
                        'srclang' => $sub['srclang'] ?? 'en',
                        'label' => $sub['label'] ?? 'Subtitles',
                        'default' => $sub['default'] ?? false
                    ];
                }
            }
        }
        
        return $this->renderPartial('player', [
            'videoUrl' => $url,
            'subtitles' => $validatedSubtitles
        ]);
    }
    
    public function actionUploadSubtitle()
    {
        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName('subtitleFile');
            $language = Yii::$app->request->post('language', 'en');
            
            if ($file && $file->extension === 'vtt') {
                $filename = Yii::$app->security->generateRandomString(10) . '.vtt';
                $filepath = Yii::getAlias('@webroot/uploads/subtitles/') . $filename;
                
                if (!is_dir(dirname($filepath))) {
                    FileHelper::createDirectory(dirname($filepath));
                }
                
                if ($file->saveAs($filepath)) {
                    return $this->asJson([
                        'success' => true,
                        'url' => Yii::getAlias('@web/uploads/subtitles/') . $filename,
                        'language' => $language
                    ]);
                }
            }
        }
        
        return $this->asJson(['success' => false, 'error' => 'Грешка при качване']);
    }
}
