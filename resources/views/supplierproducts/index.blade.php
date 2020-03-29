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
                            <th></th>
                            <th scope="col">Product Name </th>
                            <th scope="col">Type</th>
                            <th scope="col">Condition</th>
                            <th scope="col">Price</th>  
                            {{-- Only Suppliers can add new products --}}
                            @can('upload-edit-supplier-products')
                            <th scope="col"><a href="{{route('supplierproducts.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                            
                            @endcan
                            @can('manage-products')
                            <th>Manage  </th> 
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($products as $product)
                        @if($product->purchased == true)
                        <tr class="bg-secondary text-white">
                            <th class="text-white" scope="row">{{$product->id}}</th>
                            <td ><a class="text-white" href="/supplierproducts/{{$product->id}}"><img src="/gallery/{{$product->thumbnail_path}}" style="height:40px; width:auto;"></a></td>
                            <td ><a class="text-white" href="/supplierproducts/{{$product->id}}">{{$product->name}}</a></td>
                            <td>{{DB::table('categories')->where('id',$product->type)->value('type')}}</td>
                            <td>{{DB::table('categories')->where('id',$product->type)->value('type')}}</td>
                            <td>£ {{$product->selling_Price}}</td>
                            <td>                          
                                <b>Purchased</b>
                            </td>
                        </tr>    
                        @else
                        <tr >
                            <th  scope="row">{{$product->id}}</th>
                            <td ><a  href="/supplierproducts/{{$product->id}}"><img src="/gallery/{{$product->thumbnail_path}}" style="height:40px; width:auto;"></a></td>
                            <td ><a  href="/supplierproducts/{{$product->id}}">{{$product->name}}</a></td>
                            <td>{{DB::table('categories')->where('id',$product->type)->value('type')}}</td>
                            <td>{{DB::table('categories')->where('id',$product->type)->value('type')}}</td>
                            <td>£ {{$product->selling_Price}}</td>
                            <td>                          
                                @can('upload-edit-supplier-products')
                                {{-- ONly Supplier should be able to edit Product  --}}
                                <a href="{{route('supplierproducts.edit', $product->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                
                                {{-- Admin and Supplier can add product --}}
                                <form action="{{route('supplierproducts.destroy', $product->id)}}" method="POST" class="float-left" onsubmit="myButton.disabled = true; return true;">
                                    @csrf
                                    {{method_field('DELETE')}}
                                    <button type="submit" class="btn btn-danger" name="myButton">Delete</button>
                                </form> 
                                @endcan

                                @can('manage-products')
                                    <a href="{{route('products.storesupproduct', $product->id)}}"><button type="button" class="btn btn-primary float-left">Purchased</button></a>
                                @endcan
                            </td>
                        </tr>    
                        @endif
                        @endforeach   
                                               
                        </tbody>
                      </table>                      
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
