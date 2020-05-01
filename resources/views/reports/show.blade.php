@extends('layouts.app')
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
@section('content')

    
    <div class="row my-5">
        <div class=" col-md-6 order-md-1 " id="balanceSheet" >
            <div class="p-2">
                @if($totalassets > $totalequityliability)
                    <b class="text-danger">
                        Total Assets is greater than your Total Equity & Liability, please check that
                        all equities and liabilities have been recorded 
                    </b>
                @elseif($totalequityliability > $totalassets)
                    <b class="text-danger">
                        Total Equity & Liability is greater than your Total Assets, please check that
                        all assets have been recorded 
                    </b>
                @else
                    <p class="text-success">
                        Financial status of company below
                    </p>    
                @endif
            </div>
            <h6 class="card-header bg-success text-light"> 
                Balance Sheet for the Period: 
                <br>
                {{$startdate}} to {{$enddate}} 
            </h6>
            {{-- <h6 class="card-header bg-primary text-light"> 
                Financial Balance Sheet for period 
                <br>
                {{$startdate}} to {{$enddate}} 
            </h6> --}}
            <div class="statementsection">
                <div class="currentAsset">                    
                    <h5  class="mt-2"> Assets <h5>
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

        <div class=" col-md-6 order-md-2 " id="incomestatement">

            <div class="p-2">
                @if($gross_profit - $totalexpenses - $totalotherincome < 10)
                    <b class="text-danger">
                        The Net Income is very low, recommendation would be to reduce incurring cost & increase sales
                    </b>
                @else
                    <b class="text-success">
                        Net Income is Positive
                    </b>
                @endif
            </div>
            
            <h6 class="card-header bg-success text-light"> 
                Income Statement for the Period: 
                <br>
                {{$startdate}} to {{$enddate}} 
            </h6>
            <div class="income_statement card-body">
                <div class="salesRevenue">
                    <h6 class>Sales Revenue</h6>
                    <span class="leftstatment"> Net sales</span> <span class="rightstatment">£ {{$retained_earnings}}</span>
                    <br>
                    <span class="leftstatment"> Cost of Goods </span> <span class="rightstatment">£ {{$product_cost}}</span>
                    <br>
                    <hr>
                    <span class="leftstatment"> Gross Profit</b> <span class="rightstatment">£ {{$gross_profit}}</span>
                    
                </div>
                <hr>  
                <br> 
                <div class="operatingExpenses">
                    <h6>Operating Expenses</h6>
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
                    <hr>
                    <span class="leftstatment"> Total Operation Expenses </span> <span class="rightstatment">£ {{$totalexpenses}}</span>
                    <hr> 
                    <div class="grossprofit">
                        <b class="leftstatment"> Operating Income</b> <span class="rightstatment">£ {{$gross_profit - $totalexpenses}}</span>
                        <br>
                    </div>
                </div>
                <hr>
                <br> 
                <div class="operatingExpenses">
                    <h6>Other Revenue & Expenses </h6>
                    <span class="leftstatment"> Other Income </span> <span class="rightstatment">£ {{$other_Income}}</span>
                    <br>
                    <span class="leftstatment"> Income before provision for income taxes  </span> <span class="rightstatment">£ {{$income_provision}}</span>
                    <br>
                    <span class="leftstatment"> Provision for income taxes </span> <span class="rightstatment">£ {{$provision_for_income_taxes}}</span>
                    <br>                    
                    <hr>
                    <span class="leftstatment"> Total </span> <span class="rightstatment">£ {{$totalotherincome}}</span>
                    <hr>                   
                    <br>
                    <div class="grossprofit">
                        <b class="leftstatment"> Net Income</b> <span class="rightstatment">£ {{$gross_profit - $totalexpenses - $totalotherincome}}</span>
                        <br>
                    </div>
                </div>
            </div>
            <br>
            <div class="card p-2">
                <div id="chartContainer1" style="height: 370px; width: 100%;"></div>
            </div>
             <br>
        </div>
    </div>

    <div class="row">   
        <div class="col-lg-8 mt-5">
            <div id="chartContainer" style="height: 370px; width: 100%;"></div>
        </div>
        <div class="col-lg-4 mt-5 bg-success text-light p-3">
            <p>orem Ipsum is simply dummy text of the printing and typesetting industry. </p>
                 Phone: {{$sold_products_category_1}}</p>
                 <p>Appliances: {{$sold_products_category_2}}</p>
                 <p>TV & Audio: {{$sold_products_category_3}}</p>
                 <p>Computing: {{$sold_products_category_4}}</p>
                 <p>Gaming: {{$sold_products_category_5}} </p>
                 <p>Cameras: {{$sold_products_category_6}}</p>
                 <p>Smart Tech: {{$sold_products_category_7}}</p>
            
        </div>
    </div> 
    
    
    
    

    
    
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