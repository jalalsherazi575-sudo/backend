@extends('front.layouts.cms')

@section('content')
    <section class="section section-hero-area">
        <div class="container text-sm-center">
            @if ($cmsData)
            {!! $cmsData->description !!}
            @endif
        </div>
    </section>
@endsection