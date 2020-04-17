@extends('layouts.app')

@section('content')
<a href="/requests" ><button class="btn btn-md btn-outline-secondary my-4">Back</button></a>

<div class="card justify-content-center " style="max-width:40em;">
    <div class="card-header">
        <h3>{{$requests->name}}</h3>
        <a href="/requests/{{$requests->id}}/edit" class="ml-5" > <button class="btn btn-md btn-outline-primary pull-right ">Edit</button> </a>
    </div>
    <div class="card-body">
            <div class="row">
                <p class="col-md-6"><b>Product Type:</b></p>
                <p class="col-md-6">{{$categories}} </p>
            </div>
            <div class="row">
                <p class="col-md-6"><b>Condition: </b></p>
                <p class="col-md-6">{{$condition}}</p>
            </div>
            <div class="row">
                <p class="col-md-6"><b>Deposit Paid:</b></p>
                <p class="col-md-6">
                    @if($requests->deposit_paid == 1)
                        Yes
                    @else
                        No
                    @endif
                </p>
            </div>
            <div class="row">
                <p class="col-md-6"><b>Deposit Amount:</b></p>
                <p class="col-md-6">£{{$requests->deposit}}</p>
            </div>
    </div>
</div>

@endsection