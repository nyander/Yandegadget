@extends('layouts.app')

@section('content')
<div class="container my-5">
    
    <div class="bg-danger p-4 mx-6 text-light text-center my-2">
        <h5 >Can't Find Product you are looking for?</h5>
        <p>
            If you cannot find a particular product, why not try one of these 2nd hand digital marketplaces.
            
        </p>
        <div class="row justify-content-center">
            <a href="https://uk.webuy.com/" target="_blank">
                <button type="submit" class="btn text-light mr-2 ml-2" style="background-color:red;" name="myButton">
                    CEX
                </button>
            </a>

            <a href="https://www.ebay.co.uk/" target="_blank">
                <button type="submit" class="btn text-light  mr-2 ml-2" style="background-color:#f4ae02;" name="myButton">
                    Ebay
                </button>
            </a>

            <a href="https://en-gb.facebook.com/marketplace/" target="_blank">
                <button type="submit" class="btn text-light  mr-2 ml-2" style="background-color:#0063d1;" name="myButton">
                    Facebook
                </button>
            </a>

            <a href="http://www.bootgroup.com/" target="_blank">
                <button type="submit" class="btn text-light  mr-2 ml-2" style="background-color:black;" name="myButton">
                    Bootsale
                </button>
            </a>

            <a href="https://www.shpock.com/en-gb" target="_blank">
                <button type="submit" class="btn text-light  mr-2 ml-2" style="background-color:#3cce69;" name="myButton">
                    Shpock
                </button>
            </a>
        </div> 
    </div>
    <div class="row justify-content-center ">
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
                            <th scope="col">Supplier</th>
                            {{-- Only Suppliers can add new products --}}
                            @can('supplier-role')
                            <th scope="col"><a href="{{route('supplierproducts.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                            
                            @endcan
                            @can('admin-role')
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
                                <td>{{DB::table('categories')->where('id',$product->condition)->value('type')}}</td>
                                <td>£ {{$product->selling_Price}}</td>
                                <td>{{DB::table('suppliers')->where('supplier_id',$product->supplier_id)->value('name')}}</td>
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
                                <td>{{DB::table('categories')->where('id',$product->condition)->value('type')}}</td>
                                <td>£ {{$product->selling_Price}}</td>                                
                                <td>
                                    <a class="text-dark" href="/suppliers/{{$supplierID = DB::table('suppliers')->where('supplier_id',$product->supplier_id)->value('id')}}">
                                        {{$supplierID = DB::table('suppliers')->where('supplier_id',$product->supplier_id)->value('name')}}
                                    </a>    
                                </td>
                                <td>                          
                                    @can('supplier-role')
                                    {{-- ONly Supplier should be able to edit Product  --}}
                                    <a href="{{route('supplierproducts.edit', $product->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                    
                                    {{-- Admin and Supplier can add product --}}
                                    <form action="{{route('supplierproducts.destroy', $product->id)}}" method="POST" class="float-left" onsubmit="myButton.disabled = true; return true;">
                                        @csrf
                                        {{method_field('DELETE')}}
                                        <button type="submit" class="btn btn-danger" name="myButton">Delete</button>
                                    </form> 
                                    @endcan

                                    @can('admin-role')
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
