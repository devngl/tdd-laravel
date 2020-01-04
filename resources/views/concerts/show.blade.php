<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
{{ $concert->formatted_date }}
{{ $concert->formatted_start_time }}
{{ $concert->ticket_price_in_dollars }}
{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}

{{ $concert->venue }}
{{ $concert->venue_address }}
{{ $concert->additional_information }}
