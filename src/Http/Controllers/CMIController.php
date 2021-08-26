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
use Illuminate\Support\Facades\DB;
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

    protected function processOrder(array $data, bool $validation = null)
    {
        $transactionId = $data['tran_id'];
        $amount = $data['amount'];
        $currency = $data['currency'];
        $orderId = $data['value_a'];

        $status = PaymentStatusEnum::PENDING;
        if ($validation)
            $status = PaymentStatusEnum::COMPLETED;
        elseif ($validation === false)
            $status = PaymentStatusEnum::FAILED;

        return $this->storeLocalPayment([
            'amount' => $amount,
            'account_id' => 4,
            'currency' => 'MAD',
            'charge_id' => $transactionId,
            'payment_channel' => CMI_PAYMENT_METHOD_NAME,
            'status' => $status, // $validation ? PaymentStatusEnum::COMPLETED : PaymentStatusEnum::FAILED,
            'customer_id' => $data['value_c'],
            'customer_type' => urldecode($data['value_d']),
            'payment_type' => 'direct',
            'order_id' => $orderId,
        ]);
    }

    public function callback(Request $request, BaseHttpResponse $response)
    {
        $result = ($request->input('Response') == 'Error') ? false : true;
        $n_order = $request->input('oid');
        $order = $this->getOrderByToken($n_order);
        $checkoutToken = $request->input('TransId');


        // CMI response
        DB::table('cmi_response')->insert(
            [
                'order_id' => $order->id,
                'response' => '',
                'transaction' => $checkoutToken,
                'data' => serialize($request->all()),
                'create_at' => date('Y-m-d H:i:s'),
                'updated_at' =>date('Y-m-d H:i:s')
            ]
        );


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
                'action' => 'payment_failed',
                'description' => __("Payement fail via :via", ['via' => 'CMI']),
                'order_id' => $order->id,
                'user_id' => 4,
            ]);

            $this->orderHistoryRepository->createOrUpdate([
                'action' => 'cancel_order',
                'description' => trans('plugins/ecommerce::order.order_was_canceled_by', [
                    'money' => format_price($order->amount, $order->currency_id),
                ]),
                'order_id' => $order->id,
                'user_id' => 4,
            ]);
            die('FAILURE');
        } else {

            $proc_return_code = $request->input('ProcReturnCode');

            if ($proc_return_code == "00")   {

                // update order
                $this->orderRepository->createOrUpdate(['status' => OrderStatusEnum::PROCESSING,'is_confirmed' => 1], ['id' => $order->id]);

                // create payment
                $payment = $this->processOrder($data, true);

                // update payment into order
                $order->payment_id = $payment->id;
                $order->save();

                // create History [update order]
                $this->orderHistoryRepository->createOrUpdate([
                    'action' => 'payment_success',
                    'description' => __("Payement sucess via CMI - transaction: :checkoutToken", [
                        'checkoutToken' => $checkoutToken,
                    ]),
                    'order_id' => $order->id,
                    'user_id' => 4,
                ]);

                $this->orderHistoryRepository->createOrUpdate([
                    'action' => 'confirm_order',
                    'description' => trans('plugins/ecommerce::order.order_was_verified_by'),
                    'order_id' => $order->id,
                    'user_id' => 4,
                ]);

                die('ACTION=POSTAUTH');
            } else {

                // update order
                // $this->orderRepository->createOrUpdate(['status' => OrderStatusEnum::PENDING], ['id' => $order->id]);

                // create payment
                $payment = $this->processOrder($data, null);

                // update payment into order
                $order->payment_id = $payment->id;
                $order->save();


                // create History [update order]
                $this->orderHistoryRepository->createOrUpdate([
                    'action' => 'payment_success',
                    'description' => __("Payement sucess via CMI - transaction: :checkoutToken", [
                        'checkoutToken' => $checkoutToken,
                    ]),
                    'order_id' => $order->id,
                    'user_id' => 4,
                ]);

                $this->orderHistoryRepository->createOrUpdate([
                    'action' => 'payment_verification',
                    'description' => __("Payment est en cours de verification (4-5 jours)"),
                    'order_id' => $order->id,
                    'user_id' => 4,
                ]);

                die('APPROVED');
            }
        }
    }

    public function testes(){
        // return view('cmi.index');
        /*
         * "_token" => "e8YslN0nRkFLpxHiqRCmwlWyfB5HblkN6euxanaP"
  "checkout-token" => "e91e80e1dfbc3e2d13ce17616409d398"
  "coupon_code" => null
  "address" => array:6 [▼
    "name" => "insan hayawan"
    "email" => "hayawan@gmail.com"
    "phone" => "0000000000"
    "city" => "Casablanca"
    "state" => "Anfa"
    "address" => "78, rue hadika"
  ]
  "country" => "MA"
  "password" => null
  "password_confirmation" => null
  "shipping_option" => null
  "shipping_method" => "default"
  "amount" => 6999.0
  "currency" => "MAD"
  "currency_id" => "4"
  "callback_url" => "https://cosmos.isla-dev.com/payment/status"
  "return_url" => "https://cosmos.isla-dev.com/checkout/e91e80e1dfbc3e2d13ce17616409d398/success"
  "payment_method" => "cmi"
  "description" => "commande de teste"
  "user_id" => 0
  "shipping_amount" => "0.00"
  "tax_amount" => 0
  "sub_total" => 6999.0
  "discount_amount" => 0
  "status" => "pending"
  "is_finished" => true
  "token" => "e91e80e1dfbc3e2d13ce17616409d398"
  "order_id" => 302
         */

        // intval($request->input('order_id'))
        $result = DB::table('ec_order_addresses')->where('order_id', '=', 303)->first();
        dd($result);
    }

    private function getOrderByToken($n_order)
    {
        $id_order = intval($n_order) - 10000000;
        return Order::where('id', $id_order)->first();
    }
}
