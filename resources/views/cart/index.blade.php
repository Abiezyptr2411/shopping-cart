@extends('layouts.app')

@section('content')
<h2 class="text-center mb-4" style="font-weight: 700; color: #343a40;">Troli Anda</h2>

@if($cartItems->isEmpty())
    <p class="text-center fs-5 text-muted">Keranjang Anda kosong.</p>
@else
<form method="POST" action="{{ route('cart.update') }}">
    @csrf

    <div class="table-responsive">
    <table class="table align-middle" style="background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);">
        <thead class="table-light">
            <tr class="text-center text-secondary" style="font-weight: 600;">
                <th scope="col">Produk</th>
                <th scope="col">Pilihan Harga</th>
                <th scope="col">Kuantitas</th>
                <th scope="col">Subtotal</th>
                <th scope="col">Hapus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cartItems as $item)
            <tr class="align-middle text-center" style="border-top: 1px solid #eee;">
                <td class="text-start" style="width: 30%;">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $item->product->image_url ?? 'https://via.placeholder.com/60' }}" alt="product image" class="rounded-3 shadow-sm" width="60" height="60" style="object-fit: cover;">
                        <div>
                            <div style="color: #0d6efd; font-weight: 600; font-size: 1rem;">{{ $item->product->name }}</div>
                            <small class="text-muted" style="font-size: 0.85rem;">{{ $item->product->sku ?? 'SKU-' . $item->product->id }}</small>
                        </div>
                    </div>
                </td>
                <td>Rp {{ number_format($item->product->price, 0, ',', '.') }}</td>
                <td>
                    <input 
                      type="number" 
                      min="1" 
                      name="quantities[{{ $item->id }}]" 
                      value="{{ $item->quantity }}" 
                      class="form-control text-center mx-auto" 
                      style="max-width: 70px; border-radius: 6px; border: 1px solid #ced4da;"
                      aria-label="Jumlah produk"
                    >
                </td>
                <td class="fw-semibold" style="color: #198754;">
                    Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                </td>
                <td>
                    <form method="POST" action="{{ route('cart.remove') }}" onsubmit="return confirm('Yakin hapus item ini?');">
                        @csrf
                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" title="Hapus Item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach

            <tr>
                <td colspan="5" class="text-end">
                    <a href="#" class="text-success fw-semibold" style="font-size: 14px;" data-bs-toggle="modal" data-bs-target="#discountModal">
                        <i class="bi bi-percent"></i> Gunakan Kode Diskon / Reward
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button class="btn btn-primary px-4 py-2" type="submit" style="font-weight: 600;">Update Kuantitas</button>
    </div>
</form>

<!-- Ringkasan -->
<div class="d-flex justify-content-end mt-4">
    <table class="table table-borderless text-end" style="max-width: 320px; background: #f8f9fa; border-radius: 12px; padding: 15px;">
        <tr>
            <th class="text-secondary" style="font-weight: 500;">Subtotal</th>
            <td class="fw-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th class="text-secondary" style="font-weight: 500;">Diskon</th>
            <td class="text-success fw-semibold">{{ $discountDescription }} - Rp {{ number_format($discount, 0, ',', '.') }}</td>
        </tr>
        <tr style="border-top: 2px solid #0d6efd;">
            <th class="fs-5" style="font-weight: 700;">Total</th>
            <td class="fs-5 fw-bold" style="color: #0d6efd;">Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

<!-- Modal Kode Diskon -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4 rounded-4 shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="modal-title fw-bold" id="discountModalLabel">Masukkan Kode Diskon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <form method="POST" action="{{ route('cart.applyDiscount') }}">
        @csrf
        <div class="input-group">
          <input 
            type="text" 
            name="discount_code" 
            class="form-control border border-success" 
            placeholder="Masukkan kode diskon Anda" 
            required 
            autofocus
            style="height: 42px; border-radius: 8px 0 0 8px;"
          >
          <button class="btn btn-warning text-white fw-bold" type="submit" style="height: 42px; border-radius: 0 8px 8px 0;">
            Terapkan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endif
@endsection
