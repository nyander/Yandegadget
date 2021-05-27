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
</div>

<div class="row container" id="productsection">
  <div class="col-md-3">
    <div class="product">
      <div class="product-image">
        <a href="" class="image">
          <img src="gallery/Iphone12.jpg" class="pic-1">
          <img src="gallery/Iphone12A.jpg" class="pic-2">
        </a>
        <a href="" class="cart">Add to Cart</a>
          <ul class="links">
            <li><a href=""><i class="fas fa-search"></i></a></li> 
            <li><a href=""><i class="far fa-heart"></i></a></li>
          </ul>       
        <div class="content">
          <span class="category"><a href="">Iphone</a></span>
          <h4 class="title"><a href="">Iphone 12</a></h4>
          <div class="price"><span>£50.00</span> £30.00</div>
        </div>
      </div>    
    </div>
  </div>

  <div class="col-md-3">
    <div class="product">
      <div class="product-image">
        <a href="" class="image">
          <img src="gallery/Iphone12.jpg" class="pic-1">
          <img src="gallery/Iphone12A.jpg" class="pic-2">
        </a>
        <span class="discount">New</span>
        <a href="" class="cart">Add to Cart</a>
          <ul class="links">
            <li><a href=""><i class="fas fa-search"></i></a></li> 
            <li><a href=""><i class="far fa-heart"></i></a></li>
          </ul>       
        <div class="content">
          <span class="category"><a href="">Iphone</a></span>
          <h4 class="title"><a href="">Iphone 12</a></h4>
          <div class="price"><span>£50.00</span> £30.00</div>
        </div>
      </div>    
    </div>
  </div>


  <div class="col-md-3">
    <div class="product">
      <div class="product-image">
        <a href="" class="image">
          <img src="gallery/Iphone12.jpg" class="pic-1">
          <img src="gallery/Iphone12A.jpg" class="pic-2">
        </a>
        <span class="discount">Sale</span>
        <a href="" class="cart">Add to Cart</a>
          <ul class="links">
            <li><a href=""><i class="fas fa-search"></i></a></li> 
            <li><a href=""><i class="far fa-heart"></i></a></li>
          </ul>       
        <div class="content">
          <span class="category"><a href="">Iphone</a></span>
          <h4 class="title"><a href="">Iphone 12</a></h4>
          <div class="price"><span>£50.00</span> £30.00</div>
        </div>
      </div>    
    </div>
  </div>


  <div class="col-md-3">
    <div class="product">
      <div class="product-image">
        <a href="" class="image">
          <img src="gallery/Iphone12.jpg" class="pic-1">
          <img src="gallery/Iphone12A.jpg" class="pic-2">
        </a>
        <a href="" class="cart">Add to Cart</a>
          <ul class="links">
            <li><a href=""><i class="fas fa-search"></i></a></li> 
            <li><a href=""><i class="far fa-heart"></i></a></li>
          </ul>       
        <div class="content">
          <span class="category"><a href="">Iphone</a></span>
          <h4 class="title"><a href="">Iphone 12</a></h4>
          <div class="price"><span>£50.00</span> £30.00</div>
        </div>
      </div>    
    </div>
  </div>
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

<!-- script section -->
<script type="text/javascript">
  $(document).ready(function(){
    $("#product-slider").owlCarousel({
      items:6,
      itemsDestop:[1199,2],
      itemsDestopSmall:[980,2],
      itemsMoile:[700,1],
      pagination:false,
      navigation:true,
      navigationText:["",""],
      autoPlay:true
    });
  });
</script>
@endsection

