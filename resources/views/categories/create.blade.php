@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Create Categories</h3>
            <form method="POST" action="/categories/" onsubmit="myButton.disabled = true; return true;">
                @csrf
                {{-- Category Name/detail--}}
                <div class="field">
                    <label class="label" for="name">Categories Detail</label>
                    <div class="control">
                    <input class="input" type="text" name="type" id="name" > 
                    </div>
                </div>                 

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" name="myButton" type="submit">Submit</button>
                    </div>
                </div>   
                                   
            </form>
        </div>
    </div>    
@endsection

