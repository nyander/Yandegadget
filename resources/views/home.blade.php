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
                    <a href="{{route('reports.create')}}"><button type="button" class="btn btn-success " >Filter Products</button></a>
                    <a href="{{route('transactions.create')}}"><button type="button" class="btn btn-primary " >Record Transactions</button></a>
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
                                    <td>Products in store</td>
                                    <td>{{$productsavailable}}</td>
                                </tr>
                                <tr>
                                    <td>Total Sales</td>
                                    <td>{{$purchasedProducts}}</td>
                                </tr>
                                <tr>
                                    <td>Sales this month</td>
                                    <td>{{$monthsales}} </td>
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
                                    <td>Unpaid Requests</td>
                                    <td class="text-danger">{{$unpaidrequest}}</td>
                                    @if ($unpaidrequest)
                                        <td class="text-danger">Potential products</td>
                                    @endif                                    
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
                                <tr>
                                    <td>Acquired Requested product</td>
                                    <td>{{$acquiredrequ}}</td>
                                </tr>
                                @endcan

                                {{-- Customers table --}}
                                @can('customer-role')
                                    <tr>
                                        <td>Products available</td>
                                        <td>{{$productsavailable}}</td>
                                    </tr>
                                    <tr>
                                        <td>Requested product</td>
                                        <td>{{$requestedprod}}</td>
                                    </tr>
                                    <tr>
                                        <td>Unpaid Requests</td>
                                        <td class="text-danger">{{$userunpaidrequest}}</td>
                                        @if ($userunpaidrequest)
                                            <td class="text-danger">To finalize request, please pay for deposit </td>
                                        @endif                                    
                                    </tr>
                                    <tr>
                                        <td>Acquired Requested product</td>
                                        <td>{{$acquiredrequ}}</td>
                                    </tr>
                                @endcan

                                {{-- staff table --}}
                                @can('staff-role')
                                    <tr>
                                        <td>Sales this month</td>
                                        <td>{{$monthsales}} </td>
                                    </tr>
                                    <tr>
                                        <td>Products to be sold </td>
                                        <td>{{$productsavailable}}</td>
                                    </tr>
                                    @if ($notrecieved) 
                                        <tr>
                                            <td>Shipments unconfirmed </td>
                                            <td class="text-danger">{{$notrecieved}}</td>
                                            <td class="text-danger">Confirm if shipments have been recieved</td> 
                                        </tr>
                                    @endif

                                    @if($notrecievedprod) 
                                    <tr>
                                        <td>Product unconfirmed </td>
                                        <td class="text-danger">{{$notrecievedprod}}</td> 
                                        <td class="text-danger">Confirm if products in shipments have been recieved</td>
                                    </tr>
                                    @endif  
                                    
                                @endcan
                            </tbody>
                            
                        </table>
                    
                </div>
                
                
            </div>
        </div>
        
    </div>

@endsection
