<div class="space-y-6">
    {{-- Header dengan Gradient --}}
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h2 class="text-3xl font-bold mb-2">{{ $getRecord()->judul }}</h2>
                <div class="flex flex-wrap gap-3 mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 backdrop-blur-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $getRecord()->user->name }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 backdrop-blur-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ $getRecord()->kategori }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid Layout untuk Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Card Tanggal --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $getRecord()->tanggal_aktivitas->format('d F Y') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Card Waktu --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-l-4 border-amber-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900 rounded-full p-3">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Waktu</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($getRecord()->waktu_mulai)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($getRecord()->waktu_selesai)->format('H:i') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Card Durasi --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Durasi</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $getRecord()->durasi }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Deskripsi Detail --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Deskripsi Detail
        </h3>
        <div class="prose prose-sm max-w-none dark:prose-invert">
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                {{ $getRecord()->deskripsi ?? 'Tidak ada deskripsi' }}
            </p>
        </div>
    </div>

    {{-- Lokasi --}}
    @if($getRecord()->lokasi)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Lokasi
        </h3>
        <div class="flex items-center">
            <p class="text-gray-700 dark:text-gray-300 mr-4">{{ $getRecord()->lokasi }}</p>
            @php
                $lokasi = $getRecord()->lokasi;
                $isCoordinates = preg_match('/^-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?$/', $lokasi);
                $mapUrl = null;
                
                if ($isCoordinates) {
                    $coord = preg_replace('/\s+/', '', $lokasi);
                    $mapUrl = 'https://www.google.com/maps?q=' . $coord;
                } elseif (str_starts_with($lokasi, ['http://', 'https://'])) {
                    $mapUrl = $lokasi;
                }
            @endphp
            
            @if($mapUrl)
                <a href="{{ $mapUrl }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Lihat di Maps
                </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Foto Dokumentasi --}}
    @if(!empty($getRecord()->foto_bukti) && count($getRecord()->foto_bukti) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Foto Dokumentasi
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($getRecord()->foto_bukti as $foto)
                <div class="group relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img 
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($foto) }}" 
                        alt="Dokumentasi" 
                        class="w-full h-64 object-cover transform group-hover:scale-110 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <a 
                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($foto) }}" 
                                target="_blank"
                                class="inline-flex items-center text-white text-sm font-medium hover:text-blue-300"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                                Lihat Full Size
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Informasi Sistem --}}
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Informasi Sistem
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Dibuat pada:</span>
                <span class="ml-2 text-gray-900 dark:text-white font-medium">
                    {{ $getRecord()->created_at->format('d F Y, H:i') }} WIB
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Terakhir diperbarui:</span>
                <span class="ml-2 text-gray-900 dark:text-white font-medium">
                    {{ $getRecord()->updated_at->format('d F Y, H:i') }} WIB
                </span>
            </div>
        </div>
    </div>
</div>
