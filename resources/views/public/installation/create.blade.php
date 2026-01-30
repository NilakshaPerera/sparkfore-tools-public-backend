@extends('public.sparkfore-template')

@section('content')

    <!--
        <div id="solution">
            <div class="feature-area">
                <div class="container">
                    <h1>{{ __('sparkfore.installation.create.title') }}</h1>
                    <h3 class="text-center fw-600 mt-3">{{ __('sparkfore.installation.create.title_description') }}</h3>
                </div>
            </div>
        </div>
    -->
    <!--
        <div id="reliable-partner" class="position-relative">
            <div class="container">
                <h1>{{ __('sparkfore.installation.create.reliable_partner') }}</h1>
                <p>{{ __('sparkfore.installation.create.reliable_partner_tag_1') }}</p>
                <p>{{ __('sparkfore.installation.create.reliable_partner_tag_2') }}</p>
            </div>
        </div>

        <div id="reliable-partner" class="position-relative">
            <div class="container">
                <h1>{{ __('sparkfore.installation.create.create_free_moodle_page') }}</h1>
                <p>{{ __('sparkfore.installation.create.title_description') }}</p>
            </div>
        </div>
    -->

    <div id="hero">
        <video id="hero-bg" autoplay="" muted="" loop="">
            <source src="https://sparkfore.com/media/ujko1q3g/sparkle-smallest.mp4" type="video/mp4">
        </video>
        <div class="hero-text">
            <h1>{{ __('sparkfore.installation.create.create_free_moodle_page') }}</h1>
            <p>{{ __('sparkfore.installation.create.title_description') }}</p>
        </div>
    </div>

    <div id="reliable-partner-logos" class="position-relative">
        <div class="floating-moodle-partner">
            <img style="width:150px;" src="{{ url("/img/Moodle_Partner_logo.png") }}" alt="">
        </div>
        <h2 class="mb-4">{{ __('sparkfore.installation.create.reliable_partner') }}</h2>
        <div class="container">
            <img src="https://sparkfore.com/media/mzyh5zbs/loggor.png?rmode=max&width=1024">
       </div>
    </div>

    <div class="container free-form mb-5">
        <section class="row">
            <form method="POST" action="{{ route('public.create-installation', ['locale' => app()->getLocale()]) }}">
                @csrf
                <h2 class="text-center">{{ __('sparkfore.installation.create.your_free_site') }}</h2>
                <div class="mb-3">
                    <label for="siteName" class="form-label">
                        <span class="text-danger">*</span>
                        {{ __('sparkfore.installation.create.choose_site_name') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('siteName') is-invalid @enderror" id="siteName"
                            name="siteName" required value="{{ old('siteName', request()->get('siteName')) }}">
                        <span class="input-group-text">.{{ SPARKFORE_DOMAIN }}</span>
                    </div>
                    @error('siteName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label"><span class="text-danger">*</span>
                            {{ __('sparkfore.installation.create.password') }}</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="confirmPassword" class="form-label"><span class="text-danger">*</span>
                            {{ __('sparkfore.installation.create.confirm_password') }}</label>
                        <input type="password" class="form-control @error('confirmPassword') is-invalid @enderror"
                            id="confirmPassword" name="password_confirmation" required>
                        @error('confirmPassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="my-4">
                    <h3 class="text-center mt-5">{{ __('sparkfore.installation.create.your_details') }}</h3>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="firstName" class="form-label"><span class="text-danger">*</span>
                            {{ __('sparkfore.installation.create.first_name') }}</label>
                        <input type="text" class="form-control @error('firstName') is-invalid @enderror" id="firstName"
                            name="firstName" required value="{{ old('firstName') }}">
                        @error('firstName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="lastName" class="form-label"><span class="text-danger">*</span>
                            {{ __('sparkfore.installation.create.last_name') }}</label>
                        <input type="text" class="form-control @error('lastName') is-invalid @enderror" id="lastName"
                            name="lastName" required value="{{ old('lastName') }}">
                        @error('lastName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label"><span class="text-danger">*</span>
                            {{ __('sparkfore.installation.create.email') }}</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" required value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label"> {{ __('sparkfore.installation.create.phone') }}</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                            name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input @error('terms_and_conditions') is-invalid @enderror" type="checkbox"
                            id="terms_and_conditions" name="terms_and_conditions" required>
                        <label class="form-check-label" for="terms_and_conditions">
                            {{ __('sparkfore.installation.create.agree') }}
                            <a href="{{ asset('pdf/Cookies Policy.pdf') }}"
                                target="_blank">{{ __('sparkfore.installation.create.cookies_policy') }}</a> ,
                            <a href="{{ asset('pdf/Data Processor.pdf') }}"
                                target="_blank">{{ __('sparkfore.installation.create.data_processor') }}</a> &
                            <a href="{{ asset('pdf/Sparkfore - General Terms ver 1.4.pdf') }}"
                                target="_blank">{{ __('sparkfore.installation.create.general_terms') }}</a>.
                        </label>
                        @error('terms_and_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <img class="captcha-img" src="{{ captcha_get_src() }}" alt="captcha" id="captcha-image">
                        </div>
                        <div class="col-md-9 col-lg-6">
                            <div class="input-group">
                                <input type="text" class="form-control @error('captcha') is-invalid @enderror" id="captcha"
                                    name="captcha" placeholder="{{ __('sparkfore.installation.create.captcha_placeholder') }}" required>
                                <button type="button" class="btn btn-secondary captcha-refresh-btn"
                                    title="Refresh captcha">{{ __('sparkfore.installation.create.captcha_refresh') }}</button>
                            </div>
                            @error('captcha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                
                <div class="mb-3 text-right">
                    <button type="submit" class="btn">{{ __('sparkfore.installation.create.register_button_text') }}</button>
                </div>
            </form>


        </section>
    </div>



    <div id="contact-us" class="mt-6">
        <div class="container">
            <div class="row">

                <div class="contact-info">
                    <h2>{{ __('sparkfore.installation.contact.title') }}</h2>
                    <a name="contact"></a>
                    <div class="row">
                        <div class="col-6 callout">
                            <h4>Anna Elvnejd</h4>
                            <p>
                                <a href="tel:+46104924322" class="umami--click--contact-phone">+46 10 492 43 22</a><br>
                            </p>
                        </div>
                        <div class="col-6 callout">
                            <h4>Anders Stenmark</h4>
                            <p>
                                <a href="tel:+46104924325" class="umami--click--contact-phone">+46 10 492 43 25</a><br>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 callout">
                            <h4>{{ __('sparkfore.installation.contact.send_mail') }}</h4>
                            <p>
                                <a href="mailto:hello@sparkfore.com"
                                    class="umami--click--contact-mail">hello@sparkfore.com</a>
                            </p>
                        </div>
                    </div>
                    <!--
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn plausible-event-name=Contact+Form+Shown"
                                data-bs-toggle="modal" data-bs-target="#contact-modal">Contact us</button>
                        </div>
                    </div>
                    -->
                </div>

                <div class="contact-image">
                    <div class="bullseye-container">
                        <div class="top-left-bullseye-clip-outer">
                            <picture>
                                <source type="image/webp" srcset="https://sparkfore.com/media/qqnaobmm/anders_anna.jpeg?rmode=max&width=580&height=690">
                                <source type="image/jpeg" srcset="https://sparkfore.com/media/qqnaobmm/anders_anna.jpeg?rmode=max&width=580&height=690">
                                <img src="https://sparkfore.com/media/qqnaobmm/anders_anna.jpeg?rmode=max&width=580&height=690" alt="Anders &amp; Anna">
                            </picture>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('extra-js')
    
            <script>
                document.querySelector('.captcha-refresh-btn').addEventListener('click', function() {
                    var timestamp = new Date().getTime();
                    var captchaUrl = '/captcha?' + timestamp;

                    // Update the image src with the new URL to refresh the CAPTCHA
                    document.getElementById('captcha-image').src = captchaUrl;
                });
            </script>

        
@endsection
