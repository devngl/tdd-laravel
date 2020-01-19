<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\CannotPurchaseUnpublishedConcerts;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            $order = $reservation->complete($this->paymentGateway, $request->get('payment_token'),
                $concert->user->stripe_account_id);
        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            throw $e;
        }

        Mail::to($order->email)
            ->send(new OrderConfirmationEmail($order));

        return new JsonResponse($order, JsonResponse::HTTP_CREATED);
    }
}
