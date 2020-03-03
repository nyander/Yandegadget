@extends('layouts.app')

@section('content')
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<a href="{{route('reports.create')}}"><button type="button" class="btn btn-success" >Filter Products</button></a>
<a href="{{route('transactions.create')}}"><button type="button" class="btn btn-primary" >Record Transactions</button></a>
<div id="chartContainer" style="height: 300px; width: 100%;"></div>
<div id="chartContainer1" style="height: 300px; width: 100%;"></div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">Transactions</div>
                
                

                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Description</th> 
                            <th scope="col">Amount</th>                           
                            <th scope="col"><a href="{{route('transactions.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                            
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($transactions as $transaction)
                        <tr>
                            <th scope="row">{{$transaction->id}}</th>
                            <td>{{$transaction->date}}</td>
                            <td>{{$transaction->description}}</td>
                            <td>{{$transaction->amount}}</td>
                            <td>                          
                                
                                
                                @can('manage-users')
                                <form action="{{route('transactions.destroy', $transaction->id)}}" method="POST" class="float-left">
                                @csrf
                                {{method_field('DELETE')}}
                                <button type="submit" class="btn btn-danger">Delete</button>
                                </form> 
                                @endcan
                                
                            </td>
                        </tr>    
                        @endforeach   
                                               
                        </tbody>
                      </table>                      
                </div>
            </div>
        </div>
    </div>


@endsection
<script>
    // USER SIGN UPS    
    function GetMonthName(monthNumber) {
        monthNumber = monthNumber < 0 ? 11 : monthNumber;
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months[monthNumber];
        }
        
    var d = new Date();
    var newMonth = d.getMonth();
    var prevMonth = d.getMonth()-1;
    var prevMonth1 = d.getMonth()-2;
    var prevMonth2 = d.getMonth()-3;
    var prevMonth3 = d.getMonth()-4;
    var prevMonth4 = d.getMonth()-5;
    var prevMonth5 = d.getMonth()-6;


    //Month 1 - Current    
    if (newMonth < 0 ){
        newMonth += 12;      
        d.setYear(d.getYear()-1)  
    }
    else if (newMonth > 12){
        newMonth -= 12; 
        d.setYear(d.getYear()+1)
    }

    //Month 2
    if (prevMonth < 0 ){
        prevMonth += 12;      
        d.setYear(d.getYear()-1)  
    }
    else if (prevMonth > 12){
        prevMonth -= 12; 
        d.setYear(d.getYear()+1)
    }


    //Month 3
    if (prevMonth1 < 0 ){
        prevMonth1 += 12;      
        d.setYear(d.getYear()-1)  
    }
    else if (prevMonth1 > 12){
        prevMonth1 -= 12; 
        d.setYear(d.getYear()+1)
    }

    //Month 4
    if (prevMonth2 < 0 ){
        prevMonth2 += 12;      
        d.setYear(d.getYear()-1)  
    }
    else if (prevMonth2 > 12){
        prevMonth2 -= 12; 
        d.setYear(d.getYear()+1)
    }

    //Month 5
    if (prevMonth3 < 0 ){
        prevMonth3 += 12;      
        d.setYear(d.getYear()-1)  
    }
    else if (prevMonth3 > 12){
        prevMonth3 -= 12; 
        d.setYear(d.getYear()+1)
    }


    //Month 6
    if (prevMonth4 < 0 ){
        prevMonth4 += 12;      
        d.setYear(d.getYear()-1)  
    }
    else if (prevMonth4 > 12){
        prevMonth4 -= 12; 
        d.setYear(d.getYear()+1)
    }

    
        
    window.onload = function () {
    
    var chart = new CanvasJS.Chart("chartContainer", {
        animationEnabled: true,
        theme: "light2",
        title:{
            text: "User Sign Ups"
        },
        axisX:{            
            title: "Months"
        },
        axisY:{
            includeZero: false,
            title: "Number of Users"
        },
        data: [{        
            type: "line",       
            dataPoints: [
                { y: {{$current_month_users}}, label: GetMonthName(newMonth)},
                { y: {{$last_month_users}}, label: GetMonthName(prevMonth)},
                { y: {{$last_2_month_users}}, label: GetMonthName(prevMonth1) },
                { y: {{$last_3_month_users}}, label: GetMonthName(prevMonth2) },
                { y: {{$last_4_month_users}}, label: GetMonthName(prevMonth3) },
                { y: {{$last_5_month_users}}, label: GetMonthName(prevMonth4) },
                
                          
            ]
        }]
    });


    var chart1 = new CanvasJS.Chart("chartContainer1", {
        animationEnabled: true,
        theme: "light2",
        title:{
            text: "Sales In the Past 6 Months"
        },
        axisX:{            
            title: "Months"
        },
        axisY:{
            includeZero: false,
            title: "Quantity"
        },
        data: [{        
            type: "line",       
            dataPoints: [
                { y: {{$current_month_sold_products}}, label: GetMonthName(newMonth)},
                { y: {{$last_1_month_sold_products}}, label: GetMonthName(prevMonth)},
                { y: {{$last_2_month_sold_products}}, label: GetMonthName(prevMonth1) },
                { y: {{$last_3_month_sold_products}}, label: GetMonthName(prevMonth2) },
                { y: {{$last_4_month_sold_products}}, label: GetMonthName(prevMonth3) },
                { y: {{$last_5_month_sold_products}}, label: GetMonthName(prevMonth4) },
                
                          
            ]
        }]
    });
    chart.render();
    chart1.render();
    
    }

    </script>