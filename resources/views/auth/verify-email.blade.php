<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5efe4;
            --card: #fffaf2;
            --text: #111111;
            --muted: #444444;
            --border: #e5d2a6;
            --primary: #243a66;
            --primary-hover: #192a4d;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(180deg, #fcf8f0 0%, var(--bg) 100%);
            color: var(--text);
            padding: 24px;
        }

        .card {
            width: min(640px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 18px 40px rgba(16, 33, 75, 0.08);
            padding: 34px 32px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }

        .brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .brand-name {
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1.1;
        }

        h1 {
            margin: 0 0 18px;
            font-size: 1.35rem;
            line-height: 1.25;
            font-weight: 700;
            color: var(--text);
        }

        p {
            margin: 0 0 16px;
            font-size: 1rem;
            line-height: 1.7;
            color: var(--muted);
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(64, 91, 151, 0.08);
            color: var(--primary);
            border: 1px solid rgba(64, 91, 151, 0.16);
        }

        .actions {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .button {
            appearance: none;
            border: 0;
            border-radius: 12px;
            padding: 12px 18px;
            background: linear-gradient(90deg, var(--primary) 0%, #2f497f 100%);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .button:hover {
            background: linear-gradient(90deg, var(--primary-hover) 0%, var(--primary) 100%);
        }

        .hint {
            font-size: 0.92rem;
            color: var(--muted);
        }

        @media (max-width: 640px) {
            .card { padding: 24px 18px; }
            .brand-name { font-size: 1.2rem; }
            h1 { font-size: 1.12rem; }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">
            @if (file_exists(public_path('images/company-logo.svg')))
                <img src="{{ asset('images/company-logo.svg') }}" alt="{{ config('app.name') }}">
            @elseif (file_exists(public_path('images/company-logo.png')))
                <img src="{{ asset('images/company-logo.png') }}" alt="{{ config('app.name') }}">
            @endif
            <div class="brand-name">{{ config('app.name') }}</div>
        </div>

        <h1>Verifikasi alamat email Anda</h1>

        @if (session('status') === 'verification-link-sent')
            <div class="alert">
                Email verifikasi baru sudah dikirim.
            </div>
        @endif

        <p>
            Kami telah mengirimkan email ke {{ auth()->user()->email }}
            yang berisikan instruksi cara verifikasi alamat email Anda.
        </p>

        <p>
            Belum menerima email?
            Klik tombol kirim ulang di bawah.
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="actions">
            @csrf
            <button type="submit" class="button">Kirim ulang</button>
            <span class="hint">Email akan dikirim langsung tanpa antrean queue.</span>
        </form>
    </main>
</body>
</html>
