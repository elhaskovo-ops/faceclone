<?php
use humhub\modules\customvideoplayer\assets\VideoPlayerAssets;

VideoPlayerAssets::register($this);

$file = $this->context->file;
$videoData = Yii::$app->runAction('/customvideoplayer/video/get-data', ['id' => $file->id]);
?>

<div class="custom-video-preview" 
     data-file-id="<?= $file->id ?>" 
     data-file-type="video"
     data-video-url="<?= $file->getUrl() ?>"
     data-preview-url="<?= $this->context->getPreviewImageUrl($file) ?>">
     
    <div class="video-preview-container">
        <?php if (isset($videoData['error'])): ?>
            <div class="video-error">
                <div class="error-icon">⚠️</div>
                <p><?= Yii::t('CustomVideoPlayerModule.module', 'Video loading error') ?></p>
                <button class="btn btn-primary retry-load"><?= Yii::t('CustomVideoPlayerModule.module', 'Retry') ?></button>
            </div>
        <?php else: ?>
            <!-- Video player will be initialized by JavaScript -->
            <div class="video-loading-placeholder">
                <div class="loading-spinner"></div>
                <p><?= Yii::t('CustomVideoPlayerModule.module', 'Loading video player...') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoContainer = document.querySelector('[data-file-id="<?= $file->id ?>"]');
    if (videoContainer) {
        // Initialize adaptive video player
        new AdaptiveVideoPlayer(videoContainer, {
            fileId: <?= $file->id ?>,
            videoUrl: '<?= $file->getUrl() ?>',
            previewImage: '<?= $this->context->getPreviewImageUrl($file) ?>',
            language: '<?= Yii::$app->language ?>'
        });
    }
});
</script>

<style>
.custom-video-preview {
    margin: 10px 0;
    border-radius: 12px;
    overflow: hidden;
}

.video-preview-container {
    position: relative;
    min-height: 200px;
    background: #000;
    border-radius: 12px;
}

.video-error {
    text-align: center;
    padding: 40px 20px;
    color: #fff;
}

.video-error .error-icon {
    font-size: 3em;
    margin-bottom: 15px;
}

.video-loading-placeholder {
    text-align: center;
    padding: 60px 20px;
    color: #fff;
}

.video-loading-placeholder .loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top: 3px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
