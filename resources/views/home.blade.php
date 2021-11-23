@extends('layouts.app')

@section('content')

    <div class="row justify-content-center my-5">
        <div class="col-md-4 order-md-1 mr-5">
            <div class="container text-white p-3" style="background-color:#f70d1a;">
                <p>Welcome <b>{{Auth::user()->name}},</b></p>
                {{-- Suppliers view --}}
                @can('supplier-role')
                    @if ($checker)
                        <p>
                            <b>Supplier:</b>
                            As an Supplier you can upload and manage products you would like to sell.
                            Any product sold to Yande Gadgtes will be recorded by the Admin.  
                            The Dashboard products recorded & sold.
                        </p>
                        <p>Please add your details to upload products</p>
                        <a href="{{ route('suppliers.create') }}"> <button class="btn btn-success" type="submit">Add Details</button> </a>
                    @else 
                    <p>
                        <b>Supplier:</b>
                        As an Supplier you can upload and manage products you would like to sell.
                        Any product sold to Yande Gadgtes will be recorded by the Admin.  
                        The Dashboard products recorded & sold. 
                    </p>
                    <a href="{{route('suppliers.edit', $suppliers)}}"> <button class="btn btn-success" type="submit">Manage Details</button> </a>
                    @endif
                @endcan
                {{-- Admin View --}}
                @can('admin-role')
                    <p>
                        <b>Admin:</b>
                        As an Admin you can manage and ship products, manage requests as well as 
                        review company performance.  The Dashboard table displays the progress 
                        within business as well as the website. 
                    </p>
                    <a href="{{route('reports.create')}}"><button type="button" class="btn btn-success " >Review Performance</button></a>
                    <a href="{{route('transactions.create')}}"><button type="button" class="btn btn-primary " >Record Trans.</button></a>
                @endcan

                {{-- Customer View --}}
                @can('customer-role')
                    <b>Customer:</b>
                    <p>
                        As an Customer you can make and manage requests.
                        You will then be notified when the product has been acquired.  
                        The Dashboard products requested and those that have been acquired. 
                    </p>                    
                @endcan

                {{-- Staff View --}}
                @can('staff-role')
                    <p>
                        <b>Staff:</b>
                        As an Staff you can confirm shipments as well as recording purchases.
                        You will then be notified when the product has been acquired.  
                        The Dashboard displays the company progress in sales and shipments.  
                    </p>                    
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
                            
                            
                        </table>
                    
                </div>
                
                
            </div>
        </div>
        
    </div>

@endsection
