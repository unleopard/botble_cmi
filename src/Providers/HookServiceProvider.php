<?php

namespace Botble\Cmi\Providers;

use Botble\Cmi\Library\CMI;
use Botble\Ecommerce\Repositories\Interfaces\OrderAddressInterface;
use Botble\Payment\Enums\PaymentMethodEnum;
use Html;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot()
    {

        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerCMIMethod'], 16, 2);
        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithCMI'], 16, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 97, 1);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['cmi'] = CMI_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 21, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == CMI_PAYMENT_METHOD_NAME) {
                $value = 'CMI';
            }

            return $value;
        }, 21, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == CMI_PAYMENT_METHOD_NAME) {
                $value = Html::tag('span', PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label'])
                    ->toHtml();
            }

            return $value;
        }, 21, 2);
    }

    /**
     * @param string $settings
     * @return string
     * @throws Throwable
     */
    public function addPaymentSettings($settings)
    {
        return $settings . view('plugins/cmi::settings')->render();
    }

    /**
     * @param string $html
     * @param array $data
     * @return string
     */
    public function registerCMIMethod($html, array $data)
    {
        return $html . view('plugins/cmi::methods', $data)->render();
    }

    /**
     * @param Request $request
     * @param array $data
     */
    public function checkoutWithCMI(array $data, Request $request)
    {
        /*
         // create payment
            $payment = $this->processOrder($data, false);

            // update payment into order
            $order->payment_id = $payment->id;
            $order->save();
         */
        // dd($request->all());


        if ($request->input('payment_method') == CMI_PAYMENT_METHOD_NAME) {

            // urls
            $orgOkUrl = CMI_URL_SUCCESS;
            $orgFailUrl = CMI_URL_FAIL;
            $shopurl = BASE_URL;
            $orgCallbackUrl = CMI_URL_CALLBACK;

            // correction de "order address"
            $_order_id = $request->input('order_id');

            $order_addresses = DB::table('ec_order_addresses')->where('order_id', '=', $_order_id)->first();
            if (is_null($order_addresses)) {

                DB::table('ec_order_addresses')->insert([
                    "name" => $request->input('address')['name'],
                    "phone" => $request->input('address')['phone'],
                    "email" => $request->input('address')['email'],
                    "country" => 'MA',
                    "state" => $request->input('address')['state'],
                    "city" => $request->input('address')['city'],
                    "address" => $request->input('address')['address'],
                    "order_id" => $_order_id,
                    "zip_code" => null
                ]);
            }

            // CMI settings
            $client_id = get_payment_setting('public', CMI_PAYMENT_METHOD_NAME);

            $data = [
                "clientid" => $client_id,
                "amount" => $request->input('amount'),
                "okUrl" => $orgOkUrl,
                "failUrl" => $orgFailUrl,
                "TranType" => "PreAuth",
                "callbackUrl" => $orgCallbackUrl,
                "shopurl" => $shopurl,
                "currency" => "504",
                "rnd" => microtime(),
                "storetype" => "3D_PAY_HOSTING",
                "hashAlgorithm" => "ver3",
                "lang" => "fr",
                "BillToName" => $request->input('address')['name'],
                "BillToCompany" => '',
                "BillToStreet1" => $request->input('address')['address'],
                "BillToCity" => $request->input('address')['city'],
                "BillToStateProv" => $request->input('address')['state'],
                "BillToCountry" => "MA",
                "email" => $request->input('address')['email'],
                "BillToTelVoice" => $request->input('address')['phone'],
                'AutoRedirect' => 'false',
                'CallbackResponse' => '1',
                "encoding" => "UTF-8",
                "oid" => intval($request->input('order_id')) + 10000000 // $request->input('token')
            ];

            $_cmi = new CMI();
            $_cmi->create_form($data);
        }

        return $data;
    }
}
