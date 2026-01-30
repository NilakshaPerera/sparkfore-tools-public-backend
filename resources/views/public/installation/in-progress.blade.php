@extends('public.sparkfore-template')

@section("extra-css")
<style>
    .loader {
        width: 100px;
        height: 70px;
        margin: 50px auto;
        position: relative;
    }

    .loader span {
        display: block;
        width: 5px;
        height: 10px;
        background: #e43632;
        position: absolute;
        bottom: 0;
        animation: loading-1 2.25s infinite ease-in-out;
    }

    .loader span:nth-child(2) {
        left: 11px;
        animation-delay: .2s;
    }

    .loader span:nth-child(3) {
        left: 22px;
        animation-delay: .4s;
    }

    .loader span:nth-child(4) {
        left: 33px;
        animation-delay: .6s;
    }

    .loader span:nth-child(5) {
        left: 44px;
        animation-delay: .8s;
    }

    .loader span:nth-child(6) {
        left: 55px;
        animation-delay: 1s;
    }

    .loader span:nth-child(7) {
        left: 66px;
        animation-delay: 1.2s;
    }

    .loader span:nth-child(8) {
        left: 77px;
        animation-delay: 1.4s;
    }

    .loader span:nth-child(9) {
        left: 88px;
        animation-delay: 1.6s;
    }

    @-webkit-keyframes loading-1 {
        0% {
            height: 10px;
            transform: translateY(0px);
            background: #ff4d80;
        }

        25% {
            height: 60px;
            transform: translateY(15px);
            background: #3423a6;
        }

        50% {
            height: 10px;
            transform: translateY(-10px);
            background: #e29013;
        }

        100% {
            height: 10px;
            transform: translateY(0px);
            background: #e50926;
        }
    }

    @keyframes loading-1 {
        0% {
            height: 10px;
            transform: translateY(0px);
            background: #ff4d80;
        }

        25% {
            height: 60px;
            transform: translateY(15px);
            background: #3423a6;
        }

        50% {
            height: 10px;
            transform: translateY(-10px);
            background: #e29013;
        }

        100% {
            height: 10px;
            transform: translateY(0px);
            background: #e50926;
        }
    }

    .text-center {
        text-align: center;
    }
</style>
@endsection("extra-css")

@section("content")
<div id="solution">
    <div class="feature-area">
        <div class="container">
            <h1>{{ __("sparkfore.installation.in_progress.title") }}</h1>

        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <h3 class="text-center">{{ __("sparkfore.installation.in_progress.message") }}</h3>
        <div class="col-md-12 mb-5">
            <div class="loader">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="text-center mb-5" id="progress-bar"></div>
        </div>
    </div>
</div>

@endsection


@section("extra-js")
<script>
    const totalTime = 6 * 60 * 1000; // 6 minutes in milliseconds
        let currentProgress = 0;
        const progressBar = document.getElementById("progress-bar");

        // Function to update progress
        function updateProgress() {
            if (currentProgress < 100) {
                currentProgress++; // Increase by 1% each time
                progressBar.textContent = `{{ __("sparkfore.installation.in_progress.deploying") }}: ${currentProgress}%`;
            }
        }

        // Function to handle random time intervals
        function startProgress() {
            let remainingTime = totalTime; // Total time left for the progress (6 minutes)
            let intervalsLeft = 100; // We need to reach 100%

            // Start the progress, increasing by 1% with random delays
            function nextStep() {
                if (currentProgress < 100) {
                    const randomDelay = Math.random() * (remainingTime / intervalsLeft); // Random delay
                    setTimeout(() => {
                        updateProgress();
                        remainingTime -= randomDelay; // Decrease the remaining time by the random delay
                        intervalsLeft--; // Decrease the number of intervals left
                        nextStep(); // Call the next step
                    }, randomDelay);
                } else {
                    // Once 100% is reached, redirect to Laravel route
                    window.location.href = "{{$installationUrl}}";
                }
            }

            // Start the process
            nextStep();
        }

        // Initialize the progress update
        startProgress();

</script>

@endsection
