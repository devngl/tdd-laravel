@extends('layouts.backstage')

@section('backstageContent')
    <div class="bg-light p-xs-y-4 border-b">
        <div class="container">
            <div class="flex-spaced flex-y-center">
                <h1 class="text-lg">Your concerts</h1>
                <a href="{{ route('backstage.concerts.new') }}" class="btn btn-primary">Add concert</a>
            </div>
        </div>
    </div>
    <div class="bg-soft p-xs-y-5">
        <div class="container m-xs-b-4">
            <div class="m-xs-b-6">
                <h2 class="m-xs-b-3 text-base wt-medium text-dark-soft">Published</h2>
                <div class="row">
                    @foreach ($publishedConcerts as $concert)
                        <div class="col-xs-12 col-lg-4">
                            <div class="card m-xs-b-4">
                                <div class="card-section">
                                    <div class="m-xs-b-4">
                                        <div class="m-xs-b-2">
                                            <h1 class="text-lg wt-bold">{{ $concert->title }}</h1>
                                            <p class="wt-medium text-dark-soft text-ellipsis">{{ $concert->subtitle }}</p>
                                        </div>
                                        <p class="text-sm m-xs-b-2">
                                            @icon('location', 'zondicon-sm text-dark-soft m-xs-r-1')
                                            {{ $concert->venue }} &ndash; {{ $concert->city }}, {{ $concert->state }}
                                        </p>
                                        <p class="text-sm">
                                            @icon('calendar', 'zondicon-sm text-dark-soft m-xs-r-1')
                                            {{ $concert->formatted_date }} @ {{ $concert->formatted_start_time }}
                                        </p>
                                    </div>
                                    <div>
                                        <a href="{{ route('backstage.published-concert-orders.index', $concert) }}"
                                           class="btn btn-sm btn-secondary m-xs-r-2">Manage</a>
                                        <a href="{{ route('concerts.show', $concert) }}"
                                           class="link-brand text-sm wt-medium">Public Link</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <h2 class="m-xs-b-3 text-base wt-medium text-dark-soft">Drafts</h2>
                <div class="row">
                    @foreach ($unpublishedConcerts as $concert)
                        <div class="col-xs-12 col-lg-4">
                            <div class="card m-xs-b-4">
                                <div class="card-section">
                                    <div class="m-xs-b-4">
                                        <div class="m-xs-b-2">
                                            <h1 class="text-lg wt-bold">{{ $concert->title }}</h1>
                                            <p class="wt-medium text-dark-soft text-ellipsis">{{ $concert->subtitle }}</p>
                                        </div>
                                        <p class="text-sm m-xs-b-2">
                                            @icon('location', 'zondicon-sm text-dark-soft m-xs-r-1')
                                            {{ $concert->venue }} &ndash; {{ $concert->city }}, {{ $concert->state }}
                                        </p>
                                        <p class="text-sm">
                                            @icon('calendar', 'zondicon-sm text-dark-soft m-xs-r-1')
                                            {{ $concert->formatted_date }} @ {{ $concert->formatted_start_time }}
                                        </p>
                                    </div>
                                    <div>
                                        <a href="{{ route('backstage.concerts.edit', $concert) }}"
                                           class="btn btn-sm btn-secondary m-xs-r-2">Edit</a>
                                        <form class="inline-block"
                                              action="{{ route('backstage.published-concerts.store') }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="concert_id" value="{{ $concert->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary">Publish</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
