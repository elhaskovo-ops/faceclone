(function($) {
    'use strict';
    
    var CustomVideoPlayer = {
        init: function() {
            this.setupSubtitlesManagement();
            this.setupResponsiveBehavior();
        },
        
        setupSubtitlesManagement: function() {
            // Автоматично управление на субтитри
            $('video.video-js').each(function() {
                var player = videojs(this);
                
                // Събитие при промяна на субтитри
                player.on('loadstart', function() {
                    var tracks = player.textTracks();
                    var hasSubtitles = tracks.length > 0;
                    
                    // Показване/скриване на контролите за субтитри
                    var controls = $(this.el()).siblings('.video-subtitles-controls');
                    controls.toggle(hasSubtitles);
                });
            });
        },
        
        setupResponsiveBehavior: function() {
            // Направете видеото responsive
            $('.custom-video-player-container').each(function() {
                var container = $(this);
                var video = container.find('video');
                
                function adjustVideoSize() {
                    var containerWidth = container.width();
                    var aspectRatio = 16/9; // Стандартно съотношение
                    
                    if (containerWidth < 768) {
                        video.height(containerWidth / aspectRatio);
                    }
                }
                
                $(window).on('resize', adjustVideoSize);
                adjustVideoSize();
            });
        },
        
        uploadSubtitle: function(file, language, callback) {
            var formData = new FormData();
            formData.append('subtitleFile', file);
            formData.append('language', language);
            
            $.ajax({
                url: '/customvideoplayer/custom-video-player/upload-subtitle',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        callback(null, response);
                    } else {
                        callback(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    callback('Грешка при качване: ' + error);
                }
            });
        }
    };
    
    // Инициализация при зареждане на документа
    $(document).ready(function() {
        CustomVideoPlayer.init();
    });
    
    // Глобален достъп
    window.CustomVideoPlayer = CustomVideoPlayer;
    
})(jQuery);
