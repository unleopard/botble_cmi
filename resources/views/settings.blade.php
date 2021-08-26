@php $cmiStatus = get_payment_setting('status', CMI_PAYMENT_METHOD_NAME); @endphp

<style>
    .border-left {

    }
</style>

<table class="table payment-method-item">
    <tbody>
    <tr class="border-pay-row">
        <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
        <td style="width: 20%;">
            <img class="filter-black" src="{{ url('vendor/core/plugins/cmi/images/cmi.png') }}"
                 alt="cmi">
        </td>
        <td class="border-right">
            <ul>
                <li>
                    <a href="https://www.cmi.co.ma" target="_blank">{{ __('CMI') }}</a>
                    <p>{{ __('Customer can buy product and pay directly using Visa, Credit card via :name', ['name' => 'CMI']) }}</p>
                </li>
            </ul>
        </td>
    </tr>
    </tbody>
    <tbody class="border-none-t">
    <tr class="bg-white">
        <td colspan="3">
            <div class="float-left" style="margin-top: 5px;">
                <div
                    class="payment-name-label-group @if (get_payment_setting('status', CMI_PAYMENT_METHOD_NAME) == 0) hidden @endif">
                    <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span> <label
                        class="ws-nm inline-display method-name-label">{{ get_payment_setting('name', CMI_PAYMENT_METHOD_NAME) }}</label>
                </div>
            </div>
            <div class="float-right">
                <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($cmiStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($cmiStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
            </div>
        </td>
    </tr>
    <tr class="paypal-online-payment payment-content-item hidden">
        <td class="border-left" colspan="3">
            {!! Form::open() !!}
            {!! Form::hidden('type', CMI_PAYMENT_METHOD_NAME, ['class' => 'payment_type']) !!}
            <div class="row">
                <div class="col-sm-6">
                    <ul>
                        <li>
                            <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => 'CMI']) }}</label>
                        </li>
                        <li class="payment-note">
                            <p>{{ __('Configuration :name', ['name' => 'CMI']) }} :</p>
                            <ul class="m-md-l" style="list-style-type:decimal">
                                <li style="list-style-type:decimal">
                                    <p>{{ __("After login to your :name account", ['name' => 'CMI']) }}</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ __('Store Key') }}</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ __('Enter Public, Secret keys into the box in right hand') }}</p>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6 border-left">
                    <div class="well bg-white">
                        <div class="form-group">
                            <label class="text-title-field"
                                   for="cmi_name">{{ trans('plugins/payment::payment.method_name') }}</label>
                            <input type="text" class="next-input" name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_name"
                                   id="cmi_name" data-counter="400"
                                   value="{{ get_payment_setting('name', CMI_PAYMENT_METHOD_NAME, __('Online payment via :name', ['name' => 'CMI'])) }}">
                        </div>

                        <div class="form-group">
                            <label class="text-title-field" for="payment_{{ CMI_PAYMENT_METHOD_NAME }}_description">{{ __('Description') }}</label>
                            <textarea class="next-input" name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_description" id="payment_{{ CMI_PAYMENT_METHOD_NAME }}_description">{{ get_payment_setting('description', CMI_PAYMENT_METHOD_NAME, __('Payment with :name', ['name'=>'CMI'])) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="text-title-field" for="payment_{{ CMI_PAYMENT_METHOD_NAME }}_redirect_message">{{ __('Redirect message') }}</label>
                            <textarea class="next-input" name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_redirect_message" id="payment_{{ CMI_PAYMENT_METHOD_NAME }}_redirect_message">{{ get_payment_setting('_redirect_message', CMI_PAYMENT_METHOD_NAME, __('Vous allez être redirigé vers la plateforme CMI pour finaliser votre paiement')) }}</textarea>
                        </div>
<hr />



                        <p class="payment-note">
                            {{ trans('plugins/payment::payment.please_provide_information') }} <a target="_blank" href="http://www.maroctelecommerce.com/docs/IntegrationPaiementOnlineMTC_V3_3L.pdf">CMI</a>:
                        </p>
                        <div class="form-group">
                            <label class="text-title-field" for="{{ CMI_PAYMENT_METHOD_NAME }}_mode">{{ __('Mode') }}</label>


                            <label for="{{ CMI_PAYMENT_METHOD_NAME }}_mode_0" class="next-label">
                                <input type="radio" class="hrv-radio" name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_mode" id="{{ CMI_PAYMENT_METHOD_NAME }}_mode_0"
                                       value="0" {{ get_payment_setting('mode', CMI_PAYMENT_METHOD_NAME) == 0? 'checked':'' }} />
                                {{ __('Developpement') }}
                            </label>

                            <label class="text-title-field" for="{{ CMI_PAYMENT_METHOD_NAME }}_mode">
                            <input type="radio" class="hrv-radio" name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_mode" id="{{ CMI_PAYMENT_METHOD_NAME }}_mode_1"
                                   value="1" {{ get_payment_setting('mode', CMI_PAYMENT_METHOD_NAME) == 1? 'checked':'' }} />
                                {{ __('Production') }}
                            </label>

                            <!--<input type="text" class="next-input"
                                   name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_mode" id="{{ CMI_PAYMENT_METHOD_NAME }}_mode"
                                   value="{{ get_payment_setting('mode', CMI_PAYMENT_METHOD_NAME) }}">-->
                        </div>
                        <div class="form-group">
                            <label class="text-title-field" for="{{ CMI_PAYMENT_METHOD_NAME }}_public">{{ __('Client id') }}</label>
                            <input type="text" class="next-input"
                                   name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_public" id="{{ CMI_PAYMENT_METHOD_NAME }}_public"
                                   value="{{ get_payment_setting('public', CMI_PAYMENT_METHOD_NAME) }}">
                        </div>
                        <div class="form-group">
                            <label class="text-title-field" for="{{ CMI_PAYMENT_METHOD_NAME }}_secret">{{ __('Store Key') }}</label>
                            <input type="password" class="next-input" placeholder="••••••••" id="{{ CMI_PAYMENT_METHOD_NAME }}_secret"
                                   name="payment_{{ CMI_PAYMENT_METHOD_NAME }}_secret"
                                   value="{{ get_payment_setting('secret', CMI_PAYMENT_METHOD_NAME) }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 bg-white text-right">
                <button class="btn btn-warning disable-payment-item @if ($cmiStatus == 0) hidden @endif"
                        type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                <button
                    class="btn btn-info save-payment-item btn-text-trigger-save @if ($cmiStatus == 1) hidden @endif"
                    type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                <button
                    class="btn btn-info save-payment-item btn-text-trigger-update @if ($cmiStatus == 0) hidden @endif"
                    type="button">{{ trans('plugins/payment::payment.update') }}</button>
            </div>
            {!! Form::close() !!}
        </td>
    </tr>
    </tbody>
</table>
