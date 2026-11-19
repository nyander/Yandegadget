<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments): Response
    {
        $valid = $payments->handleWebhook($request->getContent(), $request->header('x-paystack-signature'));

        return response('', $valid ? 200 : 401);
    }
}
