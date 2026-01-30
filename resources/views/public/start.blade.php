@extends('public.sparkfore-template')

@section("extra-css")
    <style>
        .transparent-box {
            /* White background with 50% opacity */
            background-color: rgba(255, 255, 255, 0.5);
            color: black;
            padding-top: 20px;
            padding-bottom: 50px;
        }

        #hero-banner {
            background-image: url('/img/hero.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 75vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
        }
        .site-name-input{
            margin-right: 25px;
            margin-left: 25px;
        }

        @media screen and (max-width: 768px) {
            .submit-btn{
                margin-top: 25px;
            }
            .transparent-box{
                padding: 40px;
            }
        }
    </style>
@endsection("extra-css")

@section("content")

<div id="hero-banner">
<div style="position: absolute; top: 20px; right: 20px;">
        <img style="width:150px;" src="{{ url("/img/Moodle_Partner_logo.png") }}" alt="">
    </div>
        <div class="container transparent-box">
            <h3>{{ __('sparkfore.start.hero_banner.title') }}</h3>
            <h5>{{ __('sparkfore.start.hero_banner.subtitle') }}</h5>
            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-10">
                    <form action="{{ route('public.pricing', ["locale" => app()->getLocale()]) }}" method="GET">
                        <div class="row align-items-center">
                            <div class="col-md-8 col-lg-9 d-flex align-items-center">
                                <span class="">https://</span>
                                <input type="text" class="form-control site-name-input"  placeholder="{{ __('sparkfore.start.hero_banner.form.placeholder') }}" aria-label="Domain" name="siteName">
                                <span class="">.sparkfore.com</span>
                            </div>
                            <span class="col-md-4 col-lg-3 align-items-center">
                                <button class="btn mt0 col-xs-12 submit-btn" type="submit">{{ __('sparkfore.start.hero_banner.form.button') }}</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="container mtb-40">
        <div class="row">
            <div class="col-md-6">
                <h3>{{ __('sparkfore.start.why_choose_us.title') }}</h3>
                <strong>{{ __('sparkfore.start.why_choose_us.content.european_cloud_title') }}</strong>
                <span>{{ __('sparkfore.start.why_choose_us.content.european_cloud_text') }}</span>
                <br><br>
                <strong>{{ __('sparkfore.start.why_choose_us.content.quick_and_carefree_title') }}</strong>
                <span>{{ __('sparkfore.start.why_choose_us.content.quick_and_carefree_text') }}</span>
            </div>
            <div class="col-md-6">
                <h3>{{ __('sparkfore.start.safe_and_powerful_solution.title') }}</h3>
                <p>
                    {{ __('sparkfore.start.safe_and_powerful_solution.content') }}
                </p>
            </div>
        </div>
    </div>


@endsection


@section("extra-js")
@endsection
