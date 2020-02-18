@extends('layouts.app')

@section ('content')
<script src="https://js.stripe.com/v3/"> </script>

        <div class="container">
            <h1 class="checkout-heading stylish-heading"> Checkout </h1>
            <div class="checkout-section">
                <div>
                    <form action="#" id="payment-form">
                        <h2> Billing Details </h2>
                        
                            <div class="form-group">
                            <label for="email"> Email Address </label>
                            <input type="email"  class="form-control" id="email" name="email" value="">
                        </div>

                        <div class="form-group">
                            <label for="name"> Name </label>
                            <input type="text"  class="form-control" id="name" name="name" value="">
                        </div>

                        <div class="form-group">
                            <label for="address"> Address </label>
                            <input type="text"  class="form-control" id="address" name="address" value="">
                        </div>

                        <div class="half-form">
                            <label for="city"> City </label>
                            <input type="text"  class="form-control" id="city" name="city" value="">
                        </div>
                        
                        <div class="form-form">
                            <label for="postcode"> Postcode </label>
                            <input type="text"  class="form-control" id="postcode" name="postcode" value="">
                        </div>
                        
                        <div class="form-form">
                            <label for="phone"> Phone </label>
                            <input type="text"  class="form-control" id="phone" name="phone" value="">
                        </div>

                        <div class="spacer"></div>

                        <h2> Payment Details </h2>

                        <div class="form-group">
                            <label for="name_on_card"> Name on Card </label>
                            <input type="text"  class="form-control" id="name_on_card" name="name_on_card" value="">
                        </div>

                        <div class="form-group">
                            <label for="address"> Address </label>
                            <input type="text"  class="form-control" id="address" name="address" value="">
                        </div>

                        {{-- STRIPE payment method input fields for the user card details --}}
                        <div class="form-group">
                            <label for="card-element">
                                Credit or debit card
                            </label>
                            <div id="card-element">
                                <!-- A Stripe Element will be inserted here. -->
                            </div>
                        
                            <!-- Used to display form errors. -->
                            <div id="card-errors" role="alert"></div>
                        </div> 

                        {{-- <div class="form-group">
                            <label for="cc-number"> Credit Card Number </label>
                            <input type="text"  class="form-control" id="cc-number" name="cc-number" value="">
                        </div> --}}

                        {{-- start of half form  --}}
                        {{-- <div class="half-form">
                           
                            <div class="form-group">
                                <label for="expiry"> Expiry </label>
                                <input type="text"  class="form-control" id="expiry" name="expiry" value="">
                            </div>

                            
                            <div class="form-group">
                                <label for="cvc"> CVC Code </label>
                                <input type="text"  class="form-control" id="cvc" name="cvc" value="">
                            </div>
                        </div>  --}}
                      

                        <div class="spacer"></div>

                        <button type="submit" class="button-primary full-width"> Complete Purchase </button>
                        Price: {{$request->charge}}
                    </form>
                </div>

                <div class="checkout-table-container">
                    
                </div>

            </div>
        </div>
@endsection

@section('extra-js')
    <script> 
        (function(){
                        // Create a Stripe client.
            var stripe = Stripe('pk_test_h3CcIJurHplCOZu6M1RaFoGr00wGJMcAIS');

            // Create an instance of Elements.
            var elements = stripe.elements();

            // Custom styling can be passed to options when creating an Element.
            // (Note that this demo uses a wider set of styles than the guide below.)
            var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
            };

            // Create an instance of the card Element.
            var card = elements.create('card', {
                style: style,
                hidePostalCode: true
                });

            // Add an instance of the card Element into the `card-element` <div>.
            card.mount('#card-element');

            // Handle real-time validation errors from the card Element.
            card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
            });

            // Handle form submission.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
                }
            });
            });

            // Submit the form with the token ID.
            function stripeTokenHandler(token) {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);

            // Submit the form
            //form.submit();
            }
        })();
    </script>
@endsection