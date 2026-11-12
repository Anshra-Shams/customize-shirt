@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-2 px-2">
    <div class="co-main-container bg-white mx-auto">
        <form action="{{ route('customer_orders.store') }}" method="POST" autocomplete="off">
            @csrf
            @include('admin_panel.customer_orders._form')
        </form>
    </div>
</div>
@endsection
