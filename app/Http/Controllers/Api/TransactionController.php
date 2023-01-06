<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function transactionList(Request$request)
    {
        if(!is_null($request->get('limit'))) {
            $transactions= tap(Transaction::latest()->paginate($request->limit)->appends('limit', $request->limit))->transform(function($transaction){
                $transaction['transaction_date'] = Carbon::parse($transaction->created_at)->toDateTimeString();
                return $transaction;
            });
        }else{
            $transactions= Transaction::latest()->get()->map(function($transaction){
                $transaction['transaction_date'] = Carbon::parse($transaction->created_at)->toDateTimeString();
                return $transaction;
            });
        }

        return $this->response($transactions);
    }
}
