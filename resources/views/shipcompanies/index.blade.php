@extends('layouts.settingside')

@section('settings')

    
    <div class="card">
        <div class="card-header">Shipment Company</div>
        <div class="card-body">
                {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                <table class="table">
                <thead class="thead-dark">
                    <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Contact</th>
                    <th scope="col">Email</th> 
                    <th scope="col"><a href="{{route('shipcompanies.create')}}"> <button type="button" class="btn btn-success" >Add</button></a>                          
                    {{-- <th scope="col"><a href="{{route('shipcompanies.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                             --}}
                    </tr>
                </thead>
                <tbody>
                @foreach ($companies as $company)
                <tr>
                    <th scope="row">{{$company->id}}</th>
                    <td><a href="/shipcompanies/{{$company->id}}">{{$company->name}}</a></td>
                    <td>{{$company->address}}</td>
                    <td>{{$company->number}}</td>
                    <td>                          
                        
                        <a href="{{route('shipcompanies.edit', $company->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a> 
                        
                        
                        <form action="{{route('shipcompanies.destroy', $company->id)}}" method="POST" class="float-left" onsubmit="myButton.disabled = true; return true;">
                            @csrf
                            {{method_field('DELETE')}}
                            <button type="submit" class="btn btn-danger" name="myButton" style="margin-left: 5px;">Delete</button>
                        </form> 
                        
                        
                    </td>
                </tr>    
                @endforeach   
                                        
                </tbody>
                </table>                      
        </div>
    </div>
       

@endsection
