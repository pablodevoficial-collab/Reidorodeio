<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

$notConfigured = function () {
	return response('IPN endpoint not configured', Response::HTTP_NOT_IMPLEMENTED);
};

Route::post('paypal', $notConfigured)->name('Paypal');
Route::get('paypal-sdk', $notConfigured)->name('PaypalSdk');
Route::post('perfect-money', $notConfigured)->name('PerfectMoney');
Route::post('stripe', $notConfigured)->name('Stripe');
Route::post('stripe-js', $notConfigured)->name('StripeJs');
Route::post('stripe-v3', $notConfigured)->name('StripeV3');
Route::post('skrill', $notConfigured)->name('Skrill');
Route::post('paytm', $notConfigured)->name('Paytm');
Route::post('payeer', $notConfigured)->name('Payeer');
Route::post('paystack', $notConfigured)->name('Paystack');
Route::get('flutterwave/{trx}/{type}', $notConfigured)->name('Flutterwave');
Route::post('razorpay', $notConfigured)->name('Razorpay');
Route::post('instamojo', $notConfigured)->name('Instamojo');
Route::get('blockchain', $notConfigured)->name('Blockchain');
Route::post('coinpayments', $notConfigured)->name('Coinpayments');
Route::post('coinpayments-fiat', $notConfigured)->name('CoinpaymentsFiat');
Route::post('coingate', $notConfigured)->name('Coingate');
Route::post('coinbase-commerce', $notConfigured)->name('CoinbaseCommerce');
Route::get('mollie', $notConfigured)->name('Mollie');
Route::post('cashmaal', $notConfigured)->name('Cashmaal');
Route::post('mercado-pago', '\\App\\Http\\Controllers\\Gateway\\MercadoPagoWebhookController')->name('MercadoPago');
Route::post('authorize', $notConfigured)->name('Authorize');
Route::get('nmi', $notConfigured)->name('NMI');
Route::any('btc-pay', $notConfigured)->name('BTCPay');
Route::post('now-payments-hosted', $notConfigured)->name('NowPaymentsHosted');
Route::post('now-payments-checkout', $notConfigured)->name('NowPaymentsCheckout');
Route::post('2checkout', $notConfigured)->name('TwoCheckout');
Route::any('checkout', $notConfigured)->name('Checkout');
Route::post('sslcommerz', $notConfigured)->name('SslCommerz');
Route::post('aamarpay', $notConfigured)->name('Aamarpay');
Route::get('binance', $notConfigured)->name('Binance');
