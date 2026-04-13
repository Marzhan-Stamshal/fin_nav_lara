<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->with('loan')
            ->latest('payment_date')
            ->limit(200)
            ->get();

        return response()->json($payments);
    }
}
