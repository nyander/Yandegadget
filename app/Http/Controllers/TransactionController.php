<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Transaction;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = DB::table('transactions')->orderBy('date', 'desc')->get();
        return view('transactions.index')->with(['transactions'=>$transactions]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dt = Carbon::now();
        $today = $dt->toDateString();
        $types = DB::table('transaction_types')->select('id','type')->get();
        return view('transactions.create')->with(['types'=>$types, 'today' => $today]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();    
        // Transaction::create($data)->id;    
        if(count($request->description) >= 0)
        {
            foreach($request->description as $item =>$v){
                $transaction = new Transaction;
                $transaction->date = $request->date[$item];
                $transaction->description = $request->description[$item];
                $transaction->type = $request->type[$item];
                $transaction->amount = $request->amount[$item];
                $transaction->save();
                // $data2 = array(
                //     'date'=> $request ->date[$trans],
                //     'description' => $request->type[$trans],                    
                //     'type' => $request->type[$trans],
                //     'amount' => $request->amount[$trans]
                // );
                // Transaction::insert($data2);
            };
        };
        
        return redirect('/transactions')->with('success', 'Transaction Recorded');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $transactions = Transaction::find($id);
        $transactions->delete();

        return redirect('/transactions')->with('success', 'The product has been deleted');
    }
}
