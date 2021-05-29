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
      Yande Gadgets was started by the client in 2011. The name was based on the pronunciation of the family name Nyande. 
      The trading within the client’s house, but after an immediate within the first few months, they decided to rent out a 
      building within the city centre of Accra to begin selling the product. 
      The client hopes to expand its business into a chain by opening more stores across Africa.
      </p>
    </div>
  </div>



<div id="slidersection">
  <div id="sliderhead">
    <h3>YANDE GADGETS</h3>
    <h5>Top Categories</h5>
  </div>
</div>

<div class="row" id="productsection">
      <div class="col-md-3">
        <div class="product">
          <div class="product-image">
            <a href="" class="image">
              <img src="gallery/Iphone12.jpg" class="pic-1">
              <img src="gallery/Iphone12A.jpg" class="pic-2">
            </a>
              <ul class="links">
              </ul>       
            <div class="content">
              <span class="category"><a href="">Technology</a></span>
              <h4 class="title"><a href="">Smartphones</a></h4>
              <div class="price"><span>We offer a huge range of feature phones and smartphones, so you'll always be able to find the perfect match to suit you.</span></div>
            </div>
          </div>    
        </div>
      </div>

      <div class="col-md-3" >
        <div class="product">
          <div class="product-image">
            <a href="" class="image">
              <img src="gallery/LG TB-1586622730.jpg" class="pic-1">
              <img src="gallery/SoroundSoundSystem.png" class="pic-2">
            </a>
            <span class="discount">New items</span>
              <ul class="links">
              </ul>       
            <div class="content" style="margin-top: 9%;">
              <span class="category"><a href="">Technology</a></span>
              <h4 class="title"><a href="">TV/Audio</a></h4>
              <div class="price"><span>Our television selection is sure to cover what you need in your front room, or any other room for that matter!</span></div>
            </div>
          </div>    
        </div>
      </div>

      <div class="col-md-3">
        <div class="product">
          <div class="product-image">
            <a href="" class="image">
              <img src="gallery/Desktop-Computer.jpg" class="pic-1">
              <img src="gallery/imac-pink.jpg" class="pic-2">
            </a>
              <ul class="links"> 
              </ul>       
            <div class="content" style="margin-top: 9%;">
              <span class="category"><a href="">Technology</a></span>
              <h4 class="title"><a href="">Computing</a></h4>
              <div class="price"><span>Check out our incredible range of desktop PCs. We have over 200 desktop PC's to choose from! </span></div>
            </div>
          </div>    
        </div>
      </div>

      <div class="col-md-3">
        <div class="product">
          <div class="product-image">
            <a href="" class="image">
              <img src="gallery/Fujifilm-X-S10.jpg" class="pic-1">
              <img src="gallery/eos_range_tcm14-1266213.png" class="pic-2">
            </a>
              <ul class="links">
              </ul>       
            <div class="content" style="margin-top: 9%;">
              <span class="category"><a href="">Technology</a></span>
              <h4 class="title"><a href="">Cameras</a></h4>
              <div class="price"><span>Shop a huge range of cameras, lenses, photography accessories, videography, and drones at Yande Gadgets.</span></div>
            </div>
          </div>    
        </div>
      </div>
</div>
  



<div class="row" id="productadverisement">
    <div class="col-7">
     <img src="images/iphone-X-Banner.png" alt="" class="adImages">
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

