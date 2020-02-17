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
                        @foreach ($requests as $request)
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
                                
                                
                                <form action="{{route('requests.destroy', $request->id)}}" method="POST" class="float-left">
                                @csrf
                                {{method_field('DELETE')}}
                                <button type="submit" class="btn btn-danger">Delete</button>
                                </form> 
                                
                                
                            </td>
                        </tr>    
                        @endforeach   
                                               
                        </tbody>
                      </table>                      
                </div>
            </div>
        </div>
    </div>
</div>

@endsection