<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="cameraCapture()">
        <div class="space-y-4">
            <!-- Video Preview -->
            <div class="relative rounded-lg overflow-hidden bg-gray-900" style="max-width: 100%; aspect-ratio: 4/3;">
                <video 
                    x-ref="video" 
                    autoplay 
                    playsinline
                    class="w-full h-full object-cover"
                    x-show="!captured"
                ></video>
                
                <img 
                    x-ref="preview" 
                    x-show="captured" 
                    class="w-full h-full object-cover"
                />
                
                <!-- Status Indicator -->
                <div class="absolute top-4 left-4 right-4">
                    <div x-show="!cameraReady && !captured" class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengakses kamera...
                        </span>
                    </div>
                    
                    <div x-show="cameraReady && !captured" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Kamera siap
                        </span>
                    </div>

                    <div x-show="error" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm">
                        <span x-text="error"></span>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="flex gap-2 justify-center">
                <button 
                    type="button"
                    x-show="cameraReady && !captured"
                    @click="capturePhoto"
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold flex items-center gap-2"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    Ambil Foto
                </button>

                <button 
                    type="button"
                    x-show="captured"
                    @click="retakePhoto"
                    class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold flex items-center gap-2"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                    Foto Ulang
                </button>
            </div>

            <!-- Success Message -->
            <div x-show="captured" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-800 text-sm font-medium">
                    ✓ Foto berhasil diambil
                </p>
            </div>

            <!-- Hidden input for form -->
            <input type="hidden" x-model="photoData" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}">
        </div>
    </div>

    <script>
        function cameraCapture() {
            return {
                cameraReady: false,
                captured: false,
                photoData: null,
                error: null,
                stream: null,

                init() {
                    this.startCamera();
                },

                async startCamera() {
                    try {
                        this.error = null;
                        
                        // Request camera with specific constraints
                        const constraints = {
                            video: {
                                facingMode: 'user', // Front camera for selfie
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            }
                        };

                        this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                        this.$refs.video.srcObject = this.stream;
                        this.cameraReady = true;
                        
                    } catch (err) {
                        console.error('Camera error:', err);
                        this.error = 'Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.';
                        this.cameraReady = false;
                    }
                },

                capturePhoto() {
                    const video = this.$refs.video;
                    const canvas = document.createElement('canvas');
                    
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    const context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0);
                    
                    // Convert to base64
                    const imageData = canvas.toDataURL('image/jpeg', 0.8);
                    
                    // Show preview
                    this.$refs.preview.src = imageData;
                    this.photoData = imageData;
                    this.captured = true;
                    
                    // Stop camera
                    this.stopCamera();
                    
                    // Trigger Livewire update
                    this.$wire.set('{{ $getStatePath() }}', imageData);
                },

                retakePhoto() {
                    this.captured = false;
                    this.photoData = null;
                    this.startCamera();
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                        this.cameraReady = false;
                    }
                },

                destroy() {
                    this.stopCamera();
                }
            }
        }
    </script>
</x-dynamic-component>
