<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Contracts\View\View;
use Illuminate\View\Factory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConcertsController extends Controller
{
    /**
     * @var Factory
     */
    private $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function show(Concert $concert): View
    {
        if (!$concert->published_at) {
            throw new NotFoundHttpException('El concierto no está publicado');
        }

        return $this->viewFactory->make('concerts.show', ['concert' => $concert]);
    }
}
