<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekomendasi Tanaman — SPK TOPSIS & Fuzzy WP</title>
    <style>
        :root {
            --bg-page: #f5f7f9;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --text-soft: #94a3b8;
            --green-primary: #2e7d4f;
            --green-hover: #24633e;
            --green-soft: #ecfdf5;
            --green-border: #a7f3d0;
            --green-text: #065f46;
            --amber-bg: #fffbeb;
            --amber-border: #fcd34d;
            --amber-text: #92400e;
            --red-bg: #fef2f2;
            --red-border: #fca5a5;
            --red-text: #991b1b;
            --shadow-card: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-float: 0 6px 16px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Header */
        .topbar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 14px 24px;
        }

        .topbar-inner {
            max-width: 1040px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .brand-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 500;
        }

        /* Main Layout */
        .page-container {
            max-width: 980px;
            margin: 28px auto;
            padding: 0 16px 40px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 24px;
            align-items: stretch;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            padding: 24px;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-width: 0;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px 12px;
            margin-bottom: 18px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
        }

        .icon-refresh-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #f8fafc;
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background-color 0.15s, color 0.15s, border-color 0.15s, transform 0.25s ease;
        }

        .icon-refresh-btn:hover {
            background: var(--green-soft);
            color: var(--green-primary);
            border-color: var(--green-border);
        }

        .icon-refresh-btn:active svg {
            transform: rotate(-180deg);
        }

        .icon-refresh-btn svg {
            transition: transform 0.25s ease;
        }

        /* Form Elements */
        .form-stack {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .field-label {
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
        }

        .field-input {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            color: var(--text-main);
            background: #ffffff;
            border: 1px solid var(--border-strong);
            border-radius: 8px;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .field-input:focus {
            border-color: var(--green-primary);
            box-shadow: 0 0 0 3px rgba(46, 125, 79, 0.14);
        }

        .field-input.is-invalid {
            border-color: #dc2626;
        }

        .field-hint {
            font-size: 12px;
            color: var(--text-muted);
        }

        .field-error {
            font-size: 12px;
            color: #dc2626;
            font-weight: 500;
        }

        .coord-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .coord-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 2px;
        }

        .link-btn-muted {
            background: none;
            border: none;
            color: var(--green-primary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .link-btn-muted:hover {
            color: var(--green-hover);
        }

        .submit-btn {
            width: 100%;
            margin-top: auto;
            padding: 12px 16px;
            background: var(--green-primary);
            color: #ffffff;
            font-size: 14.5px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.15s;
        }

        .submit-btn:hover {
            background: var(--green-hover);
        }

        /* Alerts & Badges */
        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }

        .alert-error {
            background: var(--red-bg);
            border-color: var(--red-border);
            color: var(--red-text);
        }

        .alert-amber {
            background: var(--amber-bg);
            border-color: var(--amber-border);
            color: var(--amber-text);
        }

        .alert-title {
            font-weight: 700;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .alert ul {
            padding-left: 18px;
            margin-top: 4px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-agreed {
            background: var(--green-soft);
            color: var(--green-text);
            border: 1px solid var(--green-border);
        }

        .badge-diff {
            background: var(--amber-bg);
            color: var(--amber-text);
            border: 1px solid var(--amber-border);
        }

        /* Weather Strip */
        .weather-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        .weather-pill {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 10px;
            text-align: center;
        }

        .weather-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            display: block;
        }

        .weather-value {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Rank #1 Banner Card */
        .top1-wrapper {
            margin-bottom: 14px;
        }

        .top1-banner {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            display: block;
            background: #ecfdf5;
        }

        .top1-overlay {
            position: relative;
            z-index: 2;
            margin: -42px 12px 0;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: var(--shadow-float);
        }

        .top1-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px;
        }

        .rank-tag {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--green-primary);
            background: var(--green-soft);
            padding: 2px 8px;
            border-radius: 6px;
        }

        .top1-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .top1-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .scores-line {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .scores-line strong {
            color: var(--text-main);
            font-weight: 600;
        }

        /* Rank #2-#5 Cards */
        .rank-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .rank-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #ffffff;
        }

        .rank-thumb {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: #ecfdf5;
        }

        .rank-info {
            flex: 1;
            min-width: 0;
        }

        .rank-sub {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
        }

        .rank-name {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-detail {
            padding: 6px 11px;
            font-size: 12px;
            font-weight: 600;
            color: var(--green-primary);
            background: var(--green-soft);
            border: 1px solid var(--green-border);
            border-radius: 7px;
            cursor: pointer;
            flex-shrink: 0;
            transition: background-color 0.15s;
        }

        .btn-detail:hover {
            background: #d1fae5;
        }

        /* Card Footer Links */
        .result-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .footer-link-btn {
            background: none;
            border: none;
            font-size: 13px;
            font-weight: 600;
            color: var(--green-primary);
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            padding: 2px 0;
        }

        .footer-link-btn-muted {
            color: var(--text-muted);
        }

        .footer-link-btn:hover {
            color: var(--text-main);
        }

        /* Empty State */
        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 16px;
            color: var(--text-muted);
        }

        .empty-icon {
            width: 54px;
            height: 54px;
            margin: 0 auto 14px;
            border-radius: 999px;
            background: var(--green-soft);
            color: var(--green-primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* History Strip */
        .history-card {
            margin-top: 22px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 18px;
        }

        .history-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .history-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 11px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #f8fafc;
            text-decoration: none;
            color: var(--text-main);
            font-size: 12px;
            transition: border-color 0.15s;
        }

        .history-chip:hover {
            border-color: var(--green-primary);
        }

        /* Modal */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 50;
        }

        .modal-backdrop.is-open {
            display: flex;
        }

        .modal-panel {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 720px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
            padding: 22px;
        }

        .modal-panel-wide {
            max-width: 880px;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .modal-close {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            color: var(--text-main);
        }

        .modal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .modal-table th,
        .modal-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        .modal-table th {
            background: #f8fafc;
            font-weight: 700;
            color: #334155;
        }

        .suit-bar {
            width: 72px;
            height: 7px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 6px;
        }

        .suit-fill {
            display: block;
            height: 100%;
            background: var(--green-primary);
            border-radius: 999px;
            transition: width 0.2s ease;
        }

        #modal-detail {
            z-index: 60;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Responsive Breakpoints */
        @media (max-width: 860px) {
            .cards-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 18px;
            }

            .card {
                height: auto;
            }

            .page-container {
                margin: 20px auto;
            }
        }

        @media (max-width: 640px) {
            .topbar {
                padding: 12px 16px;
            }

            .brand-title {
                font-size: 16px;
            }

            .brand-subtitle {
                font-size: 11px;
            }

            .card {
                padding: 18px;
                border-radius: 12px;
            }

            .card-title {
                font-size: 16.5px;
            }

            .top1-banner {
                height: 156px;
            }

            .top1-overlay {
                margin: -34px 8px 0;
                padding: 12px;
            }

            .top1-name {
                font-size: 17px;
            }

            .modal-panel {
                padding: 16px;
            }
        }

        @media (max-width: 440px) {
            .coord-grid {
                grid-template-columns: 1fr;
            }

            .weather-strip {
                grid-template-columns: repeat(auto-fit, minmax(92px, 1fr));
            }

            .rank-item {
                gap: 10px;
                padding: 9px 10px;
            }

            .rank-thumb {
                width: 48px;
                height: 48px;
            }
        }

        /* Print View */
        .print-only {
            display: none;
        }

        @media print {
            body {
                background: #ffffff;
                color: #000000;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .page-container {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }

            .cards-grid {
                grid-template-columns: 1fr !important;
            }

            .card {
                border: none;
                box-shadow: none;
                padding: 0;
            }

            .top1-banner {
                height: 140px;
            }

            .rank-item,
            .top1-overlay {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <header class="topbar no-print">
        <div class="topbar-inner">
            <a href="{{ route('home') }}" class="brand">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22L6.66 19.7C7.14 19.87 7.64 20 8 20C19 20 22 3 22 3C21 5 14 5.25 9 6.25C4 7.25 2 11.5 2 13.5C2 15.5 3.75 17.25 3.75 17.25C7 8 17 8 17 8Z" fill="#2E7D4F"/>
                </svg>
                <span class="brand-title">Rekomendasi Tanaman</span>
            </a>
            <span class="brand-subtitle">SPK TOPSIS &amp; Fuzzy WP · Cuaca Real-Time Open-Meteo</span>
        </div>
    </header>

    <main class="page-container">
        @if ($result)
            <div class="print-only" style="margin-bottom: 16px; border-bottom: 2px solid #1e293b; padding-bottom: 10px;">
                <h1 style="font-size: 20px; font-weight: 800;">Laporan Hasil Rekomendasi Tanaman</h1>
                <p style="font-size: 12px; color: #475569; margin-top: 2px;">
                    Metode: Pembobotan Random Forest + Kesesuaian Gaussian + Fuzzy Weighted Product &amp; TOPSIS
                </p>
                <p style="font-size: 12px; margin-top: 6px;">
                    <strong>Input Tanah &amp; Lokasi:</strong>
                    N = {{ $result['input']['N'] }},
                    P = {{ $result['input']['P'] }},
                    K = {{ $result['input']['K'] }},
                    pH = {{ $result['input']['ph'] }},
                    Koordinat = ({{ $result['location']['latitude'] ?? '-' }}, {{ $result['location']['longitude'] ?? '-' }})
                </p>
                <p style="font-size: 12px; margin-top: 4px;">
                    <strong>Agregasi Cuaca Open-Meteo:</strong>
                    Suhu 14h = {{ number_format($result['weather']['temperature'], 2) }} °C,
                    Kelembapan 14h = {{ number_format($result['weather']['humidity'], 2) }} %,
                    Curah Hujan 30h = {{ number_format($result['weather']['rainfall'], 2) }} mm
                </p>
            </div>
        @endif

        <div class="cards-grid">
            {{-- Panel Kiri: Form Input --}}
            <section class="card no-print" aria-label="Form Input Data Tanah dan Lokasi">
                <div class="card-header">
                    <h2 class="card-title">Masukkan Data Tanah &amp; Lokasi</h2>
                    <button
                        type="button"
                        class="icon-refresh-btn"
                        id="btn-reset-form"
                        title="Kosongkan Form Input"
                        aria-label="Kosongkan Form Input"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 12a9 9 0 1 0 3-6.7L3 8"></path>
                            <path d="M3 3v5h5"></path>
                        </svg>
                    </button>
                </div>

                @if (session('weather_error') || $errors->has('weather'))
                    <div class="alert alert-error" role="alert">
                        <div class="alert-title">Gagal Mengambil Data Cuaca</div>
                        <p>{{ session('weather_error') ?? $errors->first('weather') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('recommend.store') }}" class="form-stack" id="spk-form" novalidate>
                    @csrf

                    <div class="field-group">
                        <label class="field-label" for="input-N">Nitrogen (N)</label>
                        <input
                            type="number"
                            step="any"
                            name="N"
                            id="input-N"
                            class="field-input @error('N') is-invalid @enderror"
                            value="{{ old('N', $formInput['N'] ?? '') }}"
                            placeholder="e.g., 90"
                            required
                        >
                        <span class="field-hint">Rentang dataset: {{ $datasetRanges['N']['min'] }} – {{ $datasetRanges['N']['max'] }} mg/kg</span>
                        @error('N')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="input-P">Fosfor (P)</label>
                        <input
                            type="number"
                            step="any"
                            name="P"
                            id="input-P"
                            class="field-input @error('P') is-invalid @enderror"
                            value="{{ old('P', $formInput['P'] ?? '') }}"
                            placeholder="e.g., 42"
                            required
                        >
                        <span class="field-hint">Rentang dataset: {{ $datasetRanges['P']['min'] }} – {{ $datasetRanges['P']['max'] }} mg/kg</span>
                        @error('P')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="input-K">Kalium (K)</label>
                        <input
                            type="number"
                            step="any"
                            name="K"
                            id="input-K"
                            class="field-input @error('K') is-invalid @enderror"
                            value="{{ old('K', $formInput['K'] ?? '') }}"
                            placeholder="e.g., 43"
                            required
                        >
                        <span class="field-hint">Rentang dataset: {{ $datasetRanges['K']['min'] }} – {{ $datasetRanges['K']['max'] }} mg/kg</span>
                        @error('K')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="input-ph">pH Tanah</label>
                        <input
                            type="number"
                            step="any"
                            name="ph"
                            id="input-ph"
                            class="field-input @error('ph') is-invalid @enderror"
                            value="{{ old('ph', $formInput['ph'] ?? '') }}"
                            placeholder="e.g., 6.5"
                            required
                        >
                        <span class="field-hint">Nilai valid: 0 – 14 (rentang dataset: {{ number_format($datasetRanges['ph']['min'], 2, ',', '.') }} – {{ number_format($datasetRanges['ph']['max'], 2, ',', '.') }})</span>
                        @error('ph')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field-group">
                        <span class="field-label">Lokasi (Koordinat)</span>
                        <div class="coord-grid">
                            <div>
                                <label class="field-hint" for="input-latitude" style="display:block;margin-bottom:4px;">Lintang (-90 s.d. 90)</label>
                                <input
                                    type="number"
                                    step="any"
                                    name="latitude"
                                    id="input-latitude"
                                    class="field-input @error('latitude') is-invalid @enderror"
                                    value="{{ old('latitude', $formInput['latitude'] ?? '') }}"
                                    placeholder="-7.2504"
                                    required
                                >
                                @error('latitude')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="field-hint" for="input-longitude" style="display:block;margin-bottom:4px;">Bujur (-180 s.d. 180)</label>
                                <input
                                    type="number"
                                    step="any"
                                    name="longitude"
                                    id="input-longitude"
                                    class="field-input @error('longitude') is-invalid @enderror"
                                    value="{{ old('longitude', $formInput['longitude'] ?? '') }}"
                                    placeholder="112.7688"
                                    required
                                >
                                @error('longitude')
                                    <span class="field-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="coord-tools">
                            <button type="button" class="link-btn-muted" id="btn-geolocate">Gunakan GPS Perangkat</button>
                        </div>
                    </div>

                    {{-- Strip Ringkas 3 Hasil Agregasi Cuaca (di panel kiri sebelum Cek Rekomendasi) --}}
                    <div class="field-group">
                        <span class="field-label">Data Cuaca Lokasi (Open-Meteo)</span>
                        <div class="weather-strip" style="margin-bottom:4px;" aria-label="Ringkasan Data Cuaca Open-Meteo">
                            <div class="weather-pill">
                                <span class="weather-label">Suhu 14h</span>
                                <span class="weather-value" id="weather-temp-val">
                                    {{ $result ? number_format($result['weather']['temperature'], 2).' °C' : '-- °C' }}
                                </span>
                            </div>
                            <div class="weather-pill">
                                <span class="weather-label">Kelembapan 14h</span>
                                <span class="weather-value" id="weather-hum-val">
                                    {{ $result ? number_format($result['weather']['humidity'], 2).' %' : '-- %' }}
                                </span>
                            </div>
                            <div class="weather-pill">
                                <span class="weather-label">Curah Hujan 30h</span>
                                <span class="weather-value" id="weather-rain-val">
                                    {{ $result ? number_format($result['weather']['rainfall'], 2).' mm' : '-- mm' }}
                                </span>
                            </div>
                        </div>
                        <span id="weather-preview-status" class="field-hint">
                            {{ $result ? 'Data cuaca Open-Meteo siap digunakan untuk perhitungan.' : 'Isi koordinat Lintang & Bujur atau gunakan GPS untuk memuat data cuaca.' }}
                        </span>
                    </div>

                    <button type="submit" class="submit-btn" id="btn-submit">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <span>Cek Rekomendasi</span>
                    </button>
                </form>
            </section>

            {{-- Panel Kanan: Hasil Rekomendasi --}}
            <section class="card" aria-label="Hasil Rekomendasi">
                <div class="card-header">
                    <h2 class="card-title">Hasil Rekomendasi</h2>
                    @if ($result)
                        <span
                            class="badge {{ $result['is_agreed'] ? 'badge-agreed' : 'badge-diff' }}"
                            title="Perbandingan Peringkat #1 Fuzzy WP ({{ $result['primary_recommendation']['nama_id'] }}) dengan Peringkat #1 TOPSIS ({{ $result['topsis_top1']['nama_id'] }})"
                        >
                            {{ $result['agreement_status'] }}
                            @if (! $result['is_agreed'])
                                · TOPSIS #1: {{ $result['topsis_top1']['nama_id'] }}
                            @endif
                        </span>
                    @endif
                </div>

                @if (! $result)
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"></path>
                                <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                            </svg>
                        </div>
                        <p style="font-weight:700;color:var(--text-main);margin-bottom:6px;">Belum Ada Analisis</p>
                        <p style="font-size:13.5px;">
                            Masukkan nilai N, P, K, pH tanah, serta koordinat lokasi pada panel kiri lalu klik
                            <strong>Cek Rekomendasi</strong> untuk memeringkat 22 tanaman.
                        </p>
                    </div>
                @else
                    {{-- Peringatan Amber jika ada input di luar rentang dataset --}}
                    @if (! empty($result['range_warnings']))
                        <div class="alert alert-amber" role="status">
                            <div class="alert-title">Peringatan Nilai di Luar Rentang Dataset</div>
                            <ul>
                                @foreach ($result['range_warnings'] as $warning)
                                    <li>{{ $warning['message'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $top1 = $result['primary_recommendation'];
                        $others = array_slice($result['top5_wp'], 1, 4);
                    @endphp

                    {{-- Peringkat #1: Banner Foto Besar --}}
                    <div class="top1-wrapper">
                        <img
                            src="{{ $top1['image_url'] }}"
                            onerror="this.onerror=null;this.src='/images/crops/fallback.svg';"
                            alt="{{ $top1['nama_id'] }} ({{ ucfirst($top1['label']) }})"
                            class="top1-banner"
                        >
                        <div class="top1-overlay">
                            <div class="top1-meta">
                                <span class="rank-tag">#1 Rekomendasi Utama · {{ $top1['kode'] }}</span>
                                <span style="font-size:11.5px;color:var(--text-muted);">Fuzzy WP &amp; TOPSIS</span>
                            </div>
                            <div class="top1-title-row">
                                <h3 class="top1-name">{{ $top1['nama_id'] }} ({{ ucfirst($top1['label']) }})</h3>
                                <button
                                    type="button"
                                    class="btn-detail no-print"
                                    data-crop-code="{{ $top1['kode'] }}"
                                >
                                    Detail
                                </button>
                            </div>
                            <p class="scores-line">
                                TOPSIS: <strong>{{ number_format($top1['V_TOPSIS'], 3) }}</strong>
                                &nbsp;|&nbsp;
                                Fuzzy WP: <strong>{{ number_format($top1['P_WP'], 3) }}</strong>
                                <span style="color:var(--text-soft);">(ln P: {{ number_format($top1['lnP'], 3) }})</span>
                            </p>
                        </div>
                    </div>

                    {{-- Peringkat #2-#5: 4 Kartu Thumbnail --}}
                    <div class="rank-list">
                        @foreach ($others as $crop)
                            <div class="rank-item">
                                <img
                                    src="{{ $crop['image_url'] }}"
                                    onerror="this.onerror=null;this.src='/images/crops/fallback.svg';"
                                    alt="{{ $crop['nama_id'] }} ({{ ucfirst($crop['label']) }})"
                                    class="rank-thumb"
                                >
                                <div class="rank-info">
                                    <div class="rank-sub">Peringkat #{{ $crop['Rank_WP'] }} · {{ $crop['kode'] }}</div>
                                    <div class="rank-name">{{ $crop['nama_id'] }} ({{ ucfirst($crop['label']) }})</div>
                                    <div class="scores-line" style="margin-top:2px;">
                                        TOPSIS: <strong>{{ number_format($crop['V_TOPSIS'], 3) }}</strong>
                                        |
                                        Fuzzy WP: <strong>{{ number_format($crop['P_WP'], 3) }}</strong>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="btn-detail no-print"
                                    data-crop-code="{{ $crop['kode'] }}"
                                >
                                    Detail
                                </button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tautan Semua 22 Tanaman & Tombol Cetak --}}
                    <div class="result-footer no-print">
                        <button type="button" class="footer-link-btn" id="btn-show-all-22">
                            Lihat semua 22 tanaman
                        </button>
                        <button type="button" class="footer-link-btn footer-link-btn-muted" onclick="window.print()">
                            Cetak Hasil
                        </button>
                    </div>

                    {{-- Tabel Lengkap 22 Tanaman untuk Mode Cetak --}}
                    <div class="print-only" style="margin-top: 22px;">
                        <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 8px;">Peringkat Lengkap 22 Tanaman</h3>
                        <table class="modal-table">
                            <thead>
                                <tr>
                                    <th>Rank WP</th>
                                    <th>Kode</th>
                                    <th>Tanaman</th>
                                    <th>P_WP</th>
                                    <th>ln P</th>
                                    <th>V_TOPSIS</th>
                                    <th>Rank TOPSIS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['all_crops'] as $crop)
                                    <tr>
                                        <td>#{{ $crop['Rank_WP'] }}</td>
                                        <td>{{ $crop['kode'] }}</td>
                                        <td>{{ $crop['nama_id'] }} ({{ $crop['label'] }})</td>
                                        <td>{{ number_format($crop['P_WP'], 6) }}</td>
                                        <td>{{ number_format($crop['lnP'], 4) }}</td>
                                        <td>{{ number_format($crop['V_TOPSIS'], 6) }}</td>
                                        <td>#{{ $crop['Rank_TOPSIS'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        @if ($recentHistories->isNotEmpty())
            <section class="history-card no-print" aria-label="Riwayat Rekomendasi Terakhir">
                <div style="font-size:13px;font-weight:700;color:var(--text-main);">Riwayat Analisis Terakhir</div>
                <div class="history-list">
                    @foreach ($recentHistories as $item)
                        <a href="{{ route('home', ['history' => $item->id]) }}" class="history-chip">
                            <strong>{{ $item->top1_wp_nama_id }}</strong>
                            <span style="color:var(--text-muted);">
                                (N:{{ $item->n }}, P:{{ $item->p }}, K:{{ $item->k }}, pH:{{ $item->ph }})
                            </span>
                            <span class="badge {{ $item->agreement_status === 'Sepakat' ? 'badge-agreed' : 'badge-diff' }}" style="padding:1px 7px;font-size:11px;">
                                {{ $item->agreement_status }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    @if ($result)
        {{-- Modal Detail Perbandingan 7 Kriteria --}}
        <div class="modal-backdrop no-print" id="modal-detail" role="dialog" aria-modal="true" aria-labelledby="modal-detail-title">
            <div class="modal-panel">
                <div class="modal-head">
                    <div>
                        <h3 id="modal-detail-title" style="font-size:18px;font-weight:800;">Detail Kriteria Tanaman</h3>
                        <p id="modal-detail-subtitle" style="font-size:13px;color:var(--text-muted);"></p>
                    </div>
                    <button type="button" class="modal-close" data-close-modal="modal-detail" aria-label="Tutup modal">&times;</button>
                </div>

                <div style="display:flex;gap:14px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
                    <img id="modal-detail-img" src="/images/crops/fallback.svg" alt="" style="width:88px;height:66px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                    <div style="display:grid;grid-template-columns:repeat(3,auto);gap:14px;font-size:12.5px;">
                        <div>
                            <div style="color:var(--text-muted);">Fuzzy WP</div>
                            <strong id="modal-detail-wp"></strong>
                        </div>
                        <div>
                            <div style="color:var(--text-muted);">ln P</div>
                            <strong id="modal-detail-lnp"></strong>
                        </div>
                        <div>
                            <div style="color:var(--text-muted);">TOPSIS</div>
                            <strong id="modal-detail-topsis"></strong>
                        </div>
                    </div>
                </div>

                <div class="table-scroll">
                    <table class="modal-table">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <th>Input Aktual</th>
                                <th>Profil Ideal (μ ± σ)</th>
                                <th>Bobot RF</th>
                                <th>Kesesuaian (S<sub>ij</sub>)</th>
                            </tr>
                        </thead>
                        <tbody id="modal-detail-rows"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal Semua 22 Tanaman --}}
        <div class="modal-backdrop no-print" id="modal-all-crops" role="dialog" aria-modal="true" aria-labelledby="modal-all-title">
            <div class="modal-panel modal-panel-wide">
                <div class="modal-head">
                    <div>
                        <h3 id="modal-all-title" style="font-size:18px;font-weight:800;">Peringkat Lengkap 22 Tanaman</h3>
                        <p style="font-size:13px;color:var(--text-muted);">
                            Diurutkan berdasarkan nilai Fuzzy WP (P<sub>i</sub>), disertai skor dan peringkat TOPSIS (V<sub>i</sub>).
                        </p>
                    </div>
                    <button type="button" class="modal-close" data-close-modal="modal-all-crops" aria-label="Tutup modal">&times;</button>
                </div>

                <div class="table-scroll">
                    <table class="modal-table">
                        <thead>
                            <tr>
                                <th>Rank WP</th>
                                <th>Kode</th>
                                <th>Tanaman</th>
                                <th>Fuzzy WP (P<sub>i</sub>)</th>
                                <th>ln P<sub>i</sub></th>
                                <th>TOPSIS (V<sub>i</sub>)</th>
                                <th>Rank TOPSIS</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result['all_crops'] as $crop)
                                <tr>
                                    <td><strong>#{{ $crop['Rank_WP'] }}</strong></td>
                                    <td>{{ $crop['kode'] }}</td>
                                    <td>{{ $crop['nama_id'] }} <span style="color:var(--text-muted);">({{ ucfirst($crop['label']) }})</span></td>
                                    <td>{{ number_format($crop['P_WP'], 6) }}</td>
                                    <td>{{ number_format($crop['lnP'], 4) }}</td>
                                    <td>{{ number_format($crop['V_TOPSIS'], 6) }}</td>
                                    <td>#{{ $crop['Rank_TOPSIS'] }}</td>
                                    <td>
                                        <button type="button" class="btn-detail" data-crop-code="{{ $crop['kode'] }}">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            window.__SPK_CROPS__ = @json(collect($result['all_crops'])->keyBy('kode'));
        </script>
    @endif

    <script>
        (function () {
            const geoBtn = document.getElementById('btn-geolocate');
            const latInput = document.getElementById('input-latitude');
            const lonInput = document.getElementById('input-longitude');
            const weatherTempEl = document.getElementById('weather-temp-val');
            const weatherHumEl = document.getElementById('weather-hum-val');
            const weatherRainEl = document.getElementById('weather-rain-val');
            const weatherStatusEl = document.getElementById('weather-preview-status');
            const weatherEndpoint = @json(route('weather.preview', [], false));

            let weatherTimer = null;
            let weatherAbortController = null;
            let lastFetchedCoords = null;

            function normalizeCoord(val) {
                return val.trim().replace(',', '.');
            }

            function isValidCoords(latStr, lonStr) {
                if (latStr === '' || lonStr === '') return false;
                const lat = Number(latStr);
                const lon = Number(lonStr);
                return Number.isFinite(lat) && Number.isFinite(lon) && lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180;
            }

            function fetchWeatherPreview(force) {
                if (!latInput || !lonInput || !weatherTempEl || !weatherHumEl || !weatherRainEl) return;

                const latVal = normalizeCoord(latInput.value);
                const lonVal = normalizeCoord(lonInput.value);

                if (!isValidCoords(latVal, lonVal)) {
                    lastFetchedCoords = null;
                    if (weatherStatusEl) {
                        weatherStatusEl.textContent = 'Isi koordinat Lintang (-90..90) & Bujur (-180..180) untuk memuat cuaca Open-Meteo.';
                    }
                    return;
                }

                const coordKey = latVal + ',' + lonVal;
                if (!force && coordKey === lastFetchedCoords) {
                    return;
                }

                if (weatherAbortController) {
                    weatherAbortController.abort();
                }
                weatherAbortController = new AbortController();

                if (weatherStatusEl) {
                    weatherStatusEl.textContent = 'Memuat data cuaca Open-Meteo...';
                }

                const url = weatherEndpoint + '?latitude=' + encodeURIComponent(latVal) + '&longitude=' + encodeURIComponent(lonVal);

                fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: weatherAbortController.signal,
                })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            if (!res.ok) {
                                throw new Error(data.message || 'Gagal memuat data cuaca.');
                            }
                            return data;
                        });
                    })
                    .then(function (data) {
                        lastFetchedCoords = coordKey;
                        weatherTempEl.textContent = data.temperature_formatted;
                        weatherHumEl.textContent = data.humidity_formatted;
                        weatherRainEl.textContent = data.rainfall_formatted;
                        if (weatherStatusEl) {
                            weatherStatusEl.textContent = 'Data cuaca Open-Meteo siap digunakan untuk perhitungan.';
                        }
                    })
                    .catch(function (err) {
                        if (err.name === 'AbortError') return;
                        lastFetchedCoords = null;
                        if (weatherStatusEl) {
                            weatherStatusEl.textContent = err.message || 'Gagal memuat pratinjau cuaca Open-Meteo.';
                        }
                    });
            }

            function scheduleWeatherPreview() {
                clearTimeout(weatherTimer);
                weatherTimer = setTimeout(function () {
                    fetchWeatherPreview(false);
                }, 400);
            }

            if (latInput && lonInput) {
                const hasServerResult = @json((bool) $result);
                latInput.addEventListener('input', scheduleWeatherPreview);
                lonInput.addEventListener('input', scheduleWeatherPreview);
                latInput.addEventListener('change', function () { fetchWeatherPreview(false); });
                lonInput.addEventListener('change', function () { fetchWeatherPreview(false); });
                latInput.addEventListener('blur', function () { fetchWeatherPreview(false); });
                lonInput.addEventListener('blur', function () { fetchWeatherPreview(false); });

                if (!hasServerResult && isValidCoords(normalizeCoord(latInput.value), normalizeCoord(lonInput.value))) {
                    fetchWeatherPreview(true);
                }
            }

            if (geoBtn && latInput && lonInput && navigator.geolocation) {
                geoBtn.addEventListener('click', function () {
                    geoBtn.textContent = 'Mengambil GPS...';
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            latInput.value = pos.coords.latitude.toFixed(4);
                            lonInput.value = pos.coords.longitude.toFixed(4);
                            geoBtn.textContent = 'Gunakan GPS Perangkat';
                            fetchWeatherPreview(true);
                        },
                        function () {
                            geoBtn.textContent = 'GPS tidak tersedia';
                        }
                    );
                });
            }

            const spkForm = document.getElementById('spk-form');
            const submitBtn = document.getElementById('btn-submit');
            const resetBtn = document.getElementById('btn-reset-form');
            const homeUrl = @json(route('home', [], false));
            const hasResultOnPage = @json((bool) $result);

            if (resetBtn && spkForm) {
                resetBtn.addEventListener('click', function () {
                    clearTimeout(weatherTimer);
                    lastFetchedCoords = null;
                    if (weatherAbortController) {
                        weatherAbortController.abort();
                    }

                    if (hasResultOnPage || window.location.pathname !== homeUrl || window.location.search !== '') {
                        window.location.assign(homeUrl);
                        return;
                    }

                    ['input-N', 'input-P', 'input-K', 'input-ph', 'input-latitude', 'input-longitude'].forEach(function (id) {
                        const input = document.getElementById(id);
                        if (input) {
                            input.value = '';
                            input.classList.remove('is-invalid');
                        }
                    });

                    spkForm.querySelectorAll('.field-error').forEach(function (el) {
                        el.remove();
                    });

                    if (weatherTempEl) weatherTempEl.textContent = '-- °C';
                    if (weatherHumEl) weatherHumEl.textContent = '-- %';
                    if (weatherRainEl) weatherRainEl.textContent = '-- mm';
                    if (weatherStatusEl) {
                        weatherStatusEl.textContent = 'Isi koordinat Lintang & Bujur atau gunakan GPS untuk memuat data cuaca.';
                    }

                    const firstInput = document.getElementById('input-N');
                    if (firstInput) firstInput.focus();
                });
            }

            if (spkForm && submitBtn) {
                spkForm.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.querySelector('span').textContent = 'Memproses...';
                });
            }

            const detailModal = document.getElementById('modal-detail');
            const allCropsModal = document.getElementById('modal-all-crops');
            const cropsMap = window.__SPK_CROPS__ || {};

            function openModal(el) {
                if (el) el.classList.add('is-open');
            }

            function closeModal(el) {
                if (el) el.classList.remove('is-open');
            }

            function renderCropDetail(kode) {
                const crop = cropsMap[kode];
                if (!crop || !detailModal) return;

                document.getElementById('modal-detail-title').textContent = crop.nama_id + ' (' + crop.label.charAt(0).toUpperCase() + crop.label.slice(1) + ')';
                document.getElementById('modal-detail-subtitle').textContent = 'Kode Alternatif: ' + crop.kode + ' · Perbandingan 7 Kriteria Lahan & Cuaca';

                const img = document.getElementById('modal-detail-img');
                img.src = crop.image_url;
                img.onerror = function () {
                    this.onerror = null;
                    this.src = '/images/crops/fallback.svg';
                };

                document.getElementById('modal-detail-wp').textContent = Number(crop.P_WP).toFixed(6) + ' (#' + crop.Rank_WP + ')';
                document.getElementById('modal-detail-lnp').textContent = Number(crop.lnP).toFixed(4);
                document.getElementById('modal-detail-topsis').textContent = Number(crop.V_TOPSIS).toFixed(6) + ' (#' + crop.Rank_TOPSIS + ')';

                const tbody = document.getElementById('modal-detail-rows');
                tbody.innerHTML = '';

                Object.values(crop.criteria).forEach(function (item) {
                    const tr = document.createElement('tr');
                    const unit = item.unit ? ' ' + item.unit : '';
                    const pct = Math.max(0, Math.min(100, item.suitability * 100));

                    tr.innerHTML =
                        '<td><strong>' + item.label + '</strong></td>' +
                        '<td>' + Number(item.input).toFixed(2) + unit + '</td>' +
                        '<td>' + Number(item.mu).toFixed(2) + ' &plusmn; ' + Number(item.sd).toFixed(2) + unit + '</td>' +
                        '<td>' + (Number(item.weight) * 100).toFixed(2) + '%</td>' +
                        '<td style="white-space:nowrap;"><span class="suit-bar"><span class="suit-fill" style="width:' + pct.toFixed(1) + '%"></span></span><strong>' + pct.toFixed(1) + '%</strong> <span style="color:var(--text-muted);font-size:11.5px;">(' + Number(item.suitability).toFixed(4) + ')</span></td>';

                    tbody.appendChild(tr);
                });

                openModal(detailModal);
            }

            document.querySelectorAll('[data-crop-code]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    renderCropDetail(this.getAttribute('data-crop-code'));
                });
            });

            const showAllBtn = document.getElementById('btn-show-all-22');
            if (showAllBtn && allCropsModal) {
                showAllBtn.addEventListener('click', function () {
                    openModal(allCropsModal);
                });
            }

            document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    closeModal(document.getElementById(this.getAttribute('data-close-modal')));
                });
            });

            [detailModal, allCropsModal].forEach(function (modal) {
                if (!modal) return;
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal(modal);
                });
            });
        })();
    </script>
</body>
</html>
