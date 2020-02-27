@extends('layouts.app')
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
@section('content')

    <h3> Results </h3>
    <br>
    <div class="card" id="balanceSheet">
    <h4> Financial Balance Sheet for period: {{$startdate}} to {{$enddate}} </h4>
        <div class="statementsection">
            <h5> Assets <h5>
                <h6>Current Asset</h6>
                <span class="leftstatment"> Stocks (inventory) </span> <span class="rightstatment">£ {{$stock}}</span>
                <br>
                <span class="leftstatment"> Cash </span> <span class="rightstatment">£ {{$retained_earnings}}</span>
                <hr>                
                <b class="leftstatment"> Total </b> <span class="rightstatment">£ {{$assets_total}}</span>                
        </div>
        <br>
        <div class="statementsection">
            <h5> Liabilities <h5>
                <h6>Current Liabilities</h6>
                <span class="leftstatment"> Delivery Charges </span> <span class="rightstatment">£ {{$delivery_charge}}</span>
                <br>
                <span class="leftstatment"> Staff Payable (wages) </span> <span class="rightstatment">£ {{$staff_payable}}</span>
                <hr>                
                <b class="leftstatment"> Total </b> <span class="rightstatment">£ {{$liability_total}}</span>
        </div>
        <br>
        <div class="statementsection">
            <h5> Equity </h5>
                <span class="leftstatment"> Retained Earnings </span> <span class="rightstatment">£ {{$retained_earnings}}</span>
                <hr>                
                <b class="leftstatment"> Total </b> <span class="rightstatment">£ {{$retained_earnings}}</span>
        </div>
    </div>

    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
    <br>
    <div id="chartContainer1" style="height: 370px; width: 100%;"></div>
    <br>
    <div id="chartContainer2" style="height: 370px; width: 100%;"></div>
    

    
    
@endsection

<script>
    window.onload = function () {
    
    var chart = new CanvasJS.Chart("chartContainer", {
        
        animationEnabled: true,
        theme: "light2", // "light1", "light2", "dark1", "dark2"
        title:{
            text: "Sales Based on Category from {{$startdate}} to {{$enddate}}"
        },
        axisY: {
            title: "Quantity Sold"
        },
        axisX: {
            title: "Type (Category)"
        },
        data: [{        
            type: "column", 
            dataPoints: [      
                { y: {{$sold_products_category_1}}, label: "Phone"},
                { y: {{$sold_products_category_2}},  label: "Appliances" },
                { y: {{$sold_products_category_3}},  label: "TV & Audio" },
                { y: {{$sold_products_category_4}},  label: "Computing" },
                { y: {{$sold_products_category_5}},  label: "Gaming" },
                { y: {{$sold_products_category_6}}, label: "Cameras" },
                { y: {{$sold_products_category_7}},  label: "Smart Tech" }
            ]
        }]
    });

    var chart1 = new CanvasJS.Chart("chartContainer1", {
        
        animationEnabled: true,
        theme: "light2", // "light1", "light2", "dark1", "dark2"
        title:{
            text: "Sales Based on Condition from {{$startdate}} to {{$enddate}}"
        },
        axisY: {
            title: "Quantity Sold"
        },
        axisX: {
            title: "Condition"
        },
        data: [{        
            type: "column", 
            dataPoints: [      
                { y: {{$sold_products_condition_1}},  label: "A" },
                { y: {{$sold_products_condition_2}},  label: "B" },
                { y: {{$sold_products_condition_3}},  label: "C" }
            ]
        }]
    });

    

    
    
    chart.render();
    chart1.render();
    
    
    }
    </script>