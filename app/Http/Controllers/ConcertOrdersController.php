<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\CannotPurchaseUnpublishedConcerts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
    private PaymentGateway $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store(Request $request, Concert $concert): JsonResponse
    {
        $this->validate($request, [
            'email'           => 'required',
            'payment_token'   => 'required',
            'ticket_quantity' => ['required', 'numeric', 'min:1'],
        ]);

        if (!$concert->published_at) {
            throw new CannotPurchaseUnpublishedConcerts(JsonResponse::HTTP_PRECONDITION_FAILED,
                'Tried to purchase a ticket on an unpublished concert.');
        }

        $ticketQuantity = $request->get('ticket_quantity');
        $amount         = $concert->ticket_price * $ticketQuantity;

        $order = null;
        try {
            $order = $concert->orderTickets($request->get('email'), $ticketQuantity);
            $this->paymentGateway->charge($amount, $request->get('payment_token'));
        } catch (PaymentFailedException $paymentFailedException) {
            $order->cancel();
            throw $paymentFailedException;
        }

        return new JsonResponse($order, JsonResponse::HTTP_CREATED);
    }
}
