@extends('layouts.app')

@section('content')

    <?php 
    $dataPoints = array( 
        array("y" => DB::table('products')->whereColumn([
                ['updated_at', '<', $currentmonth],])->count(), "label" => "Germany" ),
        array("y" => 1.94, "label" => "France" ),
        array("y" => 1.55, "label" => "China" ),
        array("y" => 1.55, "label" => "Russia" ),
        array("y" => 1.99, "label" => "Switzerland" ),
        array("y" => 1.215, "label" => "Japan" ),
        array("y" => 1.453, "label" => "Netherlands" )
    );
    ?>

    @section('content')
    <div id="chartContainer" style="height: 370px; width: 100%;"></div>

@endsection

<script>
    window.onload = function() {
     
    var chart = new CanvasJS.Chart("chartContainer", {
        animationEnabled: true,
        theme: "light2",
        title:{
            text: "Previous Months Sales Month Sales"
        },
        axisY: {
            title: "Sales Amount"
        },
        data: [{
            type: "column",
            yValueFormatString: "#,##0.## tonnes",
            dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
        }]
    });
    chart.render();
     
    }
</script>