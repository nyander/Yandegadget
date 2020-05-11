@extends('layouts.settingside')

@section('settings')

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
                            {{-- The comma is the the seperator, for each user get their roles and pluck the names --}}
                            {{-- the toArray returns the roles as an array reather than a collection  --}}
                            <td>{{implode(',',$user->roles()->get()->pluck('name')->toArray())}}</td>
                            <td>
                                @can('admin-role')
                                <a href="{{route('admin.users.edit', $user->id)}}"><button type="button" class="btn btn-sm btn-outline-primary float-left">Edit</button></a>
                                @endcan

                                @can('admin-role')
                                <form action="{{route('admin.users.destroy', $user)}}" method="POST" class="float-left">
                                  @csrf
                                  {{method_field('DELETE')}}
                                  <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form> 
                                @endcan
                            </td>
                        </tr>    
                        @endforeach   
                                               
                        </tbody>
                      </table>                      
                </div>
            </div>

@endsection
