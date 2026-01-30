@extends('public.sparkfore-template')

@section("extra-css")
    <style>
        #pricing .product-list .product {
            flex: none;
            width: 90%;
            margin-left: 15px;
            margin-right: 15px;
        }

        @media (min-width: 768px) {
            #pricing .product-list .product {
                flex: none;
                width: 40%;
                margin-left: 10px;
                margin-right: 10px;
            }
        }

        @media (min-width: 992px) {
            #pricing .product-list .product {
                flex: none;
                width: 20%;
            }

            #pricing .product-list .product:not(:last-child) {
                margin-right: 8px;
            }
        }
    </style>
@endsection("extra-css")

@section("content")
    <div id="pricing">
        <div class="pricing-text container">
            <h1>{{ __('sparkfore.pricing.title') }}</h1>
            <p>{{ __('sparkfore.pricing.subtitle') }}</p>
            <p>{{ __('sparkfore.pricing.select_plan') }}</p>
        </div>
        <div class="product-list">
            <div class="">
                <div class="row justify-content-center">
                    @foreach (__('sparkfore.pricing.plans') as $key => $plan)
                                <div class="product">
                                    <h3>{{ $plan['title'] }}</h3>
                                    <p class="price">{{ $plan['price'] }}</p>
                                    <p class="description">{!! $plan['description'] !!}</p>
                                    <p>
                                        @if ($key == 'free')
                                                            <a class="btn plausible-event-name=Clicked+Buy+Now" href="{{ route('public.create-installation.view', [
                                                "locale" => app()->getLocale(),
                                                'siteName' => request()->get('siteName', "")
                                            ]) }}">{{ $plan['button'] }}</a>
                                        @else
                                            <a class="btn plausible-event-name=Clicked+Buy+Now" data-bs-toggle="modal"
                                                data-bs-target="#contact-modal">{{ $plan['button'] }}</a>
                                        @endif
                                    </p>
                                    <ul>
                                        @foreach ($plan['features'] as $feature)
                                            <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade show" id="contact-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-modal="true"
        role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="modal-image">
                            <picture>
                                <source type="image/webp" srcset="https://sparkfore.com/img/contact-modal.webp">
                                <source type="image/jpeg" srcset="https://sparkfore.com/img/contact-modal.jpeg">
                                <img src="https://sparkfore.com/img/contact-modal.jpeg" alt="Contact us">
                            </picture>
                        </div>
                        <div class="modal-form">
                            <h3>{{ __("sparkfore.contact_modal.title") }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            <form id="contact-us-form" class="plausible-event-name=Contact+Form+Sent">
                                <input type="text" placeholder="{{ __("sparkfore.contact_modal.form.first_name") }}"
                                    name="firstname">
                                <input type="text" placeholder="{{ __("sparkfore.contact_modal.form.last_name") }}"
                                    name="lastname">
                                <input type="email" placeholder="{{ __("sparkfore.contact_modal.form.email") }}"
                                    name="email">
                                <textarea placeholder="{{ __("sparkfore.contact_modal.form.message") }}"
                                    name="message"></textarea>
                                <button type="submit" class="btn">{{ __("sparkfore.contact_modal.form.button") }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection


@section("extra-js")
    <script>
        document.getElementById('contact-us-form').addEventListener('submit', function (event) {
            event.preventDefault();

            let form = event.target;
            let formData = new FormData(form);

            fetch('{{ route("public.contact.submit", ["locale" => app()->getLocale()]) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(({ status, body }) => {
                    let messageContainer = document.createElement('div');
                    messageContainer.classList.add('alert');

                    if (status === 200 && body.success) {
                        messageContainer.classList.add('alert-success');
                        messageContainer.textContent = '{{ __("sparkfore.contact_modal.form.success_message") }}';
                        form.reset();
                    } else {
                        messageContainer.classList.add('alert-danger');
                        let errorMessage = body.errors.message;
                        messageContainer.textContent = errorMessage;
                    }

                    document.querySelector('.modal-form').prepend(messageContainer);
                    setTimeout(() => messageContainer.remove(), 5000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    let messageContainer = document.createElement('div');
                    messageContainer.classList.add('alert', 'alert-danger');
                    messageContainer.textContent = '{{ __("sparkfore.contact_modal.form.error_message") }}';
                    document.querySelector('.modal-form').prepend(messageContainer);
                    setTimeout(() => messageContainer.remove(), 5000);
                });
        });
    </script>
@endsection
