<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ProductRequest;
use App\Models\Reservation;
use App\Services\Payments\FakeGateway;
use App\Services\Payments\GatewayException;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentService;
use App\Services\Pricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Where customers come back to after paying (Paystack, or the test checkout),
 * and the test mobile money checkout used locally instead of Paystack.
 */
class PaymentController extends Controller
{
    public function callback(Request $request, PaymentService $payments): RedirectResponse
    {
        $reference = $request->string('reference')->toString() ?: $request->string('trxref')->toString();
        $payment = Payment::where('reference', $reference)->first();

        if ($payment === null) {
            return to_route('dashboard');
        }

        try {
            $payments->confirm($reference);
        } catch (GatewayException) {
            // The webhook will confirm it if the provider was briefly unreachable.
        }

        return match (true) {
            $payment->payable instanceof Reservation => to_route('reservations.show', $payment->payable),
            $payment->payable instanceof ProductRequest => to_route('requests.show', $payment->payable),
            default => to_route('dashboard'),
        };
    }

    /**
     * Local only: a pretend mobile money checkout, so the whole journey can be shown without real money.
     */
    public function testCheckout(Request $request, Payment $payment, PaymentGateway $gateway): Response
    {
        abort_unless($gateway instanceof FakeGateway, 404);
        abort_unless($payment->customer_id === $request->user()->id, 403);

        return Inertia::render('payments/test-checkout', [
            'payment' => [
                'reference' => $payment->reference,
                'amount' => app(Pricing::class)->present($payment->money),
                'status' => $payment->status->value,
                'what' => $payment->payable instanceof Reservation ? 'Reservation '.$payment->payable->reference : 'Request deposit',
                'email' => $request->user()->email,
            ],
        ]);
    }

    public function testDecide(Request $request, Payment $payment, PaymentGateway $gateway): RedirectResponse
    {
        abort_unless($gateway instanceof FakeGateway, 404);
        abort_unless($payment->customer_id === $request->user()->id, 403);

        $data = $request->validate([
            'outcome' => ['required', 'in:approve,decline'],
            'channel' => ['required', 'in:mobile_money,card'],
            'network' => ['nullable', 'in:mtn,telecel,airteltigo'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $gateway->decide($payment, $data['outcome'] === 'approve', $data['channel']);

        return to_route('payments.callback', ['reference' => $payment->reference]);
    }
}
