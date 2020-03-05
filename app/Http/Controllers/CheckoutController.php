<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\TransactionDetail;
use App\TravelPackage;
use Mail;
use App\Mail\TransactionSuccess;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Midtrans\config;
use Midtrans\snap;

class CheckoutController extends Controller
{
    public function index(Request $request, $id){
        
        $item   = Transaction::with([
            'details','travel_package','user'
        ])->findOrFail($id);
        return view('pages.checkout',[
            'item'  =>  $item
        ]);
    }

    public function process(Request $request, $id){

        $travel_packages = TravelPackage::findOrFail($id);
    
        $transaction    = Transaction::create([
            'travel_packages_id'    => $id,
            'users_id'              => Auth::user()->id,
            'additional_visa'       => 0,
            'transaction_total'     => $travel_packages->price * 1000,
            'transaction_status'    => 'IN_CART'
        ]);

        TransactionDetail::create([
            'transactions_id'    => $transaction->id,
            'username'          => Auth::user()->username,
            'nationality'       => 'IND',
            'is_visa'           =>  false,
            'doe_passport'      =>  Carbon::now()->addYears(5)
        ]);

        return redirect()->route('checkout',$transaction->id);
    }
    
    public function remove(Request $request, $detail_id){
        
        $item   = TransactionDetail::findOrFail($detail_id);

        $transaction = Transaction::with(['details','travel_package'])
            ->findOrFail($item->transactions_id);

        if ($item->is_visa) {
            $transaction->transaction_total -= 190;
            $transaction->additional_visa -= 190;
        }

        $transaction->transaction_total -= $transaction->travel_package->price;
        $transaction->save();
        $item->delete();
        
        return redirect()->route('checkout', $item->transactions_id);
    }
    
    public function create(Request $request, $id){

        $request->validate([
            'username'  =>  'required|string|exists:users,username',
            'is_visa'   =>  'required|boolean',
            'doe_passport'  =>  'required'
        ]);

        $data = $request->all();
        $data['transactions_id'] = $id;

        TransactionDetail::create($data);

        $transaction    = Transaction::with([
            'travel_package'
        ])->find($id);

        if ($request->is_visa) {
            $transaction->transaction_total += 190;
            $transaction->additional_visa += 190;
        }

        $transaction->transaction_total += $transaction->travel_package->price;

        $transaction->save();
        
        return redirect()->route('checkout', $id);
    }

    
    public function success(Request $request, $id){

        $transaction = Transaction::with(['details','travel_package.galleries','user'])
        ->findOrFail($id);
        $transaction->transaction_status = 'PENDING';

        $transaction->save();

        // Set Configurasi Midtrans
        // Set your Merchant Server Key
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');

        // Buat array untuk dikirim kemidtrans
        $midtrans_param = [
            'transaction_details' => [
                'order_id'  => 'MIDTRANS-' . $transaction->id,
                'gross_amount'  => (int) $transaction->transaction_total
            ],
            'customer_details'  => [
                'first_name'    => $transaction->user->name,
                'email'    => $transaction->user->email,
            ],
            'enabled_payments'  => ['gopay'],
            'vtweb' => []
        ];


        try {
            // Ambil halaman payment midrtans
            $paymentUrl = Snap::createTransaction($midtrans_param)->redirect_url;

            // Redirect
            header("Location: " . $paymentUrl);
            dd($paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        /* SEBELUM PAKE MIDTRANS !
            // // Kirim email ke user e-tiket nya
            // Mail::to($transaction->user->email)->send(
            //     new TransactionSuccess($transaction) // maksud new TransactionSuccess($transaction) jadi data diparameter akan di passing ke construct di class TransactionSuccess
            // );

            // return view('pages.success');
        */
    }   
}
