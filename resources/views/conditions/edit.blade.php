@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Update Conditions</h3>
            <form method="POST" action="/conditions/{{$condition->id}}">
                @csrf
                @method('PUT')
                {{-- Condition Name/detail--}}
                <div class="field">
                    <label class="label" for="name">Condition Detail</label>
                    <div class="control">
                    <input class="input" type="text" name="details" id="name" value="{{$condition->details}}"> 
                    </div>
                </div>    

                {{-- Condition Explanation--}}
                <div class="field">
                    <label class="label" for="cost">Condition Explanation</label>
                    <div class="control">
                        <input class="input" type="textarea" name="explanation" id="cost" value="{{$condition->explanation}}"> 
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

