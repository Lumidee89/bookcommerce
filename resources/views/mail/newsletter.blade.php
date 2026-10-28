<x-mail::message>
@if ($preheader)
<span style="display:none;max-height:0;overflow:hidden;">{{ $preheader }}</span>
@endif
@if ($name)
Dear {{ $name }},

@endif
{!! $bodyHtml !!}

<x-slot:subcopy>
You are receiving this because you subscribed to updates from {{ config('app.name') }}.
[Unsubscribe]({{ $unsubscribeUrl }}) at any time.
</x-slot:subcopy>
</x-mail::message>
