@extends('layouts.app')

@section('content')

    
    <div class=" my-5">
        <div id="createtransaction" class="container">
            
            <h3 class="text-center">Record Transactions</h3>
            
            <form method="POST" action="/transactions" onsubmit="myButton.disabled = true; return true;">
                @csrf

                <section>
                    <div class="panel panel-header">
                                           
                    <table class="table">
                        <thead>
                          <tr>
                            <th scope="col">Description</th>
                            <th scope="col">Transaction Type</th>
                            <th scope="col">Amount (£)</th>
                            <th scope="col">Date</th>
                            <th scope="col"><a href="#" class="addRow"><button type="button" class="btn btn-primary">+</button></i></a></th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>
                                <input class="input" type="text" name="description[]" class="form-control"> 
                            </td>
                            <td>
                                <select name="type[]" id="type" class="form-control input-lg dynamic" data-dependent="labSubCat" required>
                                    <option value="">Select</option>
                                        @foreach($types as $cn)
                                            <option value="{{$cn->id}}">{{$cn->type}}</option>
                                        @endforeach
                                </select> 
                            </td>
                            <td>
                                <input class="input" type="number" name="amount[]" id="amount" required> 
                            </td>
                            <td>
                                <input class="input" type="date" name="date[]" max="{{$today}}" placeholder="Enter Date of Purchase" required>  
                            </td>
                            <td>
                                <a href="#" class="btn btn-danger remove">x</a>
                            </td>
                          </tr>
                         
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="border:none"></td>
                                <td style="border:none"></td>
                                <td style="border:none"></td>
                                <td style="border:none"></td>
                                <td style="border:none">
                                    <div class="field is-grouped">
                                        <div class="control">
                                            <button class="button is-link" name="myButton" type="submit">Submit</button>
                                        </div>
                                    </div> 
                                </td>
                            </tr>
                        </tfoot>
                      </table>                        
                     
                </section>    

                                               
            </form>
        </div>
    </div>    
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript">
 //to add more records  
    document.addEventListener('DOMContentLoaded', function () {
        $('.addRow').on('click', function(){
            addRow();
        });
        function addRow()
        {
            var tr = '<tr>' +
            ' <td><input class="input" type="text" name="description[]" class="form-control" required></td>'+
            '<td><select name="type[]" id="type" class="form-control input-lg dynamic" data-dependent="labSubCat" required><option value="">Select</option>@foreach($types as $cn)<option value="{{$cn->id}}">{{$cn->type}}</option>@endforeach</select></td>'+
            '<td><input class="input" type="number" name="amount[]" id="amount" required></td>'+
            '<td><input class="input" type="date" max="{{$today}}" name="date[]" placeholder="Enter Date of Purchase" required></td>'+
            '<td><a href="#" class="btn btn-danger remove">x</a></td>'
            '</tr>';
            $('tbody').append(tr);
        }
    });

    // to remove records
    $('.remove').live('click',function(){
        //this will ensure that when last/one record is displayed it wont remove it - this will count how many tr are there  
        var last = $('tbody tr').length;
            if(last==1){
                alert("You can not delete the last row");
        } 
        else {
            $(this).parent().parent().remove();
        }
    });
</script>