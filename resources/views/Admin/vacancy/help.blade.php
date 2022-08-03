@extends('layouts.admin')

@section('content')

<h3 style="margin-top:10px">Страница помощник при заполнении вакансии</h3>
<hr>

<h3>Отображение на сайте</h3>
<p>Прежде всего стоит рассмотреть страницу вакансий на сайте. В разделе "Открытые вакансии" находится название вакансии (Пункт №1). 
    Далее в пункт №2 выводится описание вакансии. 
    Пункт №3 содержит в себе названия особенностей выбранной вакансии. Соответсвенно в пункт №4 выводятся все значения особенностей.
    Ниже находятся характеристики выбранной вакансии (Пункт №5). И аналогично с особенностями в пункте №6 выводятся значения характеристик.
    В пункте №12 находится иконка вакансии.
</p>

<img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto " src="/img/vacancy/VacancyHelpImg.png"  width="932" height="630">
<hr>


    <h3>Характеристики и особенности</h3>
        <p>В первую очередь для создания вакансии, требуется создать(убедится в наличии) необходимых особенностей и характеристик. 
            Для этого необходимо перейти в раздел особенности/характеристики (Пункт №7)</p>
    <img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto" src="/img/vacancy/VacancyButtons.png" width=80% height=80%>
    <hr>
    <h3>Создание/редактирование названий характеристик и особенностей</h3>
    <p>При нажатии на кнопку характеристики/особенности мы попадаем на старницу редектирования названия характеристики/особенности. 
        В верхней части находится строка для ввода нового названия особенности/характеристики (Пункт №8). 
        Для перехода к созданию/редактированию значений особенности/характеристики необходимо нажать на его название (пункт №9).
    </p>
    <img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto" src="/img/vacancy/VacansyPropertyTitleCreate.png" width=40% height=40%>
    <img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto; margin-top:10px" src="/img/vacancy/VacansySpecialtyTitleCreate.png" width=40% height=40%>
    <hr>
    <h3>Создание/редактирование значений характеристик и особенностей</h3>
        <p>Аналогично с прошлым меню, в верхней части находится строка для создания нового значения (Пункт №10). 
            В пункте №11 можно просмотреть какие значения есть в определенной характеристике.</p>
    <img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto" src="/img/vacancy/VacancySpecialtyCreate.png">
    <img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto; margin-top:10px" src="/img/vacancy/VacancyPropertyCreate.png">
    <hr>





<h3>Создание вакансии</h3>
        <p>После вводе названия и описания вакансии, необходимо указать иконку отображаемую на сайте (Пункт №12). 
        Пункт "Требуется в данный момент" указывает будет ли вакансия отображаться на сайте (Пункт №13)
        Нажатие кнопки "Далее" переместит Вас на страницу добавления характеристик и особенностей.
        </p>
    <img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto" src="/img/vacancy/VacancyCreate.png">
    <hr>
    <h3>Добавление характеристик и особенностей в вакансию</h3>
        <p>Далее Вам требуется выбрать неободимые характеристики и особенности вакансии (Пункт №14, 15). 
            На этой странице так же можно редактировать название,описание и изображение вакансии. 
            Кнопка "Сохранить" завершает создание вакансии.
        </p>
    <img style="box-shadow: 0 0 10px rgba(0,0,0,0.5); display: block; margin-left: auto; margin-right: auto; margin-bottom:20px" src="/img/vacancy/VacancyEdit.png" width=80% height=80%>
        
    <a href="{{ route('vacancy.index')}}" style="margin-bottom:20px; margin-left:10px;"><button  class="btn btn-default" >Назад к списку вакансий</button></a>

@endsection