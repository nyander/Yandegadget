<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Transaction;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }
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
        $this->validate($request,[
            'description.*' => 'required',
            'type.*' => 'required',
            'amount.*' => 'required',
            'date.*' => 'required',  
        ]);

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
            };
        };
        
        return redirect('/transactions')->with('success', 'Transaction Recorded');
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
