@extends('layouts.app')

@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/jquery.autocomplete.min.js') }}" ></script>
<link href="{{ asset('css/remains.css') }}" rel="stylesheet">


<div class="container">
  <input type="hidden" id="api_token" value="{{$api_token}}">
    <!-- переключатель -->

    <div class="row controls">
    <div class="col-8">
      <div class="custom-control custom-radio custom-control-inline">
        <input type="radio" id="selectByGroup" name="selectBy" value="group" class="custom-control-input" checked>
        <label class="custom-control-label" for="selectByGroup">Группы</label>
      </div>
      <div class="custom-control custom-radio custom-control-inline">
        <input type="radio" id="selectByMark" name="selectBy" value="mark" class="custom-control-input">
        <label class="custom-control-label" for="selectByMark">Торговые марки</label>
      </div>
      <div class="custom-control-inline" id="s-tm-box">
        <label  for="qgroup">поиск торговой марки&nbsp;</label>
        <input type="text" name="qgroup" id="qgroup"  />
      </div>
      <div class="custom-control-inline" id="s-group-box">
        <label  for="t-group">поиск группы&nbsp;</label>
        <input type="text" name="t-group" id="t-group"  />
      </div>
    </div>
  </div>

    <!-- переключатель конец -->
    <div class="row justify-content-left">
        <div class="col-md-4 left-col" id="left_box_gr">
            <ul id="group_0" class="list-group">
            <?php foreach($groups as $group):?>
            <li class="list-group-item  @if(isset($group->color)) group-{{$group->color}} @endif ">
                <div id="{{$group->id}}"  data-path="">
                @if($group->childs) <span class="wr-group plus">+</span> @else <span class="no-group">&nbsp;&nbsp;&nbsp;&nbsp;</span> @endif
                &nbsp;&nbsp;&nbsp;<span class="gr-name">{{$group->title}}</span>
                </div>
            </li>
            <?php endforeach ?>
            </ul>
        </div>
        <div class="col-md-6">
            <div id="gr-path" class="gr-path">&nbsp;</div>
            
            <div class="tables_filter" id="tables_filter">
                <label>Search:<input id="fsearch" type="search" class="" placeholder="" aria-controls="items-tbl"></label>
                <input type="hidden" id="filter_group" value="0">
            </div>
            
            <div class="group-chosen tables_filter_l">
                <table>
                    <tr>
                        <td id="item-chosen"><div></div></td>
                        <td id="choce-reset"><div>X</div></td>
                    </tr>
                </table>
            </div>

            <table id="items-tbl" class="table table-striped table-bordered table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Наименование</th>
                        <th>Наличие</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
                <tfoot>
                    <tr>
                        <th>Наименование</th>
                        <th>Наличие</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        
                    </tr>
                </tfoot>
            </table>
            <div id="pages_links"></div>
        </div> 
        
    </div>
</div>

<div id="mdb-preloader" class="flex-center" style="z-index: 20000;position:absolute;top:0;left:0;width:100%;height:100%;">
    <div style="margin:auto;">
        <img src="{{ asset('img/preloader.gif') }}" />
    </div>
</div>

<script>
    var $ = jQuery;
    var tm_parents = [];
    var tm_parents_i = -1;
    var parents = [];
    var parents_i = -1;
    jQuery(document).ready(function($) {
        var cur_page = 1;
        // setTimeout(refreshLastUpdate, 60000);
        $("#group_0 div").on("click", groupWrap);
    
        $("#choce-reset div").on("click", function(){
            $("#item-chosen div").html("");
            $("#gr-path").html("");
            $(".left-col .gr-name").removeClass('gr-name-active');
            $("#group_0 div").removeClass('gr-name-active');
            $("#filter_group").val(0);
            getItems(1);
            $('#group_0 .wr-group').html('+');
            $('#group_0 ul').remove();
        });
        $('input[name=selectBy]').change(function(e){
            if($('input[name=selectBy]:checked').val() === 'mark'){
                $("#s-group-box").hide();
                $("#s-tm-box").show();
            }
            else{
                $("#s-tm-box").hide();
                $("#s-group-box").show();
            }
            getItemGroups(0);
        });
        $("#s-tm-box").hide();
        $('#flt-areas').change(function(e){
            getItems(cur_page);
        });
        $('#flt-price').change(function(e){
            getItems(cur_page);
        });
        $("#fsearch").keyup(function(e){
            getItems(1);
        });
        
        // getItems(1);
        
        //поиск торговой марки
        $('#qgroup').autocomplete({
            serviceUrl: 'service/autocomplete.ashx', // Страница для обработки запросов автозаполнения
            minChars: 1, // Минимальная длина запроса для срабатывания автозаполнения
            delimiter: /(,|;)\s*/, // Разделитель для нескольких запросов, символ или регулярное выражение
            maxHeight: 400, // Максимальная высота списка подсказок, в пикселях
            width: 400, // Ширина списка
            zIndex: 9999, // z-index списка
            deferRequestBy: 100, // Задержка запроса (мсек), на случай, если мы не хотим слать миллион запросов, пока пользователь печатает. Я обычно ставлю 300.
            params: { country: 'Yes'}, // Дополнительные параметры
            onSelect: grcomplete , // Callback функция, срабатывающая на выбор одного из предложенных вариантов,
            showNoSuggestionNotice: true,
            noSuggestionNotice: "совпадений не найдено!",
            lookup: <?=$tm?> // Список вариантов для локального автозаполнения
        });
        //поиск группы
        $('#t-group').autocomplete({
            serviceUrl: 'Api/getGroupsList', // Страница для обработки запросов автозаполнения
            minChars: 1, // Минимальная длина запроса для срабатывания автозаполнения
            delimiter: /(,|;)\s*/, // Разделитель для нескольких запросов, символ или регулярное выражение
            maxHeight: 400, // Максимальная высота списка подсказок, в пикселях
            width: 400, // Ширина списка
            zIndex: 9999, // z-index списка
            deferRequestBy: 100, // Задержка запроса (мсек), на случай, если мы не хотим слать миллион запросов, пока пользователь печатает. Я обычно ставлю 300.
            onSelect: grfinded , // Callback функция, срабатывающая на выбор одного из предложенных вариантов,
            showNoSuggestionNotice: true,
            noSuggestionNotice: "совпадений не найдено!",
        });
    } );
    

    function getItems(page){
        $('#mdb-preloader').delay(0).fadeIn(100);
        cur_page = page;
        data = {
            page: page, 
            groupId: $("#filter_group").val(),
            area: $("#flt-areas").val(),
            price: $("#flt-price").val(),
            tm: $('input[name=selectBy]:checked').val(),
            search: $("#fsearch").val()
        }
        $.ajax({
            url: '/Api/getGoodsItems',         /* Куда пойдет запрос */
            headers: {'X-Access-Token': $("api_token").val()},
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-Access-Token", $("#api_token").val());
            },
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
            success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                tr = "";
                if(data.data.length)
                data.data.forEach(function(item, index, ar){
                    tr += "<tr><td>" + item.item + 
                          "</td><td class='remains-inf' >" + (parseInt(item.quantity)>0?"в наличии":"не в наличии") + 
                          "</td><td id='price" + item.id + "' class='price-inf' >" + item.price + 
                          "</td> <td> <div class='number'> <input type='number' id='count-items" + item.id + "' value='0' min='0' size='5' data-id='" + item.id + "' onchange='addtocart(" + item.id + ")'/></div>" +
                          "</td><td id='total-price" + item.id +"'></td></tr>";

                    /* <a "+($("#fsearch").val() === '' ? '' : "onclick=grcomplete(\'" + item.parIds + "\')") + 
                          " class='btn primary ' onclick='addtocart(" + item.id + ")' data-id='" + item.id + "' href='#' >"+($("#fsearch").val()===''? 'action' : "группа") +
                          "</a> */

                    if(index==(ar.length-1)){
                        $('#items-tbl tbody').empty();
                        b = $('#items-tbl tbody');
                        b.append(tr);
                        pages_links_draw(data.current_page, data.last_page, data.path);
                        
                    }
                });
                else{
                    $('#items-tbl tbody').empty();
                    $("#pages_links").html('');
                }
                $('#mdb-preloader').delay(0).fadeOut(100);
	        },
            error(e){
                $('#mdb-preloader').delay(0).fadeOut(100);
                console.error(e);

            }
        });
    }

    function pages_links_draw(p_cur, p_total, url){
        if((p_cur+8) < p_total)
            p_to = p_cur+8;
        else
        p_to = p_total;
        var strout = '<ul class="pagination" role="navigation">';
        if(p_cur<=1){
            strout += '<li class="page-item disabled" aria-disabled="true" aria-label="&lsaquo; Пред">';
            strout += '<span class="page-link" aria-hidden="true">&lsaquo;</span>';
            strout += '</li>';
        }else{
            strout += '<li class="page-item">';
            strout += '<a class="page-link" onclick="getItems('+(p_cur-1)+')" href="#" rel="next" aria-label="Пред &raquo;">&lsaquo;</a>';
            strout += '</li>';
        }
        if(p_total>10){
            start = p_cur;
            if(p_total-start<10){
                start = p_total-10;
                if(start<1)
                    start = p_total;
            }
        }
        else{
            start = 1;
        }
        for(i=start; i<=p_to; i++){
            if(i==p_cur)
                strout += '<li class="page-item active" aria-current="page"><span class="page-link">'+p_cur+'</span></li>';
            else
                strout += '<li class="page-item"><a class="page-link" onclick="getItems('+i+')" href="#">'+i+'</a></li>';
        }
        if(p_to < (p_total-1))
            strout += '<li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>';
        if(p_to < (p_total-2))
            strout += '<li class="page-item"><a class="page-link" onclick="getItems('+(p_total-1)+')" href="#">'+(p_total-1)+'</a></li>';
        if(p_to < (p_total))
            strout += '<li class="page-item"><a class="page-link" onclick="getItems('+(p_total)+')" href="#">'+(p_total)+'</a></li>';
        if(p_cur < p_total){
            strout += '<li class="page-item">';
            strout += '<a class="page-link" onclick="getItems('+(p_cur+1)+')" href="#" rel="next" aria-label="След &raquo;">&rsaquo;</a>';
            strout += '</li>';
        }else{
            strout += '<li class="page-item disabled" aria-disabled="true" aria-label="&raquo; След">';
            strout += '<span class="page-link" aria-hidden="true">&rsaquo;</span>';
            strout += '</li>';
        }
        strout += '</ul>';
        $("#pages_links").html(strout);
    }
    function groupWrap(){
        gr = $('#group_' + this.id);
            if( gr.length > 0 ){
                gr.remove();
                $(this).find('.wr-group').html('+');
                $(this).addClass('gr-name-active');
                $("#item-chosen div").html(this.innerHTML);
                $("#filter_group").val(this.id);
                getItems(1);
            }
            else{
                getItemGroups(this.id);
                $(this).find('.wr-group').html('-');
                $("#group_0 div").removeClass('gr-name-active');
                $(this).addClass('gr-name-active');

                $(".left-col .gr-name").removeClass('gr-name-active');
                $(this).addClass('gr-name-active');
                $("#item-chosen div").html(this.innerHTML);
                $("#filter_group").val(this.id);
                $("#gr-path").html(this.attributes["data-path"].nodeValue);
                getItems(1);
            }
    }
    function getItemGroups(subgroup){
        $('#mdb-preloader').delay(0).fadeIn(100);
        data = {
            groupId: subgroup,
            tm: $('input[name=selectBy]:checked').val(),
            td: $("#flt-td").val()
        }
        $.ajax({
            url: '/Api/getGoodsGroup',         /* Куда пойдет запрос */
            headers: {'X-Access-Token': $("api_token").val()},
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-Access-Token", $("#api_token").val());
            },
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
            success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                $('#mdb-preloader').delay(0).fadeOut(100);
                li = "<ul id='group_" + subgroup + "' class='list-group'>";
                data.forEach(function(item, index, ar){
                    li += "<li class='list-group-item " + (typeof item.color !== 'undefined' ? " group-" + item.color : "") + "'>";
                    li += "<div id='" + item.id + "' data-path='" + item.parsname + "'>";
                    if(item.childs){
                        li += '<span class="wr-group plus">+</span>';
                    }else{
                        li += '<span class="no-group">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
                    }
                    li += "&nbsp;&nbsp;&nbsp;<span class='gr-name' >" + item.title + "</span>";
                    li += "</li>";
                    if(index==(ar.length-1)){
                        li += "</ul>";
                        if(subgroup>0)
                            b = $('#'+subgroup).parent();
                        else{
                            b = $('#group_'+subgroup).parent();
                            b.empty();
                        }
                        b.append(li);
                        $("#group_" + subgroup + " div").on("click", groupWrap);
                        // если это поиск торговой марки
                        if( tm_parents_i >= 0 ){
                            $("#"+tm_parents[tm_parents_i]).find('.wr-group').html('-');
                            if( tm_parents_i == (tm_parents.length-2) ){
                                $("#item-chosen div").html($("#"+tm_parents[tm_parents_i+1]).html());
                                $("#filter_group").val(tm_parents[tm_parents_i+1]);
                                $("#"+tm_parents[tm_parents_i+1]).addClass("gr-name-active");
                                $("#gr-path").html($("#"+tm_parents[tm_parents_i+1]).attr("data-path"));
                                box = document.getElementById("left_box_gr");
                                el = document.getElementById(tm_parents[tm_parents_i+1]);
                                x = 0;
                                y = el.getBoundingClientRect().top - box.getBoundingClientRect().top - el.parentNode.getBoundingClientRect().height;
                                tm_parents_i = -1;
                                tm_parents = [];
                                box.scrollTo(x, y);
                                getItems(1);
                            }else{
                                if(subgroup>0)
                                    tm_parents_i++;
                                if( !$("#"+tm_parents[tm_parents_i]).hasClass("gr-name-active") )
                                    getItemGroups(tm_parents[tm_parents_i]);
                            }
                        }
                        
                    }
                });
                if( parents_i >= 0 ){ // если это поиск группы
                    if( parents_i == (parents.length-1) ){
                        box = document.getElementById("left_box_gr");
                        el = document.getElementById(parents[parents_i]);
                        x = 0;
                        y = el.getBoundingClientRect().top - box.getBoundingClientRect().top - el.parentNode.getBoundingClientRect().height;
                        parents_i = -1;
                        parents = [];
                        box.scrollTo(x, y);
                        //getItems(1);
                    }
                    parents_i++;
                    $("#" + parents[parents_i]).trigger('click');
                }
            },
            error(e){
                $('#mdb-preloader').delay(0).fadeOut(100);
                console.error(e);

            }
        });
    }
    
    // Когда выбрана по поиску торговая марка, устанавливаем ее текущей, раскрываем все родительские группы и скролл к выбранной ТМ
    function grcomplete(data){
        if( typeof data.data === 'undefined' )
            tm_parents = data.split(",");
        else
            tm_parents = data.data.split(",");
        tm_parents_i = 0;
        if($("#filter_group").val() == tm_parents[tm_parents.length-1])
            return;
        getItemGroups(0);
    }
    // Когда выбрана по поиску группа товара, устанавливаем ее текущей, раскрываем все родительские группы и скролл к выбранной группе
    function grfinded(data){
        if( typeof data.data === 'undefined' )
            parents = data.split(",");
        else
            parents = data.data.split(",");
        parents_i = 0;
        var newArray = parents.slice(0, parents.length);
        parents.forEach(function(item, index, ar){
            parents_i = index;
            if($("#" + item).find('.wr-group').html() == '+'){
                $("#" + item).trigger('click');
                parents.length = parents.indexOf(item); // прервать цыкл
            }
        });
        parents = newArray;
    }

    
</script>

@endsection
