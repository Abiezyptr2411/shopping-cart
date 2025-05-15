<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use App\Models\Product;
use Carbon\Carbon;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        $subtotal = $cartItems->reduce(function ($carry, $item) {
            return $carry + ($item->product->price * $item->quantity);
        }, 0);

        $discount = session('discount', 0);
        $discountDescription = session('discount_description', '-');
        $total = max(0, $subtotal - $discount);

        return view('cart.index', compact('cartItems', 'subtotal', 'discount', 'total', 'discountDescription'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        foreach ($request->quantities as $cartItemId => $qty) {
            $cartItem = CartItem::where('id', $cartItemId)->where('user_id', $user->id)->first();
            if ($cartItem) {
                $cartItem->quantity = $qty;
                $cartItem->save();
            }
        }

        session()->forget('discount');
        session()->forget('discount_description');

        return redirect()->route('cart.index')->with('success', 'Quantity berhasil diupdate.');
    }

    public function remove(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'cart_item_id' => 'required|integer',
        ]);

        $cartItem = CartItem::where('id', $request->cart_item_id)->where('user_id', $user->id)->first();
        if ($cartItem) {
            $cartItem->delete();
            session()->forget('discount');
            session()->forget('discount_description');

            return redirect()->route('cart.index')->with('success', 'Barang berhasil dihapus.');
        }

        return redirect()->route('cart.index')->with('error', 'Barang tidak ditemukan.');
    }

   public function applyDiscount(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'discount_code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->discount_code));

        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $subtotal = $cartItems->reduce(function ($carry, $item) {
            return $carry + ($item->product->price * $item->quantity);
        }, 0);

        $discount = 0;
        $discountDescription = '-';

        switch ($code) {
            case 'FA111':
                $discount = floor($subtotal * 0.10);
                $discountDescription = "Diskon 10% dari subtotal";
                break;

            case 'FA222':
                $itemFA4532 = $cartItems->first(function ($item) {
                    return $item->product->code === 'FA4532';
                });

                if ($itemFA4532) {
                    $discount = 50000;
                    $discountDescription = "Diskon Rp 50.000 untuk barang FA4532";
                } else {
                    return redirect()->route('cart.index')->with('error', 'Diskon FA222 hanya berlaku jika ada barang FA4532 di keranjang.');
                }
                break;

            case 'FA333':
                $totalEligible = 0;
                foreach ($cartItems as $item) {
                    if ($item->product->price > 400000) {
                        $totalEligible += $item->product->price * $item->quantity;
                    }
                }
                if ($totalEligible > 0) {
                    $discount = floor($totalEligible * 0.06);
                    $discountDescription = "Diskon 6% untuk barang di atas Rp 400.000";
                } else {
                    return redirect()->route('cart.index')->with('error', 'Tidak ada barang yang memenuhi syarat diskon FA333.');
                }
                break;

            case 'FA444':
                $now = Carbon::now();

                if (
                    $now->isTuesday() &&
                    $now->hour >= 13 &&
                    $now->hour < 15
                ) {
                    $discount = floor($subtotal * 0.05);
                    $discountDescription = "Diskon 5% khusus Selasa jam 13:00-15:00";
                } else {
                    return redirect()->route('cart.index')->with('error', 'Diskon FA444 hanya berlaku hari Selasa jam 13:00 - 15:00.');
                }
                break;

            default:
                return redirect()->route('cart.index')->with('error', 'Kode diskon tidak valid.');
        }

        session(['discount' => $discount, 'discount_description' => $discountDescription]);

        return redirect()->route('cart.index')->with('success', "Diskon berhasil diterapkan: $discountDescription");
    }

    public function removeDiscount(Request $request)
    {
        $request->session()->forget('discount_code');
        $request->session()->forget('discount_amount');

        return redirect()->back()->with('success', 'Diskon berhasil dihapus.');
    }
}
