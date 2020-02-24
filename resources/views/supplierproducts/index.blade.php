@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">Supplier Products</div>
                
                

                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Product Name </th>
                            <th scope="col">Type</th>
                            <th scope="col">Condition</th>
                            <th scope="col">Price</th>  
                            {{-- Only Suppliers can add new products --}}
                            <th scope="col"><a href="{{route('supplierproducts.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                            
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($products as $product)
                        <tr>
                            <th scope="row">{{$product->id}}</th>
                            <td><a href="/supplierproducts/{{$product->id}}">{{$product->name}}</a></td>
                            <td>{{DB::table('categories')->where('id',$product->type)->value('type')}}</td>
                            <td>{{DB::table('categories')->where('id',$product->type)->value('type')}}</td>
                            <td>£ {{$product->selling_Price}}</td>
                            <td>                          
                                {{-- ONly Supplier should be able to edit Product  --}}
                                <a href="{{route('supplierproducts.edit', $product->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                
                                {{-- Admin and Supplier can add product --}}
                                <form action="{{route('supplierproducts.destroy', $product->id)}}" method="POST" class="float-left">
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
