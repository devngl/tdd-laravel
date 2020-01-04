<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;
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
            'ticket_quantity' => ['required','numeric', 'min:1'],
        ]);

        $ticketQuantity = $request->get('ticket_quantity');
        $amount         = $concert->ticket_price * $ticketQuantity;
        $this->paymentGateway->charge($amount, $request->get('payment_token'));

        $order = $concert->orderTickets($request->get('email'), $ticketQuantity);

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }
}
