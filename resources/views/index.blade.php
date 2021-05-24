{{-- extend is basically extending another class using the @extend function which is extending the app file in the layout folder  --}}
@extends('layouts.app')

{{--inside the @section is what will be added in the app file, basically content to be added to your template--}}
@section('index')
{{-- $title is the variable name in which the Postcontroller will replace with the variable $tite - so this title will be variable in the postcontroller    --}}
    
  <div class="row" id="headerrow">
    <div class="col-7" id="headerimage"></div>


    <div class="col-5"id="rightHeader">
      <h1>YANDE GADGETS</h1>
      <h4>Technology Store</h4>
      <p>
        Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, 
        sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum 
        dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor 
      </p>
    </div>
  </div>



<div id="slidersection">
  <div id="sliderhead">
    <h3>YANDE GADGETS</h3>
    <h5>Top Categories</h5>
  </div>

  <div class="box">
      <!-- image box -->
      <div class="slide-img"> 
        <img src="gallery/iphone12.jpg" alt="text example">
      </div>

      <!-- Detail box -->
      <div class="detail-box">
        <span class="productCategory">Mobile Phones</span>
        <h5 class="productName"><a href="https://www.google.com/">Iphone XR</a></h5>
        <p class="productPrice"> £123.25 </p>

      <div>
        
      </div>
      </div>
  </div>
  <hr class="sectionHighlight">
</div>
  

<div class="row" id="productadverisement">
    <div class="col-7">

    </div>

    <div class="col-5">
        <h1 id="adtitle">YANDE GADGETS</h1>
        <h4>Technology Store</h4>
        <p>
          Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, 
          sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. 
        </p>
        <button class="productButton">Explore Products</button>
  </div>
</div>              
@endsection

