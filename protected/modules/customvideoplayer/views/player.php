<?php

use humhub\modules\customvideoplayer\assets\CustomVideoPlayerAsset;

CustomVideoPlayerAsset::register($this);
?>

<div class="custom-video-player-container">
    <video 
        id="custom-video-player-<?= uniqid() ?>" 
        class="video-js vjs-default-skin vjs-big-play-centered" 
        controls 
        preload="auto" 
        width="100%" 
        height="400"
        data-setup='{}'
    >
        <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
        
        <!-- Субтитри -->
        <?php foreach ($subtitles as $index => $track): ?>
        <track kind="captions" 
               src="<?= htmlspecialchars($track['src']) ?>" 
               srclang="<?= htmlspecialchars($track['srclang']) ?>" 
               label="<?= htmlspecialchars($track['label']) ?>"
               <?= $track['default'] ? 'default' : '' ?>>
        <?php endforeach; ?>
        
        <p class="vjs-no-js">
            За да гледате това видео, моля активирайте JavaScript и обмислете надграждане до браузър, 
            който <a href="https://videojs.com/html5-video-support/" target="_blank">поддържа HTML5 видео</a>.
        </p>
    </video>
    
    <!-- Интерфейс за управление на субтитри -->
    <div class="video-subtitles-controls" style="margin-top: 10px; display: <?= !empty($subtitles) ? 'block' : 'none' ?>;">
        <label for="subtitle-selector">Субтитри:</label>
        <select id="subtitle-selector" class="form-control">
            <option value="off">Изключени</option>
            <?php foreach ($subtitles as $index => $track): ?>
            <option value="<?= $index ?>" <?= $track['default'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($track['label']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var videoId = 'custom-video-player-<?= uniqid() ?>';
    var player = videojs(videoId);
    
    // Инициализация на субтитри
    var subtitleSelector = document.getElementById('subtitle-selector');
    if (subtitleSelector) {
        subtitleSelector.addEventListener('change', function() {
            var tracks = player.textTracks();
            for (var i = 0; i < tracks.length; i++) {
                tracks[i].mode = 'disabled';
            }
            
            if (this.value !== 'off') {
                var selectedTrack = tracks[this.value];
                if (selectedTrack) {
                    selectedTrack.mode = 'showing';
                }
            }
        });
    }
    
    // Автоматично активиране на default субтитри
    setTimeout(function() {
        var tracks = player.textTracks();
        for (var i = 0; i < tracks.length; i++) {
            if (tracks[i].default) {
                tracks[i].mode = 'showing';
            }
        }
    }, 1000);
});
</script>
