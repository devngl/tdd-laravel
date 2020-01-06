<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\CannotPurchaseUnpublishedConcerts;
use App\Order;
use App\Reservation;
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
        $tickets        = $concert->reserveTickets($ticketQuantity);
        $reservation    = new Reservation($tickets);

        try {
            $this->paymentGateway->charge($reservation->totalCost(), $request->get('payment_token'));
        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            throw $e;
        }
        $order = Order::forTickets($tickets, $request->get('email'), $reservation->totalCost());

        return new JsonResponse($order, JsonResponse::HTTP_CREATED);
    }
}
