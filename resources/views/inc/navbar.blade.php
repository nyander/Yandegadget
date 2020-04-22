<!--<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm">
        <h5 class="my-0 mr-md-auto font-weight-normal">Yande Gadgets</h5>
        nav class="my-2 my-md-0 mr-md-3">
          <a class="p-2 text-dark" href="/">Home</a>
          <a class="p-2 text-dark" href="/products">Products</a>
          <a class="p-2 text-dark" href="/about">About Us</a>          
        </nav>
        <a class="btn btn-outline-primary" href="#">Sign up</a>
      </div>-->
      
      <nav class="navbar navbar-expand-md navbar-light bg-yellow shadow-md " style="background-color:yellow;">
          <div class="container my-auto">
              <a class="navbar-brand" href="{{ url('/') }}">
                  {{-- {{ config('app.name', 'Laravel') }} --}}
                  <img class="brand_logo" src="/images/YandeGadgets.png">
              </a>
              <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                  <span class="navbar-toggler-icon"></span>
              </button>

              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <!-- Left Side Of Navbar -->
                  <ul class="navbar-nav mr-auto">
                      
                  </ul>

                  <!-- Right Side Of Navbar -->
                  <ul class="navbar-nav ml-auto font-weight-bold">
                      <a class="nav-link" href="/">Home</a>
                      <a class="nav-link" href="/products">Products</a>
                      <a class="nav-link" href="/about">About Us</a>  
                      <a class="nav-link" href="/supplierproducts">Supplier Products</a> 
                      <a class="nav-link" href="/requests/create">Request Product</a>

                      <a class="nav-link" href="/shipments">Shipments <span class="shipment-count">
                        @if(Cart::instance('default')->count() > 0) 
                        <span class="badge badge-light">{{Cart::instance('default')->count()}}</span></span>    
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
                        {{-- Notification--}}

                        <li class="nav-item dropdown">
                            <a id="navbarDropdown1" cclass="nav-link dropdown-toggle" class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre> 
                                <i class="fa fa-bell"></i>
                                <span class="badge badge-light">{{auth()->user()->unreadNotifications -> count() }}</span>
                            </a>                          

                            <div class="dropdown-menu dropdown-menu-right pr-3 pl-1" aria-labelledby="navbarDropdown">
                               
                                <div hidden>{{$checker = DB::table('role_user')->where('role_id', 2) ->where('user_id', Auth::user()->id)->exists()}}</div>
                                <div hidden>{{$checker1 = DB::table('role_user')->where('role_id', 1) ->where('user_id', Auth::user()->id)->exists()}}</div>

                               @if ($checker)
                                    @foreach (auth()->user()->unreadNotifications as $notification)
                                    <a class="dropdown-item card m-1" style="background-color: lightgreen;" href="/ships">                                        
                                            <div class="card-body">
                                            <b >{{$notification->data['data']}}</b>
                                            <p>{{$notification->created_at->diffForHumans()}}</p> 
                                        </div>                                   
                                    </a>  
                                    @endforeach 

                                    @foreach (auth()->user()->readNotifications as $notification)
                                    <a class="dropdown-item card m-1" style="background-color: lightgrey;" href="/ships">
                                        <hr>
                                        <b>{{$notification->data['data']}}</b>
                                        <p>{{$notification->created_at->diffForHumans()}}</p> 
                                    </a>     
                                    @endforeach

                                @elseif($checker1)
                            
                                @foreach (auth()->user()->unreadNotifications as $notification)                                    
                                    <a class="dropdown-item card m-1" style="background-color: lightgreen; " href="{{url('supproduct/notifications/'.$notification->id)}}">                                        
                                            <div class="card-body">
                                            <b >{{$notification->data['data']}}</b>
                                            <p>{{$notification->created_at->diffForHumans()}}</p> 
                                        </div>                                   
                                    </a>  
                                @endforeach 

                                @foreach (auth()->user()->readNotifications as $notification)
                                    <a class="dropdown-item card m-1" style="background-color: lightgrey; " href="">                                            
                                        <b>{{$notification->data['data']}}</b>
                                        <p>{{$notification->created_at->diffForHumans()}}</p> 
                                    </a>     
                                @endforeach
                                @else       
                                    @foreach (auth()->user()->unreadNotifications as $notification)
                                        <a class="dropdown-item card m-1" style="background-color: lightgreen;" href="{{url('products/notifications/'.$notification->id)}}">
                                            
                                                <div class="card-body">
                                                <b >{{$notification->data['data']}}</b>
                                                <p>{{$notification->created_at->diffForHumans()}}</p> 
                                            </div>                                   
                                        </a>    
                                        
                                    @endforeach 

                                    @foreach (auth()->user()->readNotifications as $notification)
                                        <a class="dropdown-item card m-1" style="background-color: lightgrey; " href="">                                            
                                            <b>{{$notification->data['data']}}</b>
                                            <p>{{$notification->created_at->diffForHumans()}}</p> 
                                        </a>     
                                    @endforeach
                               @endif 
                            </div>
                        </li>
                        
                        {{-- Logged in user --}}
                        <li class="nav-item dropdown">
                              <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                  {{ Auth::user()->name }} <span class="caret"></span>
                              </a>

                              <div class="dropdown-menu dropdown-menu-right " aria-labelledby="navbarDropdown">
                                  <a class="dropdown-item bg-secondary text-light" href="{{ route('logout') }}"
                                     onclick="event.preventDefault();
                                                   document.getElementById('logout-form').submit();">
                                      {{ __('Logout') }}
                                  </a>
                                    <a class="dropdown-item" href="{{route('home')}}">
                                        Dashboard
                                    </a>
                                    @can("admin-role")
                                        <a class="dropdown-item" href="{{route('products.create')}}">
                                            Upload Product
                                        </a>
                                    @endcan    
                                    <a class="dropdown-item" href="{{route('requests.index')}}">
                                        Requested Products
                                    </a>
                                    <a class="dropdown-item" href="{{ route('reports.index') }}">
                                        Reports 
                                    </a> 
                                    <a class="dropdown-item" href="{{ route('ships.index') }}">
                                        Shipped Product Management 
                                    </a> 
                                    @can("admin-role")
                                        <a class="dropdown-item" href="/settings">
                                            Settings
                                        </a>
                                    @endcan
                                                                        
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
   