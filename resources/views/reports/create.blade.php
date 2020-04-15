@extends('layouts.app')

@section('content')
<br>
<br>
<div class="container col-md-10">
    <h3>Select Filter</h3>
    
    <div class="row">
        <div class="col-md-6 order-md-1 bg-success p-3 text-light">
            <p>
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever 
                since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. 
                It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
            </p>
        </div>
        <div id="wrapper" class = "col-md-6 order-md-2">
            <div id="createproduct" class="container">
                
                <form method="POST" action="/reports" onsubmit="myButton.disabled = true; return true;">
                    @csrf
                    <div class="field">
                       <div class="row">
                            <div class="col-md-3 order-md-1">
                                <label class="label" for="date">Start Date</label>
                            </div>
                            <div class="control col-md-6 order-md-2">
                                <input class="input" type="date"  max="{{$today}}" name="startdate" placeholder="Enter Start Date" required> 
                            </div>
                        </div> 
                    </div> 

                    <div class="field">
                        <div class="row">
                            <div class="col-md-3 order-md-1">
                                <label class="label" for="date">End Date</label>
                            </div>
                            <div class="control col-md-6 order-md-2">
                                <input class="input" type="date"  max="{{$today}}" name="enddate" placeholder="Enter End Date" required> 
                            </div>
                        </div>
                    </div> 

                    <div class="field is-grouped">
                        <div class="control">
                            <button class="button is-link pull-right" name="myButton" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

