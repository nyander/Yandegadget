@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Update Category</h3>
            <form method="POST" action="/categories/{{$category->id}}" onsubmit="myButton.disabled = true; return true;">
                @csrf
                @method('PUT')
                {{-- Category Name/detail--}}
                <div class="field">
                    <label class="label" for="name">Category:</label>
                    <div class="control">
                    <input class="input" type="text" name="type" id="name" value="{{$category->type}}"> 
                    </div>
                </div>   

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit" name="myButton">Submit</button>
                    </div>
                </div>   
                                   
            </form>
        </div>
    </div>    
@endsection

