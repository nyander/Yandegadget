<!--<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm">
        <h5 class="my-0 mr-md-auto font-weight-normal">Yande Gadgets</h5>
        nav class="my-2 my-md-0 mr-md-3">
          <a class="p-2 text-dark" href="/">Home</a>
          <a class="p-2 text-dark" href="/products">Products</a>
          <a class="p-2 text-dark" href="/about">About Us</a>          
        </nav>
        <a class="btn btn-outline-primary" href="#">Sign up</a>
      </div>-->
      <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
          <div class="container">
              <a class="navbar-brand" href="{{ url('/') }}">
                  {{ config('app.name', 'Laravel') }}
              </a>
              <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                  <span class="navbar-toggler-icon"></span>
              </button>

              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <!-- Left Side Of Navbar -->
                  <ul class="navbar-nav mr-auto">
                      
                  </ul>

                  <!-- Right Side Of Navbar -->
                  <ul class="navbar-nav ml-auto">
                      <a class="nav-link" href="/">Home</a>
                      <a class="nav-link" href="/products">Products</a>
                      <a class="nav-link" href="/about">About Us</a>  
                      <a class="nav-link" href="/checkouts/">                    
                      <a class="nav-link" href="/requests/create">Request Product</a> 
                      <a class="nav-link" href="/shipments">Shipments <span class="shipment-count">
                        @if(Cart::instance('default')->count() > 0) 
                        <span>{{Cart::instance('default')->count()}}</span></span>    
                        @endif                    
                        </a>     
                      <!-- Authentication Links -->
                      @guest
                          <li class="nav-item">
                              <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                          </li>
                          @if (Route::has('register'))
                              <li class="nav-item">
                                  <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                              </li>
                          @endif
                      @else
                          <li class="nav-item dropdown">
                              <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                  {{ Auth::user()->name }} <span class="caret"></span>
                              </a>

                              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                  <a class="dropdown-item" href="{{ route('logout') }}"
                                     onclick="event.preventDefault();
                                                   document.getElementById('logout-form').submit();">
                                      {{ __('Logout') }}
                                  </a>
                                  <a class="dropdown-item" href="{{route('products.create')}}">
                                    Upload Product
                                </a>
                                <a class="dropdown-item" href="{{route('requests.index')}}">
                                    Requested Products
                                </a>
                                  @can('manage-users')
                                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                       User Management 
                                    </a>                                  
                                  @endcan
                                  <a class="dropdown-item" href="{{ route('suppliers.index') }}">
                                    Supplier Management 
                                  </a>   
                                  <a class="dropdown-item" href="{{ route('conditions.index') }}">
                                    Condition Management 
                                 </a> 
                                 <a class="dropdown-item" href="{{ route('categories.index') }}">
                                    Categories Management 
                                 </a>    
                                 <a class="dropdown-item" href="{{ route('ships.index') }}">
                                    Shipped Product Management 
                                  </a>                                  
                                 <a class="dropdown-item" href="{{ route('deposits.index') }}">
                                    Deposit Management 
                                 </a>                                       
                                  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                      @csrf
                                  </form>
                              </div>
                          </li>
                      @endguest
                  </ul>
                  
              </div>
          </div>
      </nav>