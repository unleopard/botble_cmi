<?php

namespace Botble\Cmi\Providers;

use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider;

class CMIServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        Helper::autoload(__DIR__ . '/../../helpers');
    }

    /**
     * @throws FileNotFoundException
     */
    public function boot()
    {
        if (is_plugin_active('payment')) {
            $this->setNamespace('plugins/cmi')
                ->loadRoutes(['web'])
                ->loadAndPublishViews()
                ->publishAssets();

            $this->app->register(HookServiceProvider::class);

            $config = $this->app->make('config');

            $config->set([
                'cmi.publicKey'     => get_payment_setting('public', CMI_PAYMENT_METHOD_NAME),
                'cmi.secretKey'     => get_payment_setting('secret', CMI_PAYMENT_METHOD_NAME),
                'cmi.merchantEmail' => get_payment_setting('merchant_email', CMI_PAYMENT_METHOD_NAME),
                'cmi.paymentUrl'    => 'https://testpayment.cmi.co.ma/fim/est3Dgate'
            ]);
        }
    }
}
