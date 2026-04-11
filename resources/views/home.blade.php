@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
<h3>Dashboard</h3>
<p>Selamat datang di Sistem MRP PT Asalta Mandiri Agung</p>

<div class="row mt-4">

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6>Bahan Baku</h6>
                <h2>12</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6>Produk</h6>
                <h2>5</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6>Order Produksi</h6>
                <h2>8</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6>Material Kurang</h6>
                <h2>3</h2>
            </div>
        </div>
    </div>

</div>
@endsection
