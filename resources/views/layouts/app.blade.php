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

    

    
    
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.0.1/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
    

    
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
    <footer class="page-footer font-small blue pt-4 ">
        <div class="container">
            <!-- Footer Links -->
            <div class="container-fluid text-center text-md-left">
    
                <!-- Grid row -->
                <div class="row">
    
                    <!-- Grid column -->
                    <div class="col-md-6 mt-md-0 mt-3">
    
                        <!-- Content -->
                        <h5 class="text-uppercase text-light">Yande Gadgets</h5>
                        <p class="text-light">
                            Yande Gadgets is a second-hand electrical retailer based in Ghana 
                            which specializes in Technology, Appliances such as phones, computers, and TVs’.
                        </p>
    
                    </div>
                    <!-- Grid column -->
    
                    <hr class="clearfix w-100 d-md-none pb-3">
    
                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">
    
                        <!-- Links -->
                        <h5 class="text-uppercase text-light">Products</h5>
    
                        <ul class="list-unstyled text-light">
                            <li>
                                <a href="/products" class="text-light">Products</a>
                            </li>
                            <li>
                                <a href="/requests" class="text-light">Product Requests</a>
                            </li>
                            <li>
                                <a href="/requests/create"class="text-light">Request a Product</a>
                            </li>
                            
                        </ul>
    
                    </div>
                    <!-- Grid column -->
    
                    <!-- Grid column -->
                    <div class="col-md-3 mb-md-0 mb-3">
    
                        <!-- Links -->
                        <h5 class="text-uppercase text-light" >About Us</h5>
    
                        <ul class="list-unstyled">
                            <li>
                                <a href="/login" class="text-light">Login</a>
                            </li>
                            <li>
                                <a href="/register" class="text-light">Register</a>
                            </li>
                            <li>
                                <a href="/about" class="text-light">About Us</a>
                            </li>
                        </ul>
    
                    </div>
                    <!-- Grid column -->
    
                </div>
                <!-- Grid row -->
    
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
