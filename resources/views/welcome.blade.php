@extends('layouts.app')
@vite (['resources/css/style.css', 'resources/js/style.js'])
@vite (['resources/css/history.css'])
@section('content')

    <!-- ========== HOME ========== -->
    <section class="view view-home" data-view="home" aria-labelledby="homeHeading">
        <div class="hero">
            <img src="{{ asset('images/hero.png') }}" alt="dental banner" class="hero-img" />
            <div class="hero-text">
                <h2 id="homeHeading">Welcome back <span
                        id="welcomeName">{{ auth()->check() ? auth()->user()->name : '—' }}</span> !</h2>
            </div>
        </div>

        <div class="home-body">
            <p class="lead mt-5">
                This online, AI-powered tool is designed to provide dental students with prompt and personalized feedback on
                their Working Length (WL) radiograph submissions—a critical technical stage in root canal treatment. Upon
                submission, the tool automatically evaluates the radiograph to determine whether the working length is
                optimal, overextended, or underextended, delivering instant automated feedback to support student learning
                and skill development.
            </p>

            <div class="home-actions">
                <a href="/upload" class="btn primary">try it</a>
            </div>
        </div>
    </section>

    <!-- ========== UPLOAD ========== -->
    <section class="view view-upload" data-view="upload" hidden aria-labelledby="uploadHeading">
        <h2 class="section-title" id="uploadHeading">Upload</h2>

        <div class="uploader" id="dropZone">
            <div class="uploader-inner">
                <div class="uploader-icon">⬆️</div>
                <p>Drag & drop dental X-ray here, or</p>
                <label class="btn outline">
                    Browse
                    <input type="file" id="fileInput" accept="image/*,.png,.jpg,.jpeg,.tif,.tiff,.dcm" hidden />
                </label>
            </div>
        </div>

        <div id="uploadStatus" class="upload-status" role="status" aria-live="polite"></div>
    </section>

    <!-- ========== HISTORY ========== -->
    <section class="view view-history" data-view="history" hidden aria-labelledby="historyHeading">
        <section class="history-wrap">
            <div class="history-head">
                <div class="lead">
                    <p>
                        View all your uploaded cases, along with the evaluation results and feedback
                        provided. This website helps you track your progress, reflect on your
                        performance, and improve your clinical skills over time.
                    </p>
                </div>

                <button class="icon-btn" id="filterBtn" aria-haspopup="dialog" aria-controls="filterDialog"
                    aria-expanded="false" title="Filter">
                    <svg viewBox="0 0 24 24" width="22" height="22">
                        <path d="M4 7h16M7 7v6m0 0h10m-6 0v4" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <!-- Cards grid -->
            <div class="cards" id="cards"></div>
        </section>

        <!-- Filter Dialog -->
        <dialog id="filterDialog" class="filter-dialog" aria-label="Filter by">
            <div class="filter-content">
                <h3>Filter by :</h3>
                <div class="chips">
                    <button class="chip" data-filter="over">over extended</button>
                    <button class="chip" data-filter="under">under extended</button>
                    <button class="chip" data-filter="optimal">optimal</button>
                </div>

                <div class="filter-actions">
                    <button class="btn outline" id="clearFilter">Clear filter</button>
                    <button class="btn primary" id="closeFilter">Done</button>
                </div>
            </div>
        </dialog>
    </section>
@endsection