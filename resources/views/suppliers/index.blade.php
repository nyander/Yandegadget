@extends('layouts.settingside')

@section('settings')
        <div>
            <div class="card">
                <div class="card-header">Suppliers</div> 
                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Email</th>                            
                            <th scope="col"><a href="{{route('suppliers.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                            
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($suppliers as $supplier)
                        <tr>
                            <th scope="row">{{$supplier->id}}</th>
                            <td><a href="/suppliers/{{$supplier->id}}">{{$supplier->name}}</a></td>
                            <td>{{$supplier->contact}}</td>
                            <td>{{$supplier->email}}</td>
                            <td>                          
                                
                                <a href="{{route('suppliers.edit', $supplier->id)}}"><button type="button" class="btn btn-sm btn-outline-primary float-left">Edit</button></a>
                                
                                @can('admin-role')
                                <form action="{{route('suppliers.destroy', $supplier->id)}}" method="POST" class="float-left" onsubmit="myButton.disabled = true; return true;">
                                  @csrf
                                  {{method_field('DELETE')}}
                                  <button type="submit" name="myButton" class="btn btn-sm btn-outline-danger">Delete</button>
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
    

@endsection
