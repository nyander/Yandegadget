<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>    
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.0.1/js/plugins/piexif.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.0.1/js/plugins/sortable.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.0.1/js/plugins/purify.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.0.1/js/fileinput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.0.1/themes/fas/theme.min.js"></script> 
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    
    <!-- Carousel in home page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.css" 
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:100,600" type="text/css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.4/css/bulma.min.css">    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/fontawesome.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/fontawesome.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@1,700&display=swap');</style>
    <style>@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap');</style>
    <style>@import url('https://fonts.googleapis.com/css2?family=Cabin:wght@800&display=swap');</style>
    
    
    
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.0.1/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js" 
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    
</head>
<body>
    <div id="app">
        @include('inc.navbar')
        <div class="container">
                @include('inc.messages')
            
                {{-- this is will return the value and saves the content in its state --}}      
                @yield('content')
            
        </div> 
        @yield('index')      

    </div>
    @yield('extra-js')

    <br>
    <br>
    <!-- Footer -->
    <footer class="page-footer font-medium blue pt-5" id="footersection">
        <div class="container">
            <!-- Footer Links -->
            <div class="container-fluid text-center text-md-left">
                <div class="subscribeTitle">
                    <h5>Subscribe to our Newsletter</h5>
                    <div class="subscriptionfield">
                        <input type="email" class="mailinglist"  name="subscription" placeholder="Email">
                    </div>
                </div>
    
                <!-- Grid row -->
                <div class="row">
    
                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">
    
                        <!-- Links -->
                        <h5 class="text-light">My Account</h5>
    
                        <ul class="list-unstyled text-light">
                            <li>
                                <a href="/products" class="text-light">My Account</a>
                            </li>
                            <li>
                                <a href="/requests" class="text-light">My Orders</a>
                            </li>                            
                        </ul>
    
                    </div> 
    
                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">
    
                        <!-- Links -->
                        <h5 class="text-light">Legal & Policies</h5>
    
                        <ul class="list-unstyled text-light">
                            <li>
                                <a href="/products" class="text-light">Conditions</a>
                            </li>
                            <li>
                                <a href="/requests" class="text-light">Privacy Policy</a>
                            </li>
                            <li>
                                <a href="/requests/create"class="text-light">Privacy Settings</a>
                            </li>
                            <li>
                                <a href="/requests/create"class="text-light">Order & Purchasing Policy</a>
                            </li>
                            
                        </ul>
    
                    </div>
                    <!-- Grid column -->
    
                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">
    
                        <!-- Links -->
                        <h5 class="text-light" >Our Company</h5>
    
                        <ul class="list-unstyled">
                            <li>
                                <a href="/about" class="text-light">Yande Gadgets</a>
                            </li>
                            <li>
                                <a href="/about" class="text-light">About Us</a>
                            </li>
                            <li>
                                <a href="/about" class="text-light">Career</a>
                            </li>
                        </ul>
    
                    </div>
                    <!-- Grid column -->

                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">
    
                        <!-- Links -->
                        <h5 class="text-light">Customer Service</h5>
    
                        <ul class="list-unstyled text-light">
                            <li>
                                <a href="/products" class="text-light">Service & FAQ</a>
                            </li>
                            <li>
                                <a href="/requests" class="text-light">Order & Payment</a>
                            </li>
                            <li>
                                <a href="/requests/create"class="text-light">Shipping & Returns</a>
                            </li>
                            
                        </ul>
    
                    </div>
    
                </div>
                <!-- Grid row -->
    


                <div class="paymentMethod">
                    <h5 class="text-light">Accepted Payment</h5>
                </div>

                <div class="row container">
                    <i class="fa fa-cc-mastercard" style="font-size:3.5em; color:white; padding:5px;"></i>
                    <i class="fa fa-cc-paypal" style="font-size:3.5em; color:white; padding:5px;"></i>
                    <i class="fa fa-cc-visa" style="font-size:3.5em; color:white; padding:5px;"></i>
                </div>
            </div>
            <!-- Footer Links -->

    
            <!-- Copyright -->
            <div class="footer-copyright text-center py-3 text-light">© 2020 Copyright:
                <a class="text-light" href="https://github.com/nyander/Yandegadget/"> Richard Nyande</a>
            </div>
            <!-- Copyright -->
        </div>
    </footer>
  <!-- Footer -->

</body>
</html>
