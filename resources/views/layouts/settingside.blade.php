@extends('layouts.app')

@section('content')

  
    <div class="py-5">
        <div class="row">
                <div class="col-md-3 order-md-1">
                    <div class="list-group" id="list-tab" role="tablist">
                        <a class="list-group-item list-group-item-action active bg-danger text-light font-weight-bold" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">Settings</a>
                        <a class="list-group-item list-group-item-action" id="list-suppliers-list" href="/admin/users" role="tab" aria-controls="profile">Users</a>
                        <a class="list-group-item list-group-item-action" id="list-suppliers-list" href="/suppliers" role="tab" aria-controls="profile">Suppliers</a>
                        <a class="list-group-item list-group-item-action" id="list-messages-list"  href="/conditions" role="tab" aria-controls="messages">Conditions</a>
                        <a class="list-group-item list-group-item-action" id="list-settings-list"  href="/categories" role="tab" aria-controls="settings">Category</a>
                        <a class="list-group-item list-group-item-action" id="list-settings-list"  href="/staffwages" role="tab" aria-controls="settings">Staff Wages</a> 
                        <a class="list-group-item list-group-item-action" id="list-settings-list"  href="/shipcompanies" role="tab" aria-controls="settings">Shipment Company</a>
                        <a class="list-group-item list-group-item-action" id="list-settings-list"  href="/currencies" role="tab" aria-controls="settings">Currency</a>
                    </div>
                </div>
                <div class="col-md-9 order-md-2">
                    @yield('settings')
                </div>
        </div>
    </div>

@endsection