<?php

namespace App\Http\Controllers;

use App\Order;
use App\PayPal;
use App\Service;
use App\User;
use Illuminate\Http\Request;

/**
 * Class PayPalController
 * @package App\Http\Controllers
 */
class PayPalController extends Controller
{


    public function form(Request $request, $service_id)
    {

        $service = Service::findOrFail($service_id);



        $order = new Order;
        $transaction_id = rand(10000000,99999999);
        $order->user_id = 1 ;  //user id
        $order->transaction_id = $transaction_id;
        $order->service_id    = $service->id;
        $order->amount    = $service->amount;
        $order->save();

        // the above order is just for example.
        return view('form', compact('service','transaction_id'));
    }

    /**
     * @param $order_id
     * @param Request $request
     */
    public function checkout($transaction_id, Request $request)
    {
        $order = Order::where('transaction_id', decrypt($transaction_id))->first();

        $paypal = new PayPal;

        $response = $paypal->purchase([
            'amount' => $paypal->formatAmount($order->amount),
            'transactionId' => $order->transaction_id,
            'currency' => 'USD',
            'cancelUrl' => $paypal->getCancelUrl($order),
            'returnUrl' => $paypal->getReturnUrl($order),
        ]);

        if ($response->isRedirect()) {
            $response->redirect();
        }

        return redirect()->back()->with([
            'message' => $response->getMessage(),

        ]);
    }

    /**
     * @param $order_id
     * @param Request $request
     * @return mixed
     */
    public function completed($order_id, Request $request)
    {

        $order = Order::findOrFail($order_id);

        $paypal = new PayPal;

        $response = $paypal->complete([
            'amount' => $paypal->formatAmount($order->amount),
            'transactionId' => $order->transaction_id,
            'currency' => 'USD',
            'cancelUrl' => $paypal->getCancelUrl($order),
            'returnUrl' => $paypal->getReturnUrl($order),
            'notifyUrl' => $paypal->getNotifyUrl($order),
        ]);

        if ($response->isSuccessful()) {
            $order->update([
                'transaction_id' => $response->getTransactionReference(),
                'payment_status' => Order::PAYMENT_COMPLETED,
            ]);

            return redirect()->route('paymentCompleted', encrypt($order_id))->with([
                'message' => 'You recent payment is sucessful with reference code ' . $response->getTransactionReference(),
            ]);
        }

        return redirect()->back()->with([
            'message' => $response->getMessage(),
        ]);

    }
    public function paymentCompleted($order)
    {
        # code...
        return "Thanks! payment completed";
    }

    /**
     * @param $order_id
     */
    public function cancelled($order_id)
    {
        $order = Order::findOrFail($order_id);

        return redirect()->route('order.paypal', encrypt($order_id))->with([
            'message' => 'You have cancelled your recent PayPal payment !',
        ]);
    }

    /**
     * @param $order_id
     * @param $env
     * @param Request $request
     */
    public function webhook($order_id, $env, Request $request)
    {
        // to do with new release of sudiptpa/paypal-ipn v3.0 (under development)
    }

}
