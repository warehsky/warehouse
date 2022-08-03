@extends('layouts.app')
<link href="{{ asset('css/main-page.css') }}" rel="stylesheet">
<link href="{{ asset('css/article.css') }}" rel="stylesheet">
<link href="{{ asset('css/good-items.css') }}" rel="stylesheet">
@if($agent->isMobile())
<link href="{{ asset('css/media.css') }}" rel="stylesheet">
@endif
@section('title', ($group ? mb_strimwidth($group->title, 0, config('shop.meta_max_length', 40)) .' | ' : '') . 'МТ Доставка')
@section('metatags')
   <meta name="description" content="{{($group ? mb_strimwidth($group->title, 0, config('shop.meta_max_length', 40)) .' | ' : '')}} МТ Доставка | {{$meta}}">
   <meta name="keywords" content="МТ Доставка, MT Delivery, {{$meta}}">
@endsection
@section('content')
@if($groupId==0 && $text=='')
<div class="banner-img container">
    <a href="/groups"><img class="banner-img_img" src="{{$baner}}" alt="Баннер доставка"></a>
</div>
@endif
<mtheader :ismobile="{{(int)$agent->isMobile()}}"></mtheader>
@if($groupId==0 && $text=='' && $tags=='no')
@if(!$agent->isMobile())
<menu-items></menu-items>
@endif
<main class="container">
    <div id="main-content" class="main__content">
        <div class="{{!$agent->isMobile()?'chief':'nangrid-chief'}}">
            @if(!$agent->isMobile())
                <vertical-menu :items="{{$itemgroups}}"></vertical-menu>
            @endif
            <div class="{{!$agent->isMobile()?'chief-slider':'chief-slider-mobile'}}">
                <banners-slider
                    :ismobile="{{(int)$agent->isMobile()}}"
                    data-get-query="/DataFiles/{{(int)$agent->isMobile()==0?'banners.json':'banners_mobile.json'}}"
                    items-class="chief-slider_item"
                    items-images-class="chief-slider_img">
                </banners-slider>
            </div>
            <articles></articles>
        </div>
        <div class="share">
            <discount 
                :widthforone="{{$widthForOne}}"
                :ismobile='{{(int)$agent->isMobile()}}'
                datagroupid="0"
                data_tags="[330]">
                <template v-slot:before>
                    <a href="/?group=0&tags=[330]" class="share-title-btn" title="Страница с акционными товарами">акционные товары</a>
                </template>
            </discount>
        </div>
        <div class="narrow-banner">
            <banners-slider
                :ismobile="{{(int)$agent->isMobile()}}"
                data-get-query="/DataFiles/{{(int)$agent->isMobile()==0?'narrowBanners.json':'narrowBanners_mobile.json'}}"
                items-class="banner"
                items-images-class="banner-img">
            </banners-slider>
        </div>
        <div class="hit-goods">
            <div class="share-goods">
                <discount
                    :widthforone="{{$widthForOne}}"
                    :ismobile='{{(int)$agent->isMobile()}}'
                    datagroupid="0" data_tags="[331]">
                    <template v-slot:before>
                        <a href="/?group=0&tags=[331]" class="share-title-btn" title="Страница с хитами продаж">хиты продаж</a>
                    </template>
                </discount>
            </div>
        </div>
        <div class="about-mt">
            <a href="/delivery" class="about-mt_item">
                <img class="about-mt_img" src="/css/main/img/delivery.svg" alt="Доставка">
                <span class="about-mt_name">доставка</span>
                <p class="about-mt_desc"><span class="bold">«МТ Доставка»</span> – интернет-магазин
                    по продаже и доставке продуктов питания ...</p>
                <span class="about-mt_more">Узнать подробнее</span>
            </a>
            <a href="/contacts" class="about-mt_item">
                <img class="about-mt_img" src="/css/main/img/message.svg" alt="Контакты">
                <span class="about-mt_name">Контакты</span>
                <p class="about-mt_desc">Номер телефона горячей линии - 377
                    Режим работы интернет-магазина ...</p>
                <span class="about-mt_more">Узнать подробнее</span>
            </a>
            <a href="/about" class="about-mt_item">
                <img class="about-mt_img" src="/css/main/img/building.svg" alt="О компании">
                <span class="about-mt_name">о компании</span>
                <p class="about-mt_desc">Мы помогаем своим клиентам
                    не нагружать себя тяжёлыми покупками и не тратить время на очереди в магазинах ...</p>
                <span class="about-mt_more">Узнать подробнее</span>
            </a>
        </div>
    </div>

</main>
@else
<div id="goods" class="container">
    <good-items data-groupId="{{$_groupId}}" :ismobile="{{(int)$agent->isMobile()}}"></good-items>
</div>
@endif


@endsection
@section('scripts')

@parent


@endsection