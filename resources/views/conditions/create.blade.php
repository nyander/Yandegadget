@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Create Conditions</h3>
            <form method="POST" action="/conditions/">
                @csrf
                {{-- Condition Name/detail--}}
                <div class="field">
                    <label class="label" for="name">Condition Detail</label>
                    <div class="control">
                    <input class="input" type="text" name="details" id="name" > 
                    </div>
                </div>    

                {{-- Condition Explanation--}}
                <div class="field">
                    <label class="label" for="cost">Condition Explanation</label>
                    <div class="control">
                        <input class="input" type="textarea" name="explanation" id="cost" > 
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

