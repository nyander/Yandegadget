<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ShipCompany;

class ShipCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = ShipCompany::all();
        return view('shipcompanies.index')->with(['companies'=>$companies]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shipcompanies.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'address' => 'required',            
            'postcode' => 'required',
            'number' => 'required',
            'extra_information' => 'required',
        ]);

        $company = new ShipCompany;
        $company->name = request("name");
        $company->address = request("address");
        $company->postcode = request("postcode");
        $company->number = request("number");
        $company->extra_information = request("extra_information");
        $company->save();
        
        return redirect('/shipcompanies')->with('success', 'Company has been added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = ShipCompany::find($id);
        return view('shipcompanies.show')->with(['company'=> $company]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = ShipCompany::find($id);
        return view('shipcompanies.edit')->with(['company'=> $company]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $company = ShipCompany::find($id);
        $company->name = request("name");
        $company->address = request("address");
        $company->postcode = request("postcode");
        $company->number = request("number");
        $company->extra_information = request("extra_information");
        $company->save();
        
        return redirect('/shipcompanies')->with('success', 'Company has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = ShipCompany::find($id);
        $company->delete();
        
        return redirect('/shipcompanies')->with('success', 'The Company has been deleted');
    }
}
