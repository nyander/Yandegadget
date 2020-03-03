@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createwages" class="container">
            <h3>Upload Supplier</h3>
            <form method="POST" action="/staffwages">
                @csrf
                
                
                {{-- Start Date --}}
                <div class="field">
                    <label class="label" for="startdate">Start Date</label>
                    <div class="control">
                        <input class="input" type="date" name="startdate" placeholder="Enter Start Date"> 
                    </div>
                </div>  


                {{-- Wages field--}}
                <div class="field">
                    <label class="label" for="wages">Wages ({{$currency}})</label>
                    <div class="control">
                        <input class="input" type="number" name="wages" id="wages"> 
                    </div>
                </div>

                {{-- End Date --}}
                <div class="field">
                    <label class="label" for="enddate">End Date</label>
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

