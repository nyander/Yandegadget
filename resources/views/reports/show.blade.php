@extends('layouts.app')
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
@section('content')

    <h3> Results </h3>
    <br>
    <div class="card" id="balanceSheet">
    <h4> Financial Balance Sheet for period: {{$startdate}} to {{$enddate}} </h4>
        <div class="statementsection">
            <div class="currentAsset">
            <h5> Assets <h5>
                <h6>Current Asset</h6>
                <span class="leftstatment"> Account Recievables</span> <span class="rightstatment">£ {{$account_Recievable}}</span>
                <br>
                <span class="leftstatment"> Cash </span> <span class="rightstatment">£ {{$cash}}</span>
                <br>     
                <span class="leftstatment"> stock </span> <span class="rightstatment">£ {{$stock}}</span>
                <br>                      
                <span class="leftstatment"> Prepaid Expense </span> <span class="rightstatment">£ {{$prepaid_Expense}}</span>
                <br>  
                <span class="leftstatment"> Other Current Assets </span> <span class="rightstatment">£ {{$othercurrentassets}}</span>
                <br>
                <br>
            </div>
            <div class="non-currentAsset">
                <h6>Non-Current Asset</h6>
                <span class="leftstatment"> land</span> <span class="rightstatment">£ {{$land}}</span>
                <br>
                <span class="leftstatment"> building </span> <span class="rightstatment">£ {{$building}}</span>
                <br>     
                <span class="leftstatment"> vehicles </span> <span class="rightstatment">£ {{$vehicles}}</span>
                <br>                      
                <span class="leftstatment"> machinery </span> <span class="rightstatment">£ {{$machinery}}</span>
                <br>  
                <span class="leftstatment"> computer </span> <span class="rightstatment">£ {{$computer}}</span>
                <br>
                <span class="leftstatment"> Other Fixed Assets </span> <span class="rightstatment">£ {{$otherfixedassets}}</span>
                <br>
                <br>
            </div>
            <hr>
            <div class="totalAsset">
                <b class="leftstatment"> Total Assets</b> <span class="rightstatment">£ {{$totalassets}}</span>
                <br>
            </div>           
        </div>
        <br>
        <div class="statementsection">
            <div class="currentLiability">
                <h5> Liabilities <h5>
                    <h6>Current Liabilities</h6>
                    <span class="leftstatment"> Delivery Charges </span> <span class="rightstatment">£ {{$delivery_charge}}</span>
                    <br>
                    <span class="leftstatment"> Staff Payable (wages) </span> <span class="rightstatment">£ {{$sum}}</span>
                    <br>
                    <span class="leftstatment"> Account Payable </span> <span class="rightstatment">£ {{$account_payable}}</span>
                    <br>
                    <span class="leftstatment"> Rent </span> <span class="rightstatment">£ {{$rent}}</span>
                    <br>
                    <span class="leftstatment"> Overdrafts </span> <span class="rightstatment">£ {{$overdrafts}}</span>
                    <br>
                    <span class="leftstatment"> Current Lease Payable </span> <span class="rightstatment">£ {{$currentLeasePayaple}}</span>
                    <br>
                    <span class="leftstatment"> Customer Repayment </span> <span class="rightstatment">£ {{$customerRepayment}}</span>
                    <br>
                    <span class="leftstatment"> OtherCurrentLiabilities </span> <span class="rightstatment">£ {{$otherCurrentLiabilities}}</span>
                    <br>
                    <br>
                </div>
                    
            <div class="currentLiabilities">
            
                <h6>Non Current Liabilities</h6>
                <span class="leftstatment"> long Term Debts </span> <span class="rightstatment">£ {{$longTermDepts}}</span>
                <br>
                <span class="leftstatment"> Pension Fund Liability </span> <span class="rightstatment">£ {{$pensionFundLiability}}</span>
                <br>
                <span class="leftstatment"> PrefferedTaxedLiability </span> <span class="rightstatment">£ {{$prefferedTaxedLiability}}</span>
                <br>
                <span class="leftstatment"> Other Long Term Liability </span> <span class="rightstatment">£ {{$otherLongTermLiability}}</span>
                <br>                
                <br>
            </div>
            <hr>
            <div class="totalliability">
                <b class="leftstatment"> Total Liabilities</b> <span class="rightstatment">£ {{$totalliability}}</span>
                <br>
            </div>
        </div>
        <br>
        <div class="statementsection">
            <h5> Equity </h5>
                <span class="leftstatment"> Retained Earnings </span> <span class="rightstatment">£ {{$retained_earnings}}</span>
                <br>
                <span class="leftstatment"> Treasury Stock </span> <span class="rightstatment">£ {{$treausryStock}}</span>
                <br>
                <span class="leftstatment"> Common Stock  </span> <span class="rightstatment">£ {{$commonStock}}</span>
                <br>
                <span class="leftstatment"> Other Shareholder Equity </span> <span class="rightstatment">£ {{$otherShareholderEquity}}</span>

                <br>
                <br>
                
                <div class="totalequity">
                    <b class="leftstatment"> Total Equity</b> <span class="rightstatment">£ {{$totalequity}}</span>
                    <br>
                </div>

                <hr>    
                <hr>            
                <b class="leftstatment"> Total Equity & Liability  </b> <span class="rightstatment">£ {{$totalequityliability}}</span>
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