@extends('layouts.app')

@section('content')

    <div class="row justify-content-center my-5">
        <div class="col-md-4 order-md-1 mr-5">
            <div class="container text-white p-3" style="background-color:#f70d1a;">
                <p>Welcome <b>{{Auth::user()->name}},</b></p>
                @can('supplier-role')
                    @if ($checker)
                        <p>
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                            Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.
                        </p>
                        <p>Please add your details to upload products</p>
                        <a href="{{ route('suppliers.create') }}"> <button class="btn btn-success" type="submit">Add Details</button> </a>
                    @else 
                    <p>
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
                        when an unknown printer took a galley of type and scrambled it to make a type specimen book. 
                    </p>
                    <a href="{{route('suppliers.edit', $suppliers)}}"> <button class="btn btn-success" type="submit">Manage Details</button> </a>
                    @endif
                @endcan
                @can('admin-role')
                    <p>
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
                        when an unknown printer took a galley of type and scrambled it to make a type specimen book. 
                    </p>
                    <a href="{{route('reports.create')}}"><button type="button" class="btn btn-success " >Filter Products</button></a>
                    <a href="{{route('transactions.create')}}"><button type="button" class="btn btn-primary " >Record Transactions</button></a>
                @endcan
            </div>            
        </div>
        <div class="col-md-7 order-md-2">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    {{-- Suppliers View --}}
                    
                        <table class="table">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Information</th>
                                    <th scope="col">Result</th>                                    
                                    <th scole="col">
                                        @can('supplier-role')
                                            <a href="{{route('supplierproducts.create')}}">
                                                <button type="button" class="btn btn-success" >Add Product</button>
                                            </a>
                                        @endcan 
                                        @can('admin-role')
                                        <a href="{{route('products.create')}}">
                                            <button type="button" class="btn btn-success" >Upload Product</button>
                                        </a>
                                        @endcan   
                                    </th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                {{-- supplier table --}}
                                @can('supplier-role')
                                <tr>
                                    <td>Products Uploaded:</td>
                                    <td>{{$supproduct}}</td>
                                </tr>
                                <tr>
                                    <td>Products Sold:</td>
                                    <td>{{$soldsupproduct}}</td>
                                </tr>
                                @endcan

                                {{-- Admin table --}}
                                @can('admin-role')
                                <tr>
                                    <td>Requested Products</td>
                                    <td>{{$requestedProducts}}</td>
                                </tr>
                                <tr>
                                    <td>Total Purchases</td>
                                    <td>{{$purchasedProducts}}</td>
                                </tr>
                                <tr>
                                    <td>Supplier Products available</td>
                                    <td>{{$supplierProducts}}</td>
                                </tr>
                                <tr>
                                    <td>Products Uploaded</td>
                                    <td>{{$uploadedProducts}}</td>
                                </tr>
                                <tr>
                                    <td>Signed Customers</td>
                                    <td>{{$signedCustomers}}</td>
                                </tr>
                                <tr>
                                    <td>Suppliers</td>
                                    <td>{{$suppliersAmount}}</td>
                                </tr>
                                <tr>
                                    <td>Suppliers with Accounts</td>
                                    <td>{{$signedSuppliers}}</td>
                                    @if($signedSuppliers>$suppliersAmount)
                                        <td class="text-danger">Atleast {{$signedSuppliers - $suppliersAmount}} Suppliers not uploaded details</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td>Transactions</td>
                                    <td>{{$transactionCount}}</td>
                                </tr>
                                @endcan
                            </tbody>
                            
                        </table>
                    
                </div>
                
                
            </div>
        </div>
        
    </div>

@endsection
