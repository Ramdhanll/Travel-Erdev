<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\TransactionSuccess;
use App\Transaction;
use Illuminate\Support\Facades\Mail;
use Midtrans\config;
use Midtrans\notification;

class MidtransController extends Controller
{
    public function notificationHandler(Request $request){
        // set configurasi midtrans https://github.com/Midtrans/midtrans-php#23-handle-http-notification
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');

        // Buat instance notification
        $notification = new Notification();

        // Pecah order id agar bisa diterima oleh database
        $order = explode('-',$notification->order_id);

        // Assign ke variable untuk memudahkan coding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $order[1];

        // Cari transaksi berdasarkan id

        $transaction = Transaction::findOrFail($order_id);

        // Handler notification status midtrans

        if($status == "capture"){
            if($type == "credit_card"){
                if($fraud == "challenge"){
                    $transaction->transaction_status = "CHALLENGE";
                } else {
                    $transaction->transaction_status = "SUCCESS";
                }
            }
        } else if($status == "settlement"){
            $transaction->transaction_status = "SUCCESS";
        } else if($status == "pending"){
            $transaction->transaction_status = "PENDING";
        } else if($status == "deny"){
            $transaction->transaction_status = "FAILED";
        } else if($status == "expire"){
            $transaction->transaction_status = "EXPIRED";
        } else if($status == "cancel"){
            $transaction->transaction_status = "FAILED";
        }

        // Simpan transaksi

        $transaction->save();

        if ($transaction){ // jika $transaction->save() berhasil
            if ($status == 'capture' && $fraud == 'accept'){
                Mail::to($transaction->user)->send(
                    new TransactionSuccess($transaction)
                );
            }
            else if ($status == 'settlement'){
                Mail::to($transaction->user)->send(
                    new TransactionSuccess($transaction)
                );
            }
            else if ($status == 'success'){
                Mail::to($transaction->user)->send(
                    new TransactionSuccess($transaction)
                );
            }
            else if ($status == 'capture' && $fraud == "challenge"){
                return response()->json([
                    'meta'  => [
                        'code'  => 200,
                        'message'   => 'Midtrans Payment Challenge'
                    ]
                ]);
            }
            else {
                return response()->json([
                    'meta'  => [
                        'code'  => 200,
                        'message'   => 'Midtrans Payment Not Settlement'
                    ]
                ]);
            }
            return response()->json([
                'meta'  => [
                    'code'  => 200,
                    'message'   => 'Midtrans Notification Success'
                ]
            ]);
        }

    }

    public function finishRedirect(Request $request){
        $transaction_status = $request->input('transaction_status');
        if ($transaction_status == 'pending'){
            return view('pages.unfinish');
        } else if ($transaction_status == 'success'){
            return view('pages.success');
        } else {
            return view('pages.failed');
        }
    }

    public function unfinishRedirect(Request $request){
        return view('pages.unfinish');
    }

    public function errorRedirect(Request $request){
        return view('pages.failed');
    }

    
}
