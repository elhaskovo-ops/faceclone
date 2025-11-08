/**
 * Adaptive Video Player for HumHub
 * Заменя стандартния видео плеър с модерен адаптивен плеър
 */
class AdaptiveVideoPlayer {
    constructor(container, options = {}) {
        this.container = container;
        this.fileId = container.getAttribute('data-file-id');
        this.videoUrl = container.getAttribute('data-video-url');
        this.previewImage = container.getAttribute('data-preview-url');
        
        this.videoElement = null;
        this.qualities = [];
        this.subtitles = [];
        this.currentQuality = 'auto';
        this.currentSubtitle = 'off';
        this.isAutoQuality = true;
        this.isPiP = false;
        this.isFullscreen = false;
        this.playbackRate = 1.0;
        this.isMuted = false;
        this.volume = 1.0;
        
        // Настройки
        this.settings = {
            checkInterval: 3000,
            minBuffer: 10,
            speedThresholds: {
                '1080p': 4000,
                '720p': 2500,
                '480p': 1200,
                '360p': 800,
                '240p': 400,
                '144p': 200
            },
            preload: true,
            language: 'bg',
            ...options
        };

        this.translations = this.getTranslations();
        this.init();
    }

    getTranslations() {
        return {
            bg: {
                quality: 'Качество',
                auto: 'Автоматично',
                subtitles: 'Субтитри',
                pip: 'Картина в картина',
                fullscreen: 'Цял екран',
                play: 'Пусни',
                pause: 'Пауза',
                mute: 'Без звук',
                unmute: 'Със звук',
                settings: 'Настройки',
                playbackSpeed: 'Скорост',
                normal: 'Нормална',
                loading: 'Видеото се зарежда...',
                failed: 'Зареждането неуспешно',
                checkConnection: 'Проверете интернет връзката',
                retry: 'Опитайте отново',
                noSubtitles: 'Няма субтитри',
                off: 'Изключено',
                selectQuality: 'Изберете качество',
                selectSubtitle: 'Изберете субтитри',
                shortcuts: 'Клавишни комбинации',
                space: 'Пусни/Спри: Space',
                fullscreenKey: 'Цял екран: F',
                pipKey: 'Картина в картина: P',
                muteKey: 'Без звук: M',
                seek: 'Търсене: ← →',
                volume: 'Сила на звука: ↑ ↓',
                speed: 'Скорост: 0-9'
            },
            en: {
                quality: 'Quality',
                auto: 'Auto',
                subtitles: 'Subtitles',
                pip: 'Picture in Picture',
                fullscreen: 'Fullscreen',
                play: 'Play',
                pause: 'Pause',
                mute: 'Mute',
                unmute: 'Unmute',
                settings: 'Settings',
                playbackSpeed: 'Speed',
                normal: 'Normal',
                loading: 'The video is loading...',
                failed: 'Loading failed',
                checkConnection: 'Check your network connection',
                retry: 'Retry',
                noSubtitles: 'No subtitles',
                off: 'Off',
                selectQuality: 'Select quality',
                selectSubtitle: 'Select subtitle',
                shortcuts: 'Keyboard Shortcuts',
                space: 'Play/Pause: Space',
                fullscreenKey: 'Fullscreen: F',
                pipKey: 'Picture-in-Picture: P',
                muteKey: 'Mute: M',
                seek: 'Seek: ← →',
                volume: 'Volume: ↑ ↓',
                speed: 'Speed: 0-9'
            }
        }[this.settings.language];
    }

    init() {
        this.createPlayer();
        this.loadVideoData();
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
        
        if (this.settings.preload) {
            this.preloadQualities();
        }
    }

    createPlayer() {
        const t = this.translations;
        
        this.container.innerHTML = `
            <div class="adaptive-video-player-wrapper">
                <video class="adaptive-video-player" 
                       poster="${this.previewImage || ''}"
                       preload="metadata"
                       crossorigin="anonymous">
                    ${t.loading}
                </video>
                
                <!-- Контролни елементи -->
                <div class="video-controls-container">
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-filled" style="width: 0%"></div>
                            <div class="progress-thumb" style="left: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="control-bar">
                        <div class="left-controls">
                            <button class="play-pause-btn" title="${t.play}">▶️</button>
                            <button class="volume-btn" title="${t.unmute}">🔊</button>
                            <div class="volume-slider-container">
                                <input type="range" class="volume-slider" min="0" max="1" step="0.1" value="1">
                            </div>
                            <div class="time-display">
                                <span class="current-time">0:00</span> / <span class="duration">0:00</span>
                            </div>
                        </div>
                        
                        <div class="center-controls">
                            <button class="playback-speed-btn" title="${t.playbackSpeed}">1x</button>
                        </div>
                        
                        <div class="right-controls">
                            <!-- Меню за качество -->
                            <div class="quality-selector">
                                <button class="quality-btn" title="${t.selectQuality}">
                                    <span class="current-quality">${t.auto}</span>
                                    <span class="arrow">▼</span>
                                </button>
                                <div class="quality-menu"></div>
                            </div>
                            
                            <!-- Меню за субтитри -->
                            <div class="subtitle-selector">
                                <button class="subtitle-btn" title="${t.selectSubtitle}">📝</button>
                                <div class="subtitle-menu"></div>
                            </div>
                            
                            <!-- Допълнителни контроли -->
                            <button class="pip-btn" title="${t.pip}">📌</button>
                            <button class="fullscreen-btn" title="${t.fullscreen}">⛶</button>
                            <button class="settings-btn" title="${t.settings}">⚙️</button>
                        </div>
                    </div>
                </div>
                
                <!-- Помощ за клавишни комбинации -->
                <div class="keyboard-shortcuts">
                    <div class="shortcut-item">${t.space}</div>
                    <div class="shortcut-item">${t.fullscreenKey}</div>
                    <div class="shortcut-item">${t.pipKey}</div>
                    <div class="shortcut-item">${t.muteKey}</div>
                    <div class="shortcut-item">${t.seek}</div>
                    <div class="shortcut-item">${t.volume}</div>
                    <div class="shortcut-item">${t.speed}</div>
                </div>
                
                <!-- Индикатор за зареждане -->
                <div class="video-loading-spinner">
                    <div class="spinner"></div>
                    <span>${t.loading}</span>
                </div>
                
                <!-- Съобщение за грешка -->
                <div class="video-error-message" style="display: none;">
                    <div class="error-icon">⚠️</div>
                    <div class="error-text">${t.failed}</div>
                    <button class="retry-btn">${t.retry}</button>
                </div>
            </div>
        `;

        this.videoElement = this.container.querySelector('.adaptive-video-player');
        this.setupQualityMenu();
        this.setupSubtitleMenu();
    }

    async loadVideoData() {
        try {
            // Зареждане на видео данни от сървъра
            const response = await fetch(`/customvideoplayer/video/get-data?id=${this.fileId}`);
            if (!response.ok) throw new Error('Network error');
            
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            
            this.qualities = data.variants || [];
            this.subtitles = data.subtitles || [];
            
            this.setupVideoSources();
            this.updateQualityMenu();
            this.updateSubtitleMenu();
            
        } catch (error) {
            console.error('Грешка при зареждане на видео:', error);
            this.setupFallbackVideo();
        }
    }

    setupVideoSources() {
        // Изчистване на старите източници
        const oldSources = this.videoElement.querySelectorAll('source');
        oldSources.forEach(source => source.remove());
        
        // Добавяне на новите източници за всяко качество
        this.qualities.forEach(quality => {
            const source = document.createElement('source');
            source.src = quality.url;
            source.type = 'video/mp4';
            source.setAttribute('data-quality', quality.quality);
            this.videoElement.appendChild(source);
        });

        // Добавяне на субтитри
        this.setupSubtitles();
        
        this.videoElement.load();
        this.setInitialQuality();
    }

    setupSubtitles() {
        // Изчистване на стари субтитри
        const oldTracks = this.videoElement.querySelectorAll('track');
        oldTracks.forEach(track => track.remove());
        
        // Добавяне на нови субтитри
        this.subtitles.forEach(subtitle => {
            const track = document.createElement('track');
            track.kind = 'subtitles';
            track.srclang = subtitle.lang;
            track.label = subtitle.label;
            track.src = subtitle.url;
            this.videoElement.appendChild(track);
        });
    }

    setupQualityMenu() {
        const t = this.translations;
        const qualityMenu = this.container.querySelector('.quality-menu');
        
        qualityMenu.innerHTML = `
            <div class="quality-option ${this.isAutoQuality ? 'active' : ''}" data-quality="auto">
                <span>🎯 ${t.auto}</span>
                <small>${t.auto}</small>
            </div>
            <div class="quality-divider"></div>
            ${this.qualities.map(quality => `
                <div class="quality-option ${this.currentQuality === quality.quality.toString() ? 'active' : ''}" 
                     data-quality="${quality.quality}">
                    <span>${quality.label}</span>
                    <small>${this.formatBitrate(quality.bitrate)}</small>
                </div>
            `).join('')}
        `;
    }

    setupSubtitleMenu() {
        const t = this.translations;
        const subtitleMenu = this.container.querySelector('.subtitle-menu');
        
        subtitleMenu.innerHTML = `
            <div class="subtitle-option ${this.currentSubtitle === 'off' ? 'active' : ''}" data-subtitle="off">
                <span>${t.off}</span>
            </div>
            <div class="subtitle-divider"></div>
            ${this.subtitles.map(subtitle => `
                <div class="subtitle-option ${this.currentSubtitle === subtitle.lang ? 'active' : ''}" 
                     data-subtitle="${subtitle.lang}">
                    <span>${subtitle.label}</span>
                </div>
            `).join('')}
        `;
    }

    updateQualityMenu() {
        this.setupQualityMenu();
    }

    updateSubtitleMenu() {
        this.setupSubtitleMenu();
    }

    setupEventListeners() {
        this.setupQualityEvents();
        this.setupSubtitleEvents();
        this.setupPlaybackEvents();
        this.setupControlEvents();
        this.setupVideoEvents();
    }

    setupQualityEvents() {
        const qualityBtn = this.container.querySelector('.quality-btn');
        const qualityMenu = this.container.querySelector('.quality-menu');

        qualityBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = qualityMenu.style.display === 'block';
            qualityMenu.style.display = isVisible ? 'none' : 'block';
            
            // Скриване на други менюта
            this.hideOtherMenus(qualityMenu);
        });

        qualityMenu.addEventListener('click', (e) => {
            const option = e.target.closest('.quality-option');
            if (option) {
                const quality = option.getAttribute('data-quality');
                this.switchToQuality(quality);
                qualityMenu.style.display = 'none';
            }
        });
    }

    setupSubtitleEvents() {
        const subtitleBtn = this.container.querySelector('.subtitle-btn');
        const subtitleMenu = this.container.querySelector('.subtitle-menu');

        subtitleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = subtitleMenu.style.display === 'block';
            subtitleMenu.style.display = isVisible ? 'none' : 'block';
            this.hideOtherMenus(subtitleMenu);
        });

        subtitleMenu.addEventListener('click', (e) => {
            const option = e.target.closest('.subtitle-option');
            if (option) {
                const subtitle = option.getAttribute('data-subtitle');
                this.switchToSubtitle(subtitle);
                subtitleMenu.style.display = 'none';
            }
        });
    }

    setupPlaybackEvents() {
        const playBtn = this.container.querySelector('.play-pause-btn');
        const volumeBtn = this.container.querySelector('.volume-btn');
        const volumeSlider = this.container.querySelector('.volume-slider');
        const speedBtn = this.container.querySelector('.playback-speed-btn');

        playBtn.addEventListener('click', () => this.togglePlay());
        volumeBtn.addEventListener('click', () => this.toggleMute());
        
        volumeSlider.addEventListener('input', (e) => {
            this.volume = parseFloat(e.target.value);
            this.videoElement.volume = this.volume;
            this.updateVolumeDisplay();
        });

        speedBtn.addEventListener('click', () => this.cyclePlaybackSpeed());
    }

    setupControlEvents() {
        const pipBtn = this.container.querySelector('.pip-btn');
        const fullscreenBtn = this.container.querySelector('.fullscreen-btn');
        const progressBar = this.container.querySelector('.progress-bar');
        const retryBtn = this.container.querySelector('.retry-btn');

        pipBtn.addEventListener('click', () => this.togglePictureInPicture());
        fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
        
        progressBar.addEventListener('click', (e) => {
            const rect = progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.seekTo(percent);
        });

        retryBtn?.addEventListener('click', () => this.retryLoading());
    }

    setupVideoEvents() {
        this.videoElement.addEventListener('timeupdate', () => this.updateProgress());
        this.videoElement.addEventListener('loadeddata', () => this.updateTimeDisplay());
        this.videoElement.addEventListener('canplay', () => this.hideLoading());
        this.videoElement.addEventListener('waiting', () => this.showLoading());
        this.videoElement.addEventListener('error', () => this.showError());
        this.videoElement.addEventListener('enterpictureinpicture', () => this.onEnterPiP());
        this.videoElement.addEventListener('leavepictureinpicture', () => this.onLeavePiP());
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (!this.isVideoFocused()) return;

            switch(e.key.toLowerCase()) {
                case ' ': e.preventDefault(); this.togglePlay(); break;
                case 'f': e.preventDefault(); this.toggleFullscreen(); break;
                case 'p': e.preventDefault(); this.togglePictureInPicture(); break;
                case 'm': e.preventDefault(); this.toggleMute(); break;
                case 'arrowleft': e.preventDefault(); this.seekRelative(-5); break;
                case 'arrowright': e.preventDefault(); this.seekRelative(5); break;
                case 'arrowup': e.preventDefault(); this.adjustVolume(0.1); break;
                case 'arrowdown': e.preventDefault(); this.adjustVolume(-0.1); break;
                case '0': case '1': case '2': case '3': case '4':
                case '5': case '6': case '7': case '8': case '9':
                    e.preventDefault(); this.seekToPercent(parseInt(e.key) / 10); break;
                case '?': e.preventDefault(); this.toggleKeyboardHelp(); break;
            }
        });
    }

    // Основни методи за контроли
    togglePlay() {
        if (this.videoElement.paused) {
            this.videoElement.play();
            this.container.querySelector('.play-pause-btn').textContent = '⏸️';
        } else {
            this.videoElement.pause();
            this.container.querySelector('.play-pause-btn').textContent = '▶️';
        }
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        this.videoElement.muted = this.isMuted;
        this.updateVolumeDisplay();
    }

    togglePictureInPicture() {
        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        } else if (document.pictureInPictureEnabled) {
            this.videoElement.requestPictureInPicture();
        }
    }

    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.container.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    switchToQuality(quality) {
        if (quality === 'auto') {
            this.isAutoQuality = true;
            this.currentQuality = 'auto';
            this.updateQualityDisplay(this.translations.auto);
            return;
        }

        this.isAutoQuality = false;
        this.currentQuality = quality;

        const targetSource = this.videoElement.querySelector(`source[data-quality="${quality}"]`);
        if (targetSource) {
            const currentTime = this.videoElement.currentTime;
            const isPaused = this.videoElement.paused;

            this.videoElement.src = targetSource.src;
            this.videoElement.load();
            this.videoElement.currentTime = currentTime;
            
            if (!isPaused) {
                this.videoElement.play();
            }

            const qualityObj = this.qualities.find(q => q.quality == quality);
            this.updateQualityDisplay(qualityObj ? qualityObj.label : `${quality}p`);
        }
    }

    switchToSubtitle(subtitle) {
        this.currentSubtitle = subtitle;
        
        // Изключване на всички субтитри
        const tracks = this.videoElement.textTracks;
        for (let i = 0; i < tracks.length; i++) {
            tracks[i].mode = 'disabled';
        }

        // Включване на избрания субтитър
        if (subtitle !== 'off') {
            const track = Array.from(tracks).find(t => t.language === subtitle);
            if (track) {
                track.mode = 'showing';
            }
        }

        this.updateSubtitleMenu();
    }

    // Допълнителни методи
    cyclePlaybackSpeed() {
        const speeds = [0.5, 0.75, 1.0, 1.25, 1.5, 2.0];
        const currentIndex = speeds.indexOf(this.playbackRate);
        const nextIndex = (currentIndex + 1) % speeds.length;
        
        this.playbackRate = speeds[nextIndex];
        this.videoElement.playbackRate = this.playbackRate;
        
        this.container.querySelector('.playback-speed-btn').textContent = 
            this.playbackRate + 'x';
    }

    seekTo(percent) {
        this.videoElement.currentTime = percent * this.videoElement.duration;
    }

    seekRelative(seconds) {
        this.videoElement.currentTime += seconds;
    }

    seekToPercent(percent) {
        this.videoElement.currentTime = percent * this.videoElement.duration;
    }

    adjustVolume(delta) {
        this.volume = Math.max(0, Math.min(1, this.volume + delta));
        this.videoElement.volume = this.volume;
        this.updateVolumeDisplay();
    }

    // Display methods
    updateProgress() {
        const progress = this.container.querySelector('.progress-filled');
        const thumb = this.container.querySelector('.progress-thumb');
        const percent = (this.videoElement.currentTime / this.videoElement.duration) * 100;
        
        if (progress) progress.style.width = percent + '%';
        if (thumb) thumb.style.left = percent + '%';
        
        this.updateTimeDisplay();
    }

    updateTimeDisplay() {
        const current = this.container.querySelector('.current-time');
        const duration = this.container.querySelector('.duration');
        
        if (current) current.textContent = this.formatTime(this.videoElement.currentTime);
        if (duration) duration.textContent = this.formatTime(this.videoElement.duration);
    }

    updateVolumeDisplay() {
        const volumeBtn = this.container.querySelector('.volume-btn');
        const volumeSlider = this.container.querySelector('.volume-slider');
        
        if (this.isMuted) {
            volumeBtn.textContent = '🔇';
            volumeBtn.title = this.translations.unmute;
        } else if (this.volume > 0.5) {
            volumeBtn.textContent = '🔊';
            volumeBtn.title = this.translations.mute;
        } else if (this.volume > 0) {
            volumeBtn.textContent = '🔈';
            volumeBtn.title = this.translations.mute;
        } else {
            volumeBtn.textContent = '🔇';
            volumeBtn.title = this.translations.unmute;
        }
        
        if (volumeSlider) volumeSlider.value = this.volume;
    }

    updateQualityDisplay(text) {
        const qualityDisplay = this.container.querySelector('.current-quality');
        if (qualityDisplay) qualityDisplay.textContent = text;
    }

    // Helper methods
    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
    }

    formatBitrate(bitrate) {
        return bitrate ? bitrate.replace('k', ' kbps') : '';
    }

    isVideoFocused() {
        return this.container.contains(document.activeElement) || 
               document.activeElement === this.videoElement;
    }

    hideOtherMenus(exceptMenu) {
        const menus = this.container.querySelectorAll('.quality-menu, .subtitle-menu');
        menus.forEach(menu => {
            if (menu !== exceptMenu) {
                menu.style.display = 'none';
            }
        });
    }

    showLoading() {
        this.container.querySelector('.video-loading-spinner').style.display = 'flex';
    }

    hideLoading() {
        this.container.querySelector('.video-loading-spinner').style.display = 'none';
    }

    showError() {
        this.container.querySelector('.video-error-message').style.display = 'flex';
    }

    hideError() {
        this.container.querySelector('.video-error-message').style.display = 'none';
    }

    toggleKeyboardHelp() {
        const help = this.container.querySelector('.keyboard-shortcuts');
        help.style.display = help.style.display === 'none' ? 'block' : 'none';
    }

    retryLoading() {
        this.hideError();
        this.videoElement.load();
        this.videoElement.play().catch(() => {});
    }

    preloadQualities() {
        // Предварително зареждане на следващо качество
        this.qualities.forEach(quality => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'video';
            link.href = quality.url;
            document.head.appendChild(link);
        });
    }

    setInitialQuality() {
        this.switchToQuality('auto');
    }

    setupFallbackVideo() {
        this.videoElement.src = this.videoUrl;
        this.videoElement.load();
    }

    onEnterPiP() {
        this.isPiP = true;
        this.container.classList.add('pip-active');
    }

    onLeavePiP() {
        this.isPiP = false;
        this.container.classList.remove('pip-active');
    }

    destroy() {
        // Cleanup
        if (this.qualityCheckInterval) {
            clearInterval(this.qualityCheckInterval);
        }
    }
}

// Автоматична инициализация
document.addEventListener('DOMContentLoaded', function() {
    const videoContainers = document.querySelectorAll('[data-file-type="video"]');
    videoContainers.forEach(container => {
        new AdaptiveVideoPlayer(container);
    });
});

// Глобални event listeners
document.addEventListener('fullscreenchange', function() {
    const container = document.querySelector('.adaptive-video-player-wrapper');
    if (container) {
        container.classList.toggle('fullscreen', !!document.fullscreenElement);
    }
});
