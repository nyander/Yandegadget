@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Users</div>

                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Roles</th>
                            <th scope="col">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <th scope="row">{{$user->id}}</th>
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            {{-- get the roles, then display it using plunk in an array, this is important for large list of roles  --}}
                            <td>{{implode(',',$user->roles()->get()->pluck('name')->toArray())}}</td>
                            <td>
                                @can('edit-users')
                                <a href="{{route('admin.users.edit', $user->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                @endcan

                                @can('delete-users')
                                <form action="{{route('admin.users.destroy', $user)}}" method="POST" class="float-left">
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
