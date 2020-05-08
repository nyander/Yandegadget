@extends('layouts.settingside')

@section('settings')

<div class="p-4 mx-6 text-center my-2">
    <div class="col-md-5 order-md-2">
        <form method="POST" action="/conversions/{{$currency->id}}" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">
            @csrf
            @method('PUT')

            <div class="field row">
                <label class="label col-md-5" for="rate">Rate</label>
                <div class="control col-md-6">
                    <input class="input" type="number" name="rate" id="rate" value="{{$currency->rate}}" required> 
                </div>
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <button class="btn btn-md btn-outline-primary" type="submit" name="myButton">Submit</button>
                </div>
            </div>                                
        </form>
    </div>
</div>

@endsection