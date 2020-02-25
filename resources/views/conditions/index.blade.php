@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Conditions</div>
                
                

                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>                            
                            <th scope="col"><a href="{{route('conditions.create')}}"><button type="button" class="btn btn-primary float-left" >Add Condition</button></a></th>                            
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($conditions as $condition)
                        <tr>
                            <th scope="row">{{$condition->id}}</th>
                            <td>{{$condition->details}}</td>
                            <td>                          
                                
                                <a href="{{route('conditions.edit', $condition->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                
                                @can('manage-users')
                                <form action="{{route('conditions.destroy', $condition->id)}}" method="POST" class="float-left">
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
