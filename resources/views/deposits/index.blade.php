@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">Deposits</div>
                
                

                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Product Type</th>
                            <th scope="col">Condition A</th>
                            <th scope="col">Condition B</th>
                            <th scope="col">Condition C</th>                            
                            <th scope="col"><a href="{{route('deposits.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                            
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($deposits as $deposits)
                        <tr>
                            <th scope="row">{{$deposits->id}}</th>
                            <td>{{DB::table('categories')->where('id',$deposits->type)->value('type')}}</td>
                            <td>£ {{$deposits->condition_a}}</td>
                            <td>£ {{$deposits->condition_b}}</td>
                            <td>£ {{$deposits->condition_c}}</td>
                            <td>                          
                                
                                <a href="{{route('deposits.edit', $deposits->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                
                                @can('manage-users')
                                <form action="{{route('deposits.destroy', $deposits->id)}}" method="POST" class="float-left">
                                @csrf
                                {{method_field('DELETE')}}
                                <button type="submit" class="btn btn-danger">Delete</button>
                                </form> 
                                @endcan
                                
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