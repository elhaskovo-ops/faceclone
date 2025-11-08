<?php
namespace humhub\modules\customvideoplayer;

use Yii;
use yii\helpers\Url;

class Module extends \humhub\components\Module
{
    public $resourcesPath = 'resources';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    /**
     * Register translations for this module
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['customvideoplayer*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@humhub/modules/customvideoplayer/messages',
            'fileMap' => [
                'customvideoplayer' => 'module.php',
            ],
        ];
    }

    public function getConfigUrl()
    {
        return Url::to(['/customvideoplayer/admin/config']);
    }

    public function enable()
    {
        parent::enable();

        try {
            $migration = new \humhub\modules\customvideoplayer\migrations\M20000101000001_install();
            $migration->safeUp();
        } catch (\Exception $e) {
            Yii::error('Could not enable customvideoplayer module: ' . $e->getMessage(), 'customvideoplayer');
        }
    }

    public function disable()
    {
        parent::disable();
    }
}
