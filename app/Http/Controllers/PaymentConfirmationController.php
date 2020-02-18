<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentConfirmationController extends Controller
{
  public function index(){
      if(! session()->has('success')){
          return redirect('/');
      }

      return view('requests.index');
  }
}
