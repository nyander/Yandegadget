@extends('layouts.app')

@section('content')
<br>
<br>
<div class="container col-md-10">
    <h3>Select Filter</h3>
    
    <div class="row">
        <div class="col-md-6 order-md-1 bg-success p-3 text-light">
            <p>
                To determine the performance of the Yande gadgets within a certain period of time, select the start and end date of the period 
                you would like to view. Ensure that start date is before the end date.    
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

