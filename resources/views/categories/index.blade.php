@extends('layouts.settingside')

@section('settings')

        <div    >
            <div class="card">
                <div class="card-header">Categories</div>
                
                

                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>                            
                            <th scope="col"><a href="{{route('categories.create')}}"><button type="button" class="btn btn-primary float-left" >Add Category </button></a></th>                            
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($categories as $category)
                        <tr>
                            <th scope="row">{{$category->id}}</th>
                            <td>{{$category->type}}</td>
                            <td>                          
                                
                                <a href="{{route('categories.edit', $category->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                
                                @can('manage-users')
                                <form action="{{route('categories.destroy', $category->id)}}" method="POST" class="float-left">
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

@endsection
