@extends('layouts.app')

@section ('content')
     
    <div class="checkout-section my-5">
        <div>
        <form action="{{route('checkouts.update',$request->id)}}" method="POST" id="payment-form">
            @method('PUT')
            {{ csrf_field() }}
                <h2> Billing Details </h2>
                <div class="row">
                    <div class="col-md-4 order-md-2 mb-4">
                        <h3 class="list-group-item d-flex justify-content-between lh-condensed row">Requested Product</h3>
                        <div class="list-group-item d-flex justify-content-between lh-condensed row">
                            <label for="Product Name" class="col-md-6"> Product Name </label>
                            <input type="text"  class="form-control col-md-6" id="productname" name="productname" value="{{$request->name}}" readonly>
                        </div>
                        <div class="form-group list-group-item d-flex justify-content-between lh-condensed row">
                            <label for="type" class="col-md-6"> Type </label>
                            <input type="text"  class="form-control col-md-6" id="type" name="type" value="{{$categoriesname}}" readonly>
                        </div>
                        <div class="form-group list-group-item d-flex justify-content-between lh-condensed row">
                            <label for="Condition" class="col-md-6"> Condition </label>
                            <input type="text"  class="form-control col-md-6" id="condition" name="condition" value="{{$conditionname}}" readonly>
                        </div>
                        <div class="form-group list-group-item d-flex justify-content-between lh-condensed row">
                            <label for="total" class="col-md-6"> Total Price </label>
                            <input type="text"  class="form-control col-md-6" id="charge" name="charge" value="{{$request->deposit}}" readonly>
                        </div>
                    </div>   

                    <div class="col-md-8 order-md-1">
                        <input type="hidden" id="custId" name="custId" value="1">

                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="name" > Full Name </label>
                                <input type="text"  class="form-control" id="name" name="name" value="{{old('name')}}" required>
                            </div>
                            
                            <div class="form-group col-md-5">
                                <label for="email"> Email Address </label>
                                <input type="email"  class="form-control" id="email" name="email" value="{{old('email')}}" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="address"> Address </label>
                                <input type="text"  class="form-control" id="address" name="address" value="{{old('address')}}" required>
                            </div>

                            <div class="form-group col-md-5">
                                <label for="city"> City </label>
                                <input type="text"  class="form-control" id="city" name="city" value="{{old('city')}}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="postcode"> Postcode </label>
                                <input type="text"  class="form-control" id="postcode" name="postcode" value="{{old('postcode')}}" required>
                            </div>
                            
                            <div class="form-group col-md-5">
                                <label for="phone"> Phone </label>
                                <input type="text"  class="form-control" id="phone" name="phone" value="{{old('phone')}}" required>
                            </div>
                        </div>

                        <div class="spacer"></div>

                        <h2 class="my-3"> Payment Details </h2>
                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="name_on_card"> Name on Card </label>
                                <input type="text"  class="form-control" id="name_on_card" name="name_on_card" value="" required>
                            </div>

                            <div class="form-group col-md-5">
                                <label for="address"> Address </label>
                                <input type="text"  class="form-control" id="address" name="address" value="" required>
                            </div>
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
                        {{-- END OF STRIPE HTML --}}
                    </div>                    
                </div>
                <div class="spacer"></div>
                <button type="submit" id="complete-order" class="btn btn-outline-primary my-3"> Confirm</button>
                
            </form>
        </div>

        <div class="checkout-table-container">
            
        </div>

    </div>
        
@endsection

@section('extra-js')
    <script src="https://js.stripe.com/v3/"> </script>
    <script>   

    document.addEventListener('DOMContentLoaded', function () {
                        // Create a Stripe client.
            var stripe = Stripe('pk_test_0rU5AzN9osLSyuAJWlIc9FRy');

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

            //This will ensure that transcation does not occur twice if the user clicks confirms more than once
            document.getElementById('complete-order').disabled = true;

            // This will pass in the card details and address as it is recommended
            var options = {
                name: document.getElementById('name_on_card').value,
                address_line1: document.getElementById('address').value,
                address_city: document.getElementById('city').value,
                address_zip: document.getElementById('postcode').value
            }

            stripe.createToken(card, options).then(function(result) {
                if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;

                //enable the submit button    
                document.getElementById('complete-order').disabled = false;
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
            form.submit();
            }
        });
    </script>
@endsection