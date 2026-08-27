@extends('layouts.app')
@section('meta-title')
404 - Page not found
@endsection

@section('meta-description')
404 - Page not found
@endsection
@section('meta-image')
@endsection
 
@section('content')
<div class="blogs-main-section-inner">
    <div class="infinite-scroll-component__outerdiv" >
        <div class="infinite-scroll-component">
            <div class="row blog-content text-center error404" id="news-container">
            <img src="{{ config('app.url') }}/public/assets/images/error.gif"/>
            <p class="gujfont">આપ જે પેજ શોધી રહ્યા છો, તે ઉપલબ્ધ નથી . કૃપયા નીચે આપેલું બટન ક્લિક કરીને હોમ પેજ પાર જાઓ અથવા નીચે આપેલા ટોપ ન્યૂઝ વાંચો.</p>
                <a class="btnCls gotohome mb-5" href="{{ config('app.url') }}">Go to Homepage</a>
            </div>
        </div>
    </div>
</div> 
@endsection
