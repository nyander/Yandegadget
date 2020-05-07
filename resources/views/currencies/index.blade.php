@extends('layouts.settingside')

@section('settings')

<div class="bg-danger p-4 mx-6 text-light text-center my-2">
    <h5 >Currency Rate</h5>
    <p>
        The currency conversion rate changes quite alot. Ensure to keep up to date with the conversion rates, 
        The currently Recorded currency rate is:
    </p>
    <p>
        @foreach ($currencies as $currency)
            <h4>£{{$currency->rate}}</h4>            
            <a href="/currencies/{{$currency->id}}/edit"><button type="button" class="btn btn-sm btn-outline-light">Change</button></a>
        @endforeach
        
    </p>
</div>

@endsection