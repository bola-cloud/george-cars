@extends('layouts.admin')

@section('content')

<div class="card" style="height:80vh !important; position: relative; overflow: hidden;">
    <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
        <div class="text-center">
            <h1 class="mb-3">{{ __('lang.welcome_dashboard') }}</h1>
            <p class="fs-4">{{ __('lang.dashboard_message') }}</p>
        </div>
    </div>

    <!-- Celebration Confetti -->
    <div id="confetti-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></div>
</div>

<!-- Include Confetti JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confettiContainer = document.getElementById('confetti-container');

        // Generate Confetti
        function generateConfetti() {
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
                confettiContainer.appendChild(confetti);
            }
        }

        generateConfetti();
    });
</script>

<!-- Styles for Confetti -->
<style>
    .confetti {
        position: absolute;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        opacity: 0.8;
        animation: confetti-fall 3s ease-in-out infinite;
    }

    @keyframes confetti-fall {
        0% {
            transform: translateY(-100px) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
</style>
@endsection
