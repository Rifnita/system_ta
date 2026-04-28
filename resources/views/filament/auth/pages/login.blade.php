{{-- ═══ Global overrides: strip Filament page chrome ═══ --}}
<style>
/* Hilangkan background & padding dari Filament page wrapper di luar blade kita */
.fi-simple-main,
.fi-simple-layout,
.fi-simple-page,
[class*="fi-simple"] {
    background: transparent !important;
    padding: 0 !important;
    box-shadow: none !important;
}

/* Hilangkan card putih Filament (fi-simple-card / fi-page-content) */
.fi-simple-main > *,
.fi-simple-layout > *,
.fi-simple-page > * {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    max-width: none !important;
    width: 100% !important;
}

/* Pastikan body & html tidak punya background yang mengganggu */
body, html {
    background: #eceef4 !important;
}
</style>

@php
    $heading = $this->getHeading();
    $subheading = $this->getSubHeading();
    $appName = (string) config('app.name', 'System Absensi');

    $logoUrl = null;
    if (file_exists(public_path('images/company-logo.svg'))) {
        $logoUrl = asset('images/company-logo.svg');
    } elseif (file_exists(public_path('images/company-logo.png'))) {
        $logoUrl = asset('images/company-logo.png');
    }
@endphp

<div class="lx-shell">
<style>
/* ─── Scoped reset ─────────────────────────────────────────────── */
.lx-shell *, .lx-shell *::before, .lx-shell *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* ─── Full-viewport wrapper ─────────────────────────────────────── */
.lx-shell {
    --lx-navy:    #1b3270;
    --lx-navy2:   #152960;
    --lx-navy3:   #0f2050;
    --lx-gold:    #c9a84c;
    --lx-ink:     #111e3c;
    --lx-muted:   #5a6a85;
    --lx-border:  #dde3ef;
    --lx-bg:      #eceef4;
    --lx-white:   #ffffff;
    --lx-radius:  1rem;
    --lx-shadow:  0 20px 60px rgba(15,32,80,.16);

    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    width: 100%;
    padding: 2rem 1.25rem;
    background: transparent;
    font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
}

/* ─── Card ──────────────────────────────────────────────────────── */
.lx-card {
    display: grid;
    grid-template-columns: minmax(0,46%) minmax(0,54%);
    width: 100%;
    max-width: 960px;
    background: transparent;
    border-radius: 1.5rem;
    overflow: hidden;
    box-shadow: none;
}

/* ══════════════════════════════════════════════════════════════════
   LEFT PANEL
══════════════════════════════════════════════════════════════════ */
.lx-left {
    background:
        radial-gradient(ellipse at 20% -10%, #2e4f9a 0%, transparent 55%),
        radial-gradient(ellipse at 85% 95%, #162c6b 0%, transparent 50%),
        linear-gradient(160deg, #1e3a82 0%, var(--lx-navy) 45%, var(--lx-navy3) 100%);
    color: rgba(255,255,255,.95);
    padding: 2.25rem 2.25rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.6rem;
    position: relative;
    overflow: hidden;
}

/* subtle dot texture */
.lx-left::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.045) 1px, transparent 1px);
    background-size: 22px 22px;
    pointer-events: none;
}

/* ── Brand ── */
.lx-brand {
    display: flex;
    align-items: center;
    gap: .85rem;
    position: relative;
}

.lx-brand-logo-wrap {
    width: 3rem;
    height: 3rem;
    border-radius: .85rem;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.22);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.lx-brand-logo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: .35rem;
}

.lx-brand-placeholder-icon {
    width: 1.5rem;
    height: 1.5rem;
    color: var(--lx-gold);
}

.lx-brand-name {
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .2em;
    text-transform: uppercase;
    line-height: 1.25;
    color: #fff;
}

.lx-brand-tagline {
    font-size: .8rem;
    color: rgba(255,255,255,.72);
    margin-top: .2rem;
    font-weight: 400;
}

/* ── Lead copy ── */
.lx-lead {
    font-size: 1.08rem;
    line-height: 1.55;
    font-weight: 500;
    color: rgba(255,255,255,.9);
    position: relative;
}

/* ── Feature cards ── */
.lx-features {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .85rem;
    margin-top: auto;
    position: relative;
}

.lx-feature {
    padding: 1.1rem 1rem 1rem;
    border-radius: .95rem;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.08);
    display: flex;
    flex-direction: column;
    gap: .3rem;
    transition: background 150ms;
}

.lx-feature:hover {
    background: rgba(255,255,255,.13);
}

.lx-feature-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .4rem;
}

.lx-feature-icon-wrap {
    width: 2rem;
    height: 2rem;
    border-radius: .5rem;
    background: rgba(255,255,255,.11);
    border: 1px solid rgba(255,255,255,.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.lx-feature-icon {
    width: 1.1rem;
    height: 1.1rem;
    color: rgba(255,255,255,.9);
}

.lx-feature-badge {
    font-size: .6rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    font-weight: 700;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 999px;
    padding: .22rem .6rem;
    color: rgba(255,255,255,.85);
    white-space: nowrap;
}

.lx-feature-title {
    font-size: .97rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}

.lx-feature-note {
    font-size: .78rem;
    color: rgba(255,255,255,.72);
}

.lx-feature-link {
    margin-top: .6rem;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    color: var(--lx-gold);
    text-decoration: none;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    font-size: .7rem;
}

.lx-feature-link svg {
    width: .85rem;
    height: .85rem;
}

/* ── Footer note ── */
.lx-footer-note {
    font-size: .77rem;
    line-height: 1.6;
    color: rgba(255,255,255,.52);
    position: relative;
}

/* ══════════════════════════════════════════════════════════════════
   RIGHT PANEL
══════════════════════════════════════════════════════════════════ */
.lx-right {
    background: #f6f8fc;
    padding: 2.5rem 2.75rem 2.25rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0;
    box-shadow: var(--lx-shadow);
    border-radius: 1.5rem;
}

.lx-right-eyebrow {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .22em;
    text-transform: uppercase;
    color: var(--lx-navy);
    margin-bottom: .65rem;
}

.lx-right-heading {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.15;
    color: var(--lx-ink);
    margin-bottom: .6rem;
    letter-spacing: -.01em;
}

.lx-right-subheading {
    font-size: .875rem;
    line-height: 1.6;
    color: var(--lx-muted);
    margin-bottom: 1.5rem;
}

/* ── Filament form overrides — scoped tightly to .lx-form-wrap ── */
.lx-form-wrap .fi-fo { gap: .85rem !important; }

/* Label */
.lx-form-wrap .fi-fo-field-wrp-label label,
.lx-form-wrap .fi-fo-field-wrp > label {
    font-size: .82rem !important;
    font-weight: 600 !important;
    color: var(--lx-ink) !important;
}

/* Input wrapper */
.lx-form-wrap .fi-input-wrp,
.lx-form-wrap input[type="text"],
.lx-form-wrap input[type="email"],
.lx-form-wrap input[type="password"] {
    border-radius: .35rem !important;
    border-color: var(--lx-border) !important;
    background: var(--lx-white) !important;
    box-shadow: 0 1px 3px rgba(0,0,0,.05) !important;
    transition: border-color 150ms, box-shadow 150ms !important;
}

.lx-form-wrap .fi-input-wrp:focus-within {
    border-color: #3b5dbf !important;
    box-shadow: 0 0 0 3px rgba(59,93,191,.12) !important;
}

/* Input text */
.lx-form-wrap .fi-input {
    font-size: .875rem !important;
    color: var(--lx-ink) !important;
}

.lx-form-wrap .fi-input::placeholder {
    color: #aab3c4 !important;
}

/* ── "Lupa kata sandi?" ──
   Filament renders: label-row → hint → input-wrp
   Kita mau: label-row → input-wrp → hint (di bawah, kanan)
   Caranya: jadikan fi-fo-field-wrp flex column dan gunakan order
── */
.lx-form-wrap .fi-fo-field-wrp {
    display: flex !important;
    flex-direction: column !important;
}

/* Label baris = order 1 */
.lx-form-wrap .fi-fo-field-wrp-label {
    order: 1 !important;
    display: flex !important;
    align-items: center !important;
    margin-bottom: .35rem !important;
}

/* Input wrapper = order 2 */
.lx-form-wrap .fi-input-wrp,
.lx-form-wrap .fi-fo-field-wrp > div:has(input),
.lx-form-wrap .fi-fo-field-wrp > .fi-input-wrp {
    order: 2 !important;
}

/* Hint (Lupa kata sandi?) = order 3, pojok kanan, di bawah input */
.lx-form-wrap .fi-fo-field-wrp-hint {
    order: 3 !important;
    display: flex !important;
    justify-content: flex-end !important;
    margin-top: .3rem !important;
    margin-bottom: 0 !important;
}

/* Error message = order 4 */
.lx-form-wrap .fi-fo-field-wrp-error-message {
    order: 4 !important;
}

.lx-form-wrap .fi-fo-field-wrp-hint a,
.lx-form-wrap .fi-link {
    font-size: .8rem !important;
    font-weight: 600 !important;
    color: var(--lx-navy) !important;
    text-decoration: none !important;
}

.lx-form-wrap .fi-fo-field-wrp-hint a:hover,
.lx-form-wrap .fi-link:hover {
    color: #2f4f90 !important;
    text-decoration: underline !important;
}

/* Checkbox */
.lx-form-wrap .fi-checkbox-label {
    font-size: .82rem !important;
    color: var(--lx-ink) !important;
}

.lx-form-wrap .fi-checkbox-input {
    border-radius: .35rem !important;
    border-color: #b8c4d6 !important;
}

/* Submit / Masuk button */
.lx-form-wrap .fi-btn.fi-color-primary {
    width: 100%;
    min-height: 2.9rem;
    border-radius: .75rem !important;
    font-size: .95rem !important;
    font-weight: 700 !important;
    letter-spacing: .01em !important;
    background: var(--lx-navy) !important;
    border-color: transparent !important;
    box-shadow: 0 4px 14px rgba(27,50,112,.28) !important;
    transition: background 150ms, box-shadow 150ms !important;
}

.lx-form-wrap .fi-btn.fi-color-primary:hover {
    background: var(--lx-navy2) !important;
    box-shadow: 0 6px 18px rgba(27,50,112,.36) !important;
}

/* Register / Daftar button */
.lx-register {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: .65rem;
    min-height: 2.75rem;
    border-radius: .75rem;
    border: 1px solid var(--lx-border);
    background: var(--lx-white);
    color: var(--lx-ink);
    text-decoration: none;
    font-size: .9rem;
    font-weight: 600;
    transition: background 120ms, border-color 120ms;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}

.lx-register:hover {
    background: #f0f4fd;
    border-color: #b8cce6;
}

/* ─── Responsive ────────────────────────────────────────────────── */
@media (max-width: 820px) {
    .lx-shell { padding: .75rem .75rem 1.5rem; align-items: flex-start; }
    .lx-card { grid-template-columns: 1fr; max-width: 480px; }
    .lx-left { padding: 1.75rem 1.5rem 1.5rem; gap: 1.25rem; }
    .lx-features { grid-template-columns: 1fr 1fr; }
    .lx-right { padding: 1.75rem 1.5rem; }
    .lx-right-heading { font-size: 1.55rem; }
}

@media (max-width: 480px) {
    .lx-features { grid-template-columns: 1fr; }
}
</style>

    <div class="lx-card">

        {{-- ════ LEFT PANEL ════ --}}
        <section class="lx-left" aria-hidden="true">

            {{-- Brand --}}
            <div class="lx-brand">
                <div class="lx-brand-logo-wrap">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                    @else
                        {{-- Fallback: building icon --}}
                        <svg class="lx-brand-placeholder-icon" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75
                                     3v1.5m0 0V6m0-1.5h1.5m-1.5 0H5.25M3.75
                                     21V8.25C3.75 7.007 4.757 6 6 6h2.25m10.5
                                     15V8.25C18.75 7.007 17.743 6 16.5 6H14.25"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <div class="lx-brand-name">{{ $appName }}</div>
                    <div class="lx-brand-tagline">Sistem absensi pegawai</div>
                </div>
            </div>

            {{-- Lead copy --}}
            <p class="lx-lead">
                Kelola kehadiran, pantau jam kerja, dan akses laporan absensi pegawai dengan mudah dalam satu sistem terintegrasi.
            </p>

            {{-- Feature cards --}}
            <div class="lx-features">

                {{-- Card 1: Login Marketing — cart icon (outline) --}}
                <article class="lx-feature">
                    <div class="lx-feature-top">
                        <div class="lx-feature-icon-wrap">
                            {{-- Heroicon: shopping-cart (outline) --}}
                            <svg class="lx-feature-icon" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5
                                         14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3
                                         2.1-4.684 2.924-7.138a60.114 60.114 0 0
                                         0-16.536-1.84M7.5 14.25L5.106 5.272M6
                                         20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1
                                         1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75
                                         0 0 1 1.5 0Z"/>
                            </svg>
                        </div>
                        <span class="lx-feature-badge">Segera hadir</span>
                    </div>
                    <div class="lx-feature-title">Login Marketing</div>
                    <div class="lx-feature-note">Portal pemasaran</div>
                    <span class="lx-feature-link">
                        Placeholder
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25
                                     2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18
                                     18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </span>
                </article>

                {{-- Card 2: Login Internal — shield-check icon (outline) --}}
                <article class="lx-feature">
                    <div class="lx-feature-top">
                        <div class="lx-feature-icon-wrap">
                            {{-- Heroicon: shield-check (outline) --}}
                            <svg class="lx-feature-icon" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959
                                         11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3
                                         9.749c0 5.592 3.824 10.29 9 11.623
                                         5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571
                                         -.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                            </svg>
                        </div>
                        <span class="lx-feature-badge">Segera hadir</span>
                    </div>
                    <div class="lx-feature-title">Login Internal</div>
                    <div class="lx-feature-note">Portal terpisah</div>
                    <span class="lx-feature-link">
                        Placeholder
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25
                                     2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18
                                     18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </span>
                </article>

            </div>

            <p class="lx-footer-note">
                Tombol akses marketing dan internal akan diarahkan ke sistem terpisah pada tahap berikutnya.
            </p>

        </section>

        {{-- ════ RIGHT PANEL ════ --}}
        <section class="lx-right">

            <p class="lx-right-eyebrow">{{ $appName }}</p>

            @if (filled($heading))
                <h1 class="lx-right-heading">{{ $heading }}</h1>
            @endif

            @if (filled($subheading))
                <div class="lx-right-subheading">{!! $subheading !!}</div>
            @endif

            {{-- Filament form — wrapped for scoped CSS overrides --}}
            <div class="lx-form-wrap">
                {{ $this->content }}
            </div>

            @if (filament()->hasRegistration())
                <a class="lx-register" href="{{ filament()->getRegistrationUrl() }}">
                    Daftar Akun Baru
                </a>
            @endif

        </section>

    </div>

</div>