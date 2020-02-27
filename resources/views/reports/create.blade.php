@extends('layouts.app')

@section('content')

<div id="wrapper">
    <div id="createproduct" class="container">
        <h3>Upload Products</h3>
        <form method="POST" action="/reports">
            @csrf
            <div class="field">
                <label class="label" for="date">Start Date</label>
                <div class="control">
                    <input class="input" type="date" name="startdate" placeholder="Enter Start Date"> 
                </div>
            </div> 

            <div class="field">
                <label class="label" for="date">End Date</label>
                <div class="control">
                    <input class="input" type="date" name="enddate" placeholder="Enter End Date"> 
                </div>
            </div> 

            <div class="field is-grouped">
                <div class="control">
                    <button class="button is-link" type="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

