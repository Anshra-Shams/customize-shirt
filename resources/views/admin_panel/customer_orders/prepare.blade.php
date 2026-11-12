@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-2 px-2">
    <div class="co-main-container bg-white mx-auto">
        <form action="{{ route('customer_orders.update', $order->id) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')
            <input type="hidden" name="confirm_preparing" value="1">
            @include('admin_panel.customer_orders._form', ['isPrepareConfirmation' => true])
        </form>
    </div>
</div>
@endsection
