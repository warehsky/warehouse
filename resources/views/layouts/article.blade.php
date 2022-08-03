<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	@yield('metatags')
	<title>@yield('title')</title>

	<!-- Scripts -->
	<script src="{{ mix('js/app.js') }}" defer></script>

	<!-- Fonts -->
	<!--<link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
-->
	<!-- Styles -->
	<link href="{{ asset('css/css/cash.css') }}" rel="stylesheet">
	<link rel="icon" type="image/x-icon" href="/favicon.png" />
</head>

<body>
	<div id="goods">
		<div class="container">
			<mtheader :ismobile="{{(int)$agent->isMobile()}}"></mtheader>
			@if(!$agent->isMobile())
			<div class="good-path">
				<a class="crumbs" href="/">Главная</a>
				<img class="crumbs-sep" src="/img/icons/arrow.svg">
				<a class="crumbs" href="/articles">Список всех статей</a>
				<img class="crumbs-sep" src="/img/icons/arrow.svg">
				<span class="crumbs-cur">{{$article->title}}</span>
			</div>
			@endif
		</div>
	</div>
	@yield('content')


	<footer class="footer">
		<div class="container">
			<div class="footer_inner">
				<div class="footer_logo">
					<a href="/" class="footer_logo-img" title="На главную страницу"></a>
					<div class="footer_block phone">
						<p class="footer_block-title phone"><span class="footer_block-img"></span>377</p>
						<p class="footer_block-item">Операторы на связи</p>
						<p class="footer_block-item">с 8:00 до 21:00</p>
					</div>
				</div>

				<div class="footer_block footer_block-servis">
					<p class="footer_block-title servis">Сервис</p>
					<div class="block-servis">
						<a href="/delivery#zone" class="footer_block-item" title="Зоны доставки">Зоны доставки</a>
						<a href="/partner" class="footer_block-item" title="Наши партнёры">Партнеры</a>
						<!-- <a href = "/vacancy" class = "footer_block-item" title = "Вакансии">Вакансии</a> -->
						<a href="/offer" class="footer_block-item" title="Публичная оферта">Публичная оферта</a>
						<a href="/help" class="footer_block-item" title="Помощь покупателю">Помошь покупателю</a>
					</div>
				</div>
				<div class="footer_block footer_block-inf">
					<p class="footer_block-title inf">Информация</p>
					<div class="inf-box">
						<a href="/groups" class="footer_block-item" title="Каталог товаров">Каталог</a>
						<a href="/about" class="footer_block-item" title="О компании">О компании</a>
						<a href="/delivery" class="footer_block-item" title="Условия доставки и оплата">Доставка и оплата</a>
						<a href="/articles" class="footer_block-item" title="Статьи">Статьи</a>
						<a href="/stocks" class="footer_block-item" title="Условия акции - получай призы!">Акции</a>
						<a href="/spesial" class="footer_block-item" title="Супер цена">Специальные цены</a>
						<a href="/contacts" class="footer_block-item" title="Как с нами связаться ?">Контакты</a>
						<a href="/alcohol" class="footer_block-item" title="Порядок резервирования и приобретения продукции">Резервирование алкоголя</a>
					</div>
				</div>
				<div class="footer_block footer_block-social">
					<p class="footer_block-title social">Социальные сети</p>
					<div class="social-box">
						<a class="social-box_link" href="https://ok.ru/mtdelivery" target="_blank">
							<img class="social-icon" src="/img/icons/ok-small.svg" alt="Ссылка на страницу Одноклассники 'Мастер-Доставка'" title="Ссылка на страницу Одноклассники 'Мастер-Доставка'">
						</a>
						<a class="social-box_link" href="https://vk.com/mtdelivery" target="_blank">
							<img class="social-icon" src="/img/icons/vk-small.svg" alt="Ссылка на страницу Вконтакте 'Мастер-Доставка'" title="Ссылка на страницу Вконтакте 'Мастер-Доставка'">
						</a>
						<a class="social-box_link" href="https://t.me/@MTdelivery" target="_blank">
							<img class="social-icon" src="/img/icons/telegram-small.svg" alt="Ссылка на Телеграм 'Мастер-Доставка'" title="Ссылка на Telegram 'Мастер-Доставка'">
						</a>
						<a class="social-box_link" href="https://www.instagram.com/mtdostavka/" target="_blank">
							<img class="social-icon" src="/img/icons/instagram-small.svg" alt="Ссылка на Инстаграм 'Мастер-Доставка'" title="Ссылка на Instagram 'Мастер-Доставка'">
						</a>
					</div>
				</div>

			</div>
		</div>
	</footer>


	</div>
</body>

</html>