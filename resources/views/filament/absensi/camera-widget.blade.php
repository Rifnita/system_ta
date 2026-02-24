@php
    $tipe = $tipe ?? 'masuk';
    $title = $title ?? 'Verifikasi Wajah';
    $subtitle = $subtitle ?? 'Posisikan wajah pada frame lalu ambil foto.';

    $fotoField = "foto_{$tipe}";
    $fotoStatePath = "data.{$fotoField}";
@endphp

<div x-data="attendanceCameraWidget({ statePath: '{{ $fotoStatePath }}' })" x-init="init()" class="abs-widget" wire:ignore>
    <div class="abs-widget__head">
        <div>
            <h3 class="abs-widget__title">{{ $title }}</h3>
            <p class="abs-widget__subtitle">{{ $subtitle }}</p>
        </div>
        <span class="abs-pill" :class="statusClass" x-text="statusText"></span>
    </div>

    <div class="abs-camera-frame">
        <video x-ref="video" autoplay playsinline class="abs-camera-media" x-show="!captured"></video>
        <img x-ref="preview" :src="photoData || ''" class="abs-camera-media" x-show="captured" alt="Preview foto absensi" />

        <div class="abs-camera-overlay">
            Pastikan wajah terlihat jelas, pencahayaan cukup, dan kamera stabil.
        </div>
    </div>

    <p x-show="error" x-cloak class="abs-alert abs-alert--danger" x-text="error"></p>

    <div class="abs-actions">
        <x-filament::button type="button" color="primary" x-show="cameraReady && !captured" x-on:click="capturePhoto">
            Ambil Foto
        </x-filament::button>

        <x-filament::button type="button" color="gray" x-show="captured" x-on:click="retakePhoto">
            Ambil Ulang
        </x-filament::button>
    </div>
</div>

@once
    <style>
        .abs-widget {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            padding: 16px;
        }
        .abs-widget__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }
        .abs-widget__title {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }
        .abs-widget__subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: #64748b;
        }
        .abs-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .abs-pill--warn { background: #fef3c7; color: #92400e; }
        .abs-pill--ok { background: #dcfce7; color: #166534; }
        .abs-pill--saved { background: #dbeafe; color: #1e40af; }
        .abs-pill--error { background: #fee2e2; color: #991b1b; }

        .abs-camera-frame {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            background: #111827;
            aspect-ratio: 16 / 9;
        }
        .abs-camera-media {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .abs-camera-overlay {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 10px 12px;
            font-size: 11px;
            color: #fff;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0));
        }
        .abs-alert {
            margin: 10px 0 0;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 12px;
            border: 1px solid;
        }
        .abs-alert--danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }
        .abs-actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
    </style>

    <script>
        window.attendanceCameraWidget = window.attendanceCameraWidget || function (config) {
            return {
                statePath: config.statePath,
                stream: null,
                cameraReady: false,
                captured: false,
                error: null,
                photoData: null,

                get statusText() {
                    if (this.error) return 'Gagal';
                    if (this.captured) return 'Foto tersimpan';
                    if (this.cameraReady) return 'Kamera siap';
                    return 'Mengaktifkan kamera';
                },

                get statusClass() {
                    if (this.error) return 'abs-pill--error';
                    if (this.captured) return 'abs-pill--saved';
                    if (this.cameraReady) return 'abs-pill--ok';
                    return 'abs-pill--warn';
                },

                init() {
                    const existingPhoto = this.$wire.get(this.statePath);

                    if (existingPhoto) {
                        this.photoData = existingPhoto;
                        this.captured = true;
                        this.cameraReady = false;
                        return;
                    }

                    this.startCamera();
                },

                async startCamera() {
                    try {
                        this.error = null;
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: 'user',
                                width: { ideal: 1280 },
                                height: { ideal: 720 },
                            },
                            audio: false,
                        });

                        this.$refs.video.srcObject = this.stream;
                        this.cameraReady = true;
                    } catch (error) {
                        this.cameraReady = false;
                        this.error = 'Kamera tidak dapat diakses. Pastikan izin kamera di browser sudah diizinkan.';
                    }
                },

                capturePhoto() {
                    if (!this.$refs.video || !this.cameraReady) return;

                    const video = this.$refs.video;
                    const canvas = document.createElement('canvas');

                    const maxWidth = 960;
                    const scale = Math.min(1, maxWidth / (video.videoWidth || maxWidth));
                    canvas.width = Math.floor((video.videoWidth || maxWidth) * scale);
                    canvas.height = Math.floor((video.videoHeight || 540) * scale);

                    const context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const imageData = canvas.toDataURL('image/jpeg', 0.82);
                    this.photoData = imageData;
                    this.captured = true;
                    this.stopCamera();
                    this.$wire.set(this.statePath, imageData);
                },

                retakePhoto() {
                    this.captured = false;
                    this.photoData = null;
                    this.$wire.set(this.statePath, null);
                    this.startCamera();
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach((track) => track.stop());
                        this.stream = null;
                    }
                    this.cameraReady = false;
                },
            };
        };
    </script>
@endonce
