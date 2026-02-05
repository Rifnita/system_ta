<div class="space-y-6">
    {{-- Header Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="border-l-4 border-blue-500 pl-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ $getRecord()->judul }}</h1>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $getRecord()->user->name }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    {{ $getRecord()->kategori }}
                </span>
            </div>
        </div>
    </div>

    {{-- Info Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Tanggal</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-white mt-0.5">
                        {{ $getRecord()->tanggal_aktivitas->format('d F Y') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Waktu</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-white mt-0.5">
                        {{ \Carbon\Carbon::parse($getRecord()->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($getRecord()->waktu_selesai)->format('H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Durasi</p>
                    <p class="text-base font-semibold text-green-600 dark:text-green-400 mt-0.5">
                        {{ $getRecord()->durasi }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Deskripsi --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Deskripsi Kegiatan
        </h2>
        <div class="prose prose-sm max-w-none dark:prose-invert">
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $getRecord()->deskripsi ?? 'Tidak ada deskripsi' }}</p>
        </div>
    </div>

    {{-- Lokasi --}}
    @if($getRecord()->lokasi)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Lokasi
        </h2>
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <p class="text-gray-700 dark:text-gray-300">{{ $getRecord()->lokasi }}</p>
                @php
                    $lokasi = $getRecord()->lokasi;
                    $isCoordinates = preg_match('/^-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?$/', $lokasi);
                    $mapUrl = null;
                    
                    if ($isCoordinates) {
                        $coord = preg_replace('/\s+/', '', $lokasi);
                        $mapUrl = 'https://www.google.com/maps?q=' . $coord;
                    } elseif (str_starts_with($lokasi, 'http://') || str_starts_with($lokasi, 'https://')) {
                        $mapUrl = $lokasi;
                    }
                @endphp
                
                @if($mapUrl)
                    <a href="{{ $mapUrl }}" target="_blank" class="inline-flex items-center mt-2 px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Foto Dokumentasi --}}
    @if(!empty($getRecord()->foto_bukti) && count($getRecord()->foto_bukti) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Foto Dokumentasi
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($getRecord()->foto_bukti as $foto)
                @php
                    // Try multiple paths to find the image
                    $imagePaths = [
                        storage_path('app/private/' . $foto),
                        storage_path('app/public/' . $foto),
                        \Illuminate\Support\Facades\Storage::disk('local')->path('private/' . $foto),
                    ];
                    
                    $imageUrl = null;
                    foreach ($imagePaths as $path) {
                        if (file_exists($path)) {
                            $imageUrl = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
                            break;
                        }
                    }
                @endphp
                
                @if($imageUrl)
                <div class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                    <img 
                        src="{{ $imageUrl }}" 
                        alt="Dokumentasi" 
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                    />
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Metadata Footer --}}
    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">Dibuat:</span>
                <span>{{ $getRecord()->created_at->format('d M Y, H:i') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="font-medium">Diperbarui:</span>
                <span>{{ $getRecord()->updated_at->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>
</div>
