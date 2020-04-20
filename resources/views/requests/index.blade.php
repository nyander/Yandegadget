@extends('layouts.app')

@section('content')


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">Categories</div>
                    
                    <div class="card-body">
                        {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                        <table class="table">
                            <thead class="thead-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Customer Name</th>
                                <th scope="col">Product Name</th>
                                <th scope="col">Deposit Paid</th>                            
                                <th scope="col"><a href="{{route('requests.create')}}"><button type="button" class="btn btn-success" >Add Request</button></a></th>                            
                            </tr>
                            </thead>
                            <tbody>      
                                @if(Gate::allows('admin-role'))       
                                    @foreach ($requests as $request)
                                        @if($request->acquired)
                                            <tr class="bg-success text-light" >
                                                <th scope="row" class="text-light">{{$request->id}}</th>
                                                <td>{{DB::table('users')->where('id', $request->customer_id)->value('name')}}</td>
                                                <td><a class="text-light" href="/requests/{{$request->id}}">{{$request->name}}</a></td>
                                                <td>
                                                    @if($request->deposit_paid == 1)
                                                        Yes
                                                    @else
                                                        No
                                                    @endif
                                                </td>
                                                    <td >
                                                        <b >Acquired<b>
                                                    </td>

                                            </tr>
                                        @else 
                                        <tr>
                                            <th scope="row">{{$request->id}}</th>
                                            <td>{{DB::table('users')->where('id', $request->customer_id)->value('name')}}</td>
                                            <td><a href="/requests/{{$request->id}}">{{$request->name}}</a></td>
                                            <td>
                                                @if($request->deposit_paid == 1)
                                                    Yes
                                                @else
                                                    No
                                                @endif
                                            </td>
                                            <td>                                
                                                <a href="{{route('requests.edit', $request->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                                
                                                <a href="{{route('products.storereqproduct', $request->id)}}"><button type="button" class="btn btn-info float-left">Acquired</button></a>
                                                @endif
                                                
                                            </td>
                                        </tr>    
                                    @endforeach 
                                @else    
                                    @foreach ($requests as $request)
                                        @if(($request->customer_id == Auth::id()))
                                            @if($request->acquired)
                                                <tr class="bg-success text-light" >
                                                    <th scope="row" class="text-light">
                                                        {{$request->id}}
                                                    </th>
                                                    <td>
                                                        {{DB::table('users')->where('id', $request->customer_id)->value('name')}}
                                                    </td>
                                                    <td>
                                                        <a class="text-light" href="/requests/{{$request->id}}">
                                                            {{$request->name}}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        @if($request->deposit_paid == 1)
                                                            Yes
                                                        @else
                                                            No
                                                        @endif
                                                    </td>
                                                        <td >
                                                            <b >Acquired<b>
                                                        </td>

                                                </tr>
                                            @else
                                                <tr>
                                                    <th scope="row">
                                                        {{$request->id}}
                                                    </th>
                                                    <td>
                                                        {{DB::table('users')->where('id', $request->customer_id)->value('name')}}
                                                    </td>
                                                    <td>
                                                        <a href="/requests/{{$request->id}}">
                                                            {{$request->name}}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        @if($request->deposit_paid == 1)
                                                            Yes
                                                        @else
                                                            No
                                                        @endif
                                                    </td>      
                                                    <td>                          
                                                        @can('admin-role')
                                                            <a href="{{route('requests.edit', $request->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                                        @endcan
                                                            {{-- after customer has made the payment, they cannot remove the requested product for reference purposes --}}
                                                        @if($request->deposit_paid == 0)
                                                            <form action="{{route('requests.destroy', $request->id)}}" method="POST" class="float-left" onsubmit="myButton.disabled = true; return true;">
                                                                @csrf
                                                                {{method_field('DELETE')}}
                                                                <button type="submit" class="btn btn-danger" name="myButton">Delete</button>
                                                            </form> 
                                                        @endif
                                                        @if($request->deposit_paid == 0)
                                                            <a href="{{route('checkouts.index')}}/{{$request->id}}"><button type="button" class="btn btn-success float-left">Pay</button></a>
                                                        @endif
                                                            
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    @endforeach         
                                @endif                                        
                            </tbody>
                        </table>                      
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection