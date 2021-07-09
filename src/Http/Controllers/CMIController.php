<?php

namespace Botble\Cmi\Http\Controllers;

use Botble\Cmi\Library\CMI;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Facades\CartFacade as Cart;
use Botble\Ecommerce\Facades\OrderHelperFacade;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Repositories\Interfaces\OrderHistoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\OrderInterface;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Botble\Payment\Services\Traits\PaymentTrait;
use Illuminate\Support\Facades\Auth;
use OrderHelper;
use Illuminate\Http\Request;
use Session;



// use Paystack;
use Throwable;

class CMIController extends BaseController
{
    use PaymentTrait;

    protected $paymentRepository;
    protected $orderRepository;
    protected $orderHistoryRepository;

    public function __construct(
        OrderInterface $orderRepository,
        OrderHistoryInterface $orderHistoryRepository,
        PaymentInterface $paymentRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderHistoryRepository = $orderHistoryRepository;
        $this->paymentRepository = $paymentRepository;
    }


    public function success(Request $request, BaseHttpResponse $response)
    {

        // get order id
        $n_order = $request->input('oid');
        $order = $this->getOrderByToken($n_order);

        if ($request->input('Response') == 'Error') {

            return $response
                ->setError()
                ->setNextUrl(route('public.checkout.success', $order->token))
                ->setMessage(__('Error when processing payment via CMI'));

        } else {
            return $response
                ->setNextUrl(route('public.checkout.success', $order->token))
                ->setMessage(__('Checkout successfully!'));

        }
    }

    public function fail(Request $request, BaseHttpResponse $response)
    {

        /*
         id, order_id, response, transaction, data
         */

        // get order id
        $n_order = $request->input('oid');
        $order = $this->getOrderByToken($n_order);

        if ($request->input('Response') == 'Error') {

            return $response
                ->setError()
                ->setNextUrl(route('public.checkout.success', $order->token))
                ->setMessage(__('Error when processing payment via CMI'));

        } else {
            return $response
                ->setNextUrl(route('public.checkout.success', $order->token))
                ->setMessage(__('Checkout successfully!'));
        }
    }

    protected function processOrder(array $data, bool $validation = false)
    {
        $transactionId = $data['tran_id'];
        $amount = $data['amount'];
        $currency = $data['currency'];
        $orderId = $data['value_a'];


        // return OrderHelper::processOrder($orderId, $validation);


        return $this->storeLocalPayment([
            'amount' => $amount,
            'account_id' => 4,
            'currency' => 'MAD',
            'charge_id' => $transactionId,
            'payment_channel' => CMI_PAYMENT_METHOD_NAME,
            'status' => $validation ? PaymentStatusEnum::COMPLETED : PaymentStatusEnum::FAILED,
            'customer_id' => $data['value_c'],
            'customer_type' => urldecode($data['value_d']),
            'payment_type' => 'direct',
            'order_id' => $orderId,
        ]);
        // dd($result);
    }

    public function callback(Request $request, BaseHttpResponse $response)
    {
        $result = ($request->input('Response') == 'Error') ? false : true;
        $n_order = $request->input('oid');
        $order = $this->getOrderByToken($n_order);
        $checkoutToken = $request->input('TransId');

        $data = [
            "order_id" => $order->id,
            "response" => $request->input('Response'), // "Response":"Error",
            "transaction" => null,
            "data" => $request->all(),
            'tran_id' => $checkoutToken,
            'amount' => $order->amount,
            'currency' => 'MAD',
            'value_a' => $order->id,
            'value_c' => null,
            'value_d' => null
        ];

        if ( !$result ) {
            $this->orderRepository->createOrUpdate(['status' => OrderStatusEnum::CANCELED], ['id' => $order->id]);

            // create payment
            $payment = $this->processOrder($data, false);

            // update payment into order
            $order->payment_id = $payment->id;
            $order->save();

            // create History [update order]
            $this->orderHistoryRepository->createOrUpdate([
                'action' => 'cancel_order',
                'description' => trans('plugins/ecommerce::order.order_was_canceled_by', [
                    'money' => format_price($order->amount, $order->currency_id),
                ]),
                'order_id' => $order->id,
                'user_id' => 4,
            ]);
        } else {
            // update order
            $this->orderRepository->createOrUpdate(['status' => OrderStatusEnum::PROCESSING,'is_confirmed' => 1], ['id' => $order->id]);

            // create payment
            $payment = $this->processOrder($data, true);

            // update payment into order
            $order->payment_id = $payment->id;
            $order->save();

            // create History [update order]
            $this->orderHistoryRepository->createOrUpdate([
                'action' => 'confirm_order',
                'description' => trans('plugins/ecommerce::order.order_was_verified_by', [
                    'money' => format_price($order->amount, $order->currency_id),
                ]),
                'order_id' => $order->id,
                'user_id' => 4,
            ]);
        }
    }

    public function testes(){
        return view('cmi.index');
    }

    private function getOrderByToken($n_order)
    {
        $id_order = intval($n_order) - 10000000;
        return Order::where('id', $id_order)->first();
    }
}
