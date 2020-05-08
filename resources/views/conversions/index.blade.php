@extends('layouts.settingside')

@section('settings')

<div class="bg-danger p-4 mx-6 text-light text-center my-2">
    <h5 >Conversion Rate</h5>
    <p>
        The currency conversion rate changes quite alot. Ensure to keep up to date with the conversion rates, 
        The currently Recorded currency rate is:
    </p>
    <p>
        @foreach ($conversions as $conversion)
            <h4>£{{$conversion->rate}}</h4>            
            <a href="/conversions/{{$conversion->id}}/edit"><button type="button" class="btn btn-sm btn-outline-light">Change</button></a>
        @endforeach
        
    </p>
</div>

@endsection