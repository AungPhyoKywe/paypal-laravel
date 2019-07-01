@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="gateway--info">
            <div class="gateway--desc">
                @if(session()->has('message'))
                    <p class="message">
                        {{ session('message') }}
                    </p>
                @endif
                <div class="row">
                    <div class="col">
                        <img src="{{ asset('images/paypal.png') }}" class="img-responsive gateway__img">
                    </div>
                    <div class="col">
                        <img src="{{ asset('images/laravel.png') }}" class="img-responsive gateway__img">
                    </div>
                </div>
                <p><strong>Order Overview !</strong></p>
                <hr>
                <p>Item : Yearly Subscription cost !</p>
                <p>Amount : ${{ $service->amount }}</p>
                <hr>
            </div>
            <div class="gateway--paypal">
                <form method="POST" action="{{ route('checkout.payment.paypal', ['transaction_id' => encrypt($transaction_id)]) }}">
                    {{ csrf_field() }}
                    <button class="btn btn-pay">
                        <i class="fa fa-paypal" aria-hidden="true"></i> Pay with PayPal
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop