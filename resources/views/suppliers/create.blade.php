@extends('layouts.app')

@section('content')

    <a href="{{ URL::previous() }}" class="btn btn-sm btn-outline-secondary mt-3 mb-2">Back</a>
    <div class="row ">
        
        <div class="col-md-5 order-md-1 my-auto">
            <div class="container bg-info text-white p-3 mr-auto">
               <p>
                Upload your details into the Yande Gadgets to enable you to upload products. 
                This information is required for Yande Gadgets to contact you. 
                Please fill in ALL fields of the forms. 
               </p>
            </div>
        </div>
        <div class="col-md-5 order-md-2">
            <h3>Upload Supplier</h3>
            <form method="POST" action="/suppliers" onsubmit="myButton.disabled = true; return true;">
                @csrf
                {{-- Supplier Name field--}}
                <div class="field row">
                    <label class="label col-md-6" for="name">Supplier Name</label>
                    <input class="input col-md-6" type="text" name="name" id="name" required>                     
                </div>    
                
                {{-- Supplier address --}}
                <div class="field row">
                    <label class="label col-md-6" for="condition_Notes">Address</label>
                    <input class="input col-md-6" name="address" id="address" required>
                </div>


                {{-- Supplier contact --}}
                <div class="field row">
                    <label class="label col-md-6" for="condition_Notes">Phone Number</label>
                    <input class="input col-md-6" type="tel" name="contact" id="contact" placeholder ="[0]1234567891"pattern="(0)[0-9]{10}" required>
                </div>

                {{-- Condititon Dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="date">User</label>
                    <select name="supplier_id" id="supplier_id" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat">                      
                    @if ($userIsSupplier)                                         
                            <option value= {{$currentUserid}}>{{$currentUsername}}</option>
                        
                    @else
                        <option value="{{$suppliersrole}}">Select User</option>
                        <option value=""> No Account</option> 
                        @foreach($suppliersrole as $supplier)
                            <option value="{{DB::table('users')->where('id', $supplier->user_id)->value('id')}}">{{DB::table('users')->where('id', $supplier->user_id)->value('name')}}</option>
                        @endforeach
                    @endif    
                    </select>
                </div>     

                <div class="field is-grouped">
                    <div class="control">
                        <button class="btn btn-sm btn-outline-primary float-right" type="submit" name="myButton">Submit</button>
                    </div>
                </div>                                
            </form>
        </div>
    </div>    
@endsection

