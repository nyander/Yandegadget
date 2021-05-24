<!-- Black Section at the top of the page -->
<row style=" width:100%;">
<div class="col" id="topbar">
      <p >All Products were Sourced and Quality Checked in United Kingdom</p>
    </div>
</row>      

<!-- Row Number 2 : Login - Logo - Cart etc. -->
<div class="row" id="topmenurow">
    <div class="col-3" id="loginstatus">
        <div class="header-banner" >
            @if (Auth::check()) 
                <p class="index-subtitle"> Logged In as: {{Auth::user()->name}}
            @else 
            <p><a href="/login" role="button" style="color:black;">Guest</a>
            @endif
        </div>
    </div>

    <div class="col-6" id="Mainheaderlogo">
        <a class="navbar-brand" href="{{ url('/') }}">
            <p>YANDE GADGETS</p>
        </a>
    </div>

    <div class="col-3" id="lefticons">
        <i class="material-icons" style="padding-left: 5px; padding-right:5px;">search </i>
        <!-- Top Loggin section -->

                @guest
                        <li class="nav-item dropdown" style="margin-top: -8px;">    
                            <a id="navbarDropdown1" class="nav-link material-icons" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                person
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown1" >
                                <a class="nav-link" style="color: black; font-size:14px; text-align:right;" href="{{ route('login') }}">{{ __('Login') }}</a>
                                <a class="nav-link" style="color: black; font-size:14px; text-align:right;" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </div>

                        </li>
                @else
                        {{-- Logged in user --}}
                        <li class="nav-item dropdown" style="margin-top: -8px;">
                              <a id="navbarDropdown" class="nav-link material-icons" style="padding-left: 5px; padding-right:5px; color:black;" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                  person
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
                                    
                                    @can('managing-requests')
                                        <a class="dropdown-item" href="{{route('requests.index')}}">
                                            Requested Products
                                        </a>
                                    @endcan

                                    @can('admin-role')
                                        <a class="dropdown-item" href="{{ route('reports.index') }}">
                                            Reports 
                                        </a> 
                                    @endcan

                                    @can('manage-shipped-products')
                                        <a class="dropdown-item" href="{{ route('ships.index') }}">
                                            Shipped Product Management 
                                        </a> 
                                    @endcan
                                    
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
                          
                          
                        <i class="material-icons" style="padding-left: 5px; padding-right:5px;">shopping_cart</i>
                          
                        <!-- Notification -->
                        <li class="nav-item dropdown" style="margin-top: -8px;">
                            <a id="navbarDropdown1" cclass="nav-link dropdown-toggle" class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre> 
                            <i class="material-icons" style="color:black ">notifications</i>
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
                                            <hr>                                     
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
                                            <hr>                                     
                                            <b>{{$notification->data['data']}}</b>
                                            <p>{{$notification->created_at->diffForHumans()}}</p> 
                                        </a>     
                                    @endforeach
                               @endif 
                            </div>
                        </li>


                    @endguest
    </div>
</div>





<!-- Row Number 3: Menus -->
<div class="row">
    <div class="col-3" style="padding-top:9px; font-family: 'Montserrat', sans-serif;  font-size:14px;">
    </div>

    <div class="col-6" style="display: flex;  margin-left:-10px; justify-content: center;  align-items: center; font-family: 'Montserrat'; font-size:16px;">
    <nav class="navbar navbar-expand-md navbar-light shadow-md ">
          <div class="container my-auto">
              <a class="navbar-brand" href="{{ url('/') }}">
                  {{-- {{ config('app.name', 'Laravel') }} --}}
              </a>
              <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                  <span class="navbar-toggler-icon"></span>
              </button>

              <div class="collapse navbar-collapse" id="navbarSupportedContent">

                  <!-- Right Side Of Navbar -->
                  <ul class="navbar-nav font-weight-bold">
                      <a class="nav-link" href="/">Home</a>
                      <a class="nav-link" href="/products">Products</a>
                      <a class="nav-link" href="/about">About Us</a> 
                      @can('supproducts') 
                        <a class="nav-link" href="/supplierproducts">Supplier Products</a> 
                      @endcan
                      @can('managing-requests')
                        <a class="nav-link" href="/requests/create">Request Product</a>
                      @endcan

                      @can("admin-role")
                        <a class="nav-link" href="/shipments">Shipments <span class="shipment-count">
                            @if(Cart::instance('default')->count() > 0) 
                            <span class="badge badge-light">{{Cart::instance('default')->count()}}</span></span>    
                            @endif                    
                        </a>
                      @endcan   

                      <!-- Authentication Links -->
                      @guest
                      @else
                        {{-- Notification--}}
                      @endguest
                  </ul>
                  
              </div>
          </div>
      </nav>
    </div>

    <div class="col-3">
    </div>
</div>


      
   