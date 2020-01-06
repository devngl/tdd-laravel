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
        $reservation    = $concert->reserveTickets($ticketQuantity, $request->get('email'));

        try {
            $this->paymentGateway->charge($reservation->totalCost(), $request->get('payment_token'));
        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            throw $e;
        }

        $order = $reservation->complete();

        return new JsonResponse($order, JsonResponse::HTTP_CREATED);
    }
}
