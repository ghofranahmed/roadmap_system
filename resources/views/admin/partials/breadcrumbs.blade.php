@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <ol class="breadcrumb float-sm-right">
        @foreach($breadcrumbs as $breadcrumb)
            @if($loop->last)
                <li class="breadcrumb-item active">{{ $breadcrumb['text'] }}</li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['text'] }}</a>
                </li>
            @endif
        @endforeach
    </ol>
@endif

