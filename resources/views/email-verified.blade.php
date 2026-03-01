<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if(session('success'))
    <meta http-equiv="refresh" content="3;url=/admin">
    @endif
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(135deg, #eff1f7 0%, #d9deea 100%);">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center border" style="border-color: #d9deea;">
            @if(session('success'))
                <div class="mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 animate-pulse">
                        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-3">Email Terverifikasi</h2>
                <p class="text-gray-600 mb-6">{{ session('success') }}</p>

                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-3">Anda akan diarahkan ke dasbor dalam <span id="countdown" class="font-bold" style="color: #2f497f;">3</span> detik...</p>
                </div>

                <a href="/admin" class="inline-block text-white font-semibold py-3 px-8 rounded-lg transition duration-200 shadow-lg" style="background: linear-gradient(90deg, #405b97 0%, #2f497f 100%);">
                    Masuk Sekarang
                </a>
            @else
                <div class="mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                        <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-3">Verifikasi Gagal</h2>
                <p class="text-gray-600 mb-4">{{ session('error', 'Link verifikasi tidak valid atau sudah kedaluwarsa.') }}</p>
                <p class="text-sm text-gray-500 mb-6">Silakan hubungi administrator untuk mendapatkan link verifikasi baru.</p>

                <a href="/admin" class="inline-block text-white font-semibold py-3 px-8 rounded-lg transition duration-200 shadow-lg" style="background-color: #405b97;">
                    Kembali ke Dasbor
                </a>
            @endif
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak cipta dilindungi.
        </p>
    </div>

    @if(session('success'))
    <script>
        let seconds = 3;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = '/admin';
            }
        }, 1000);
    </script>
    @endif
</body>
</html>
