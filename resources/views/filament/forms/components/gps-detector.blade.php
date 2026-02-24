<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="gpsDetector()">
        <div class="space-y-4">
            <!-- GPS Status -->
            <div class="rounded-lg border-2 p-4" :class="gpsReady ? 'border-green-500 bg-green-50' : 'border-yellow-500 bg-yellow-50'">
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <svg x-show="loading" class="animate-spin h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        
                        <svg x-show="!loading && gpsReady" class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>

                        <svg x-show="error" class="h-6 w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Loading State -->
                        <div x-show="loading">
                            <p class="text-sm font-semibold text-yellow-800">Mendeteksi lokasi...</p>
                            <p class="text-xs text-yellow-700 mt-1">Mohon tunggu, pastikan GPS aktif</p>
                        </div>

                        <!-- Success State -->
                        <div x-show="gpsReady && !error">
                            <p class="text-sm font-semibold text-green-800">✓ Lokasi terdeteksi</p>
                            <div class="mt-2 space-y-1 text-xs text-green-700">
                                <p>Lat: <span x-text="latitude?.toFixed(6)"></span></p>
                                <p>Long: <span x-text="longitude?.toFixed(6)"></span></p>
                                <p>Akurasi: <span x-text="accuracy?.toFixed(0)"></span> meter</p>
                            </div>
                        </div>

                        <!-- Error State -->
                        <div x-show="error">
                            <p class="text-sm font-semibold text-red-800">⚠ Gagal mendeteksi lokasi</p>
                            <p class="text-xs text-red-700 mt-1" x-text="error"></p>
                        </div>

                        <!-- Fake GPS Warning -->
                        <div x-show="isMock" class="mt-3 p-3 bg-red-100 border border-red-300 rounded-lg">
                            <p class="text-sm font-bold text-red-800">⚠️ FAKE GPS TERDETEKSI</p>
                            <p class="text-xs text-red-700 mt-1">
                                Sistem mendeteksi penggunaan aplikasi fake GPS atau mock location. 
                                Harap matikan aplikasi tersebut dan gunakan GPS asli.
                            </p>
                        </div>
                    </div>

                    <!-- Retry Button -->
                    <div x-show="error">
                        <button 
                            type="button"
                            @click="getLocation"
                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded"
                        >
                            Coba Lagi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Map Preview (Optional) -->
            <div x-show="gpsReady && latitude && longitude" class="rounded-lg overflow-hidden border">
                <div class="bg-gray-100 p-2 text-xs text-gray-600">
                    Preview Peta
                </div>
                <iframe 
                    :src="`https://www.openstreetmap.org/export/embed.html?bbox=${longitude-0.001},${latitude-0.001},${longitude+0.001},${latitude+0.001}&layer=mapnik&marker=${latitude},${longitude}`"
                    class="w-full h-48 border-0"
                ></iframe>
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" x-model="latitude" name="latitude">
            <input type="hidden" x-model="longitude" name="longitude">
            <input type="hidden" x-model="accuracy" name="accuracy">
            <input type="hidden" x-model="isMock" name="isMock">
        </div>
    </div>

    <script>
        function gpsDetector() {
            return {
                loading: false,
                gpsReady: false,
                latitude: null,
                longitude: null,
                accuracy: null,
                isMock: false,
                error: null,
                watchId: null,

                init() {
                    this.getLocation();
                },

                async getLocation() {
                    if (!navigator.geolocation) {
                        this.error = 'Browser Anda tidak mendukung geolocation';
                        return;
                    }

                    this.loading = true;
                    this.error = null;
                    this.gpsReady = false;

                    const options = {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    };

                    navigator.geolocation.getCurrentPosition(
                        (position) => this.handleSuccess(position),
                        (error) => this.handleError(error),
                        options
                    );
                },

                async handleSuccess(position) {
                    this.loading = false;
                    this.gpsReady = true;
                    
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.accuracy = position.coords.accuracy;

                    // Detect fake GPS/mock location
                    this.isMock = await this.detectMockLocation(position);

                    // Update Livewire
                    const data = {
                        latitude: this.latitude,
                        longitude: this.longitude,
                        accuracy: this.accuracy,
                        isMock: this.isMock
                    };

                    this.$wire.set('{{ $getStatePath() }}', data);

                    // Multiple checks for higher accuracy
                    this.performSecondaryChecks();
                },

                handleError(error) {
                    this.loading = false;
                    this.gpsReady = false;
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            this.error = "Izin akses lokasi ditolak. Aktifkan izin lokasi di browser.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            this.error = "Informasi lokasi tidak tersedia.";
                            break;
                        case error.TIMEOUT:
                            this.error = "Waktu permintaan lokasi habis. Coba lagi.";
                            break;
                        default:
                            this.error = "Terjadi kesalahan tidak dikenal.";
                    }
                },

                async detectMockLocation(position) {
                    let isFake = false;

                    // Check 1: Accuracy too perfect (usually < 5 meters from fake GPS)
                    if (position.coords.accuracy < 5) {
                        console.warn('🚨 Suspicious: Accuracy too perfect');
                        isFake = true;
                    }

                    // Check 2: Check for common mock location indicators
                    if (position.mocked === true) {
                        console.warn('🚨 Mock location detected by browser');
                        isFake = true;
                    }

                    // Check 3: Speed and heading availability (real GPS usually provides this)
                    if (position.coords.speed === null && position.coords.heading === null) {
                        console.warn('⚠️ No speed/heading data (possible mock)');
                        // Not definitive, just suspicious
                    }

                    // Check 4: Altitude check
                    if (position.coords.altitude === null || position.coords.altitude === 0) {
                        console.warn('⚠️ No altitude data (suspicious)');
                    }

                    // Check 5: Check for developer mode (Android)
                    if (this.accuracy < 10 && !position.coords.altitudeAccuracy) {
                        console.warn('⚠️ Possible mock location detected');
                        isFake = true;
                    }

                    return isFake;
                },

                performSecondaryChecks() {
                    // Get location multiple times to verify consistency
                    setTimeout(() => {
                        navigator.geolocation.getCurrentPosition((pos) => {
                            const latDiff = Math.abs(pos.coords.latitude - this.latitude);
                            const lonDiff = Math.abs(pos.coords.longitude - this.longitude);
                            
                            // If location is exactly the same (no movement at all), suspicious
                            if (latDiff === 0 && lonDiff === 0 && pos.coords.accuracy < 5) {
                                console.warn('🚨 Location identical in multiple reads - highly suspicious');
                                this.isMock = true;
                                
                                // Update Livewire
                                const data = {
                                    latitude: this.latitude,
                                    longitude: this.longitude,
                                    accuracy: this.accuracy,
                                    isMock: this.isMock
                                };
                                this.$wire.set('{{ $getStatePath() }}', data);
                            }
                        });
                    }, 2000);
                },

                destroy() {
                    if (this.watchId) {
                        navigator.geolocation.clearWatch(this.watchId);
                    }
                }
            }
        }
    </script>
</x-dynamic-component>
