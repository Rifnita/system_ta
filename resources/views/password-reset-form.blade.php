<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(135deg, #eff1f7 0%, #d9deea 100%);">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8 border" style="border-color: #d9deea;">
            <div class="text-center mb-8">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4" style="background-color: #eff1f7;">
                    <svg class="h-10 w-10" style="color: #2f497f;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Reset Kata Sandi</h2>
                <p class="text-gray-600 text-sm">Masukkan kata sandi baru untuk akun Anda</p>
            </div>

            @if($error)
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-red-700 text-sm">{{ $error }}</p>
                </div>
                <a href="/admin" class="block w-full text-center text-white font-semibold py-3 px-8 rounded-lg transition duration-200" style="background-color: #405b97;">
                    Kembali ke Dasbor
                </a>
            @else
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            @foreach ($errors->all() as $error)
                                <p class="text-red-700 text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email"
                               id="email"
                               value="{{ $email }}"
                               disabled
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               minlength="8"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                               style="--tw-ring-color: #405b97;"
                               placeholder="Minimal 8 karakter">
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               minlength="8"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                               style="--tw-ring-color: #405b97;"
                               placeholder="Ketik ulang kata sandi">
                    </div>

                    <button type="submit"
                            class="w-full text-white font-semibold py-3 px-8 rounded-lg transition duration-200 shadow-lg"
                            style="background: linear-gradient(90deg, #405b97 0%, #2f497f 100%);">
                        Reset Kata Sandi
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="/admin" class="text-sm text-gray-600 transition" style="color: #405b97;">
                        &larr; Kembali ke Halaman Masuk
                    </a>
                </div>
            @endif
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak cipta dilindungi.
        </p>
    </div>
</body>
</html>
