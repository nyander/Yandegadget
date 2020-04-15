{{-- extend is basically extending another class using the @extend function which is extending the app file in the layout folder  --}}
@extends('layouts.app')

{{--inside the @section is what will be added in the app file, basically content to be added to your template--}}
@section('index')
            <div style="background-color:#008000; " class="p-5">
                <div class="my-4">
                    <h4 class=" d-flex justify-content-center text-light"> Find the latest technology in Yande Gadgets  </h4> 
                    <h1 class=" d-flex justify-content-center my-4 text-light">{{$title}}</h1>
                    <div class=" d-flex justify-content-center text-light">
                        <p class=" w-50 text-center text-light">
                            Yande Gadgets is a second-hand electrical retailer based in Ghana. 
                            The company specializes in Technology, Computing, Gaming and Appliances. 
                            The company purchases unwanted items, which is then shipped to Ghana for resale at their flagship store.
                        </p>
                    </div>
                </div>
            </div>
            <img src="/images/accra-1.jpg" style="width:100%; max-height:10em; object-fit: cover;">

            <div class="row my-5 justify-content-center">
                <div class="col-md-4 my-3">
                {{-- History --}}      
                    <div class="text-center">        
                        <img src="/images/history-24px.svg" style="height:5em;" class="justify-content-center"> 
                    </div>
                    <h5 class="mt-2 justify-content-center text-center">History</h5>
                    <p class="justify-content-center text-justify">
                        Yande Gadgets was started by the client in 2011. The name was based on the 
                        pronunciation of the family name Nyande.   
                        The trading within the client’s house, but after an immediate within the first few months, 
                        they decided to rent out a building within the city centre of Accra to begin selling the product. 
                        The client hopes to expand its business into a   chain by opening more stores across Africa.
                    </p>
                </div>

                <div class="col-md-4 my-auto">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.412449682287!2d-0.1811137854828162!3d5.6533232959020125!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdf9c7eaac8b6d5%3A0x63e1fd54f881e500!2sLegon%20City%20Mall!5e0!3m2!1sen!2suk!4v1586722932153!5m2!1sen!2suk" width="600" height="450" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
                    <p class= "mt-3"><b>Address:</b> 27 Ayawaso, Legon Boundary, Accra, Ghana</p>
                    <p><b>Contact:</b> <a href="tel:01234567890">01234567890</a></p>
                    <p><b>Email:</b><a href="mailto:enquiries@yandegadgets.com"> Enquiries@Yandegadgets.com</a></p>
                </div>
            </div>
           
@endsection
