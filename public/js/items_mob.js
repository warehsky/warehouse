var $ = jQuery;
var tm_parents = [];
var tm_parents_i = -1;
var parents = [];
var parents_i = -1;

var frmWeb;

jQuery(document).ready(function($) {
    // окно для показа web групп
    frmWeb = $( "#frmWeb" ).dialog({
        autoOpen: false,
        height: 300,
        width: 300,
        modal: true,
        buttons: {
            добавить: function(){
                setWebGroupItem();
                frmWeb.dialog( "close" );
            },
            закрыть: function() {
                frmWeb.dialog( "close" );
            }
        },
        close: function() {
            frmWeb.dialog( "close" );
        }
    });
    // окно для показа товаров торгового агента
    frmItems = $( "#frmItems" ).dialog({
        autoOpen: false,
        height: 500,
        width: 900,
        modal: true,
        buttons: {
            закрыть: function() {
                frmItems.dialog( "close" );
            }
        },
        close: function() {
            frmItems.dialog( "close" );
        }
    });
    var cur_page = 1;
    
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
    $("#snickers").click(function(e){
        $("#fsearch").val('snickers');
        getItems(1);
    });
    // getItems(1);
    getItemGroups(0);
    $('#price-list').hide();
    $('#remains-list').hide();
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
        lookup: [] // Список вариантов для локального автозаполнения
    });
    //поиск группы
    $('#t-group').autocomplete({
        serviceUrl: 'getGroupsList', // Страница для обработки запросов автозаполнения
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
    getWebGroups();
} );
function set_price_info(){
    $(".price-inf").on("mouseover", function(e){
        inf = $('#price-list');
        var s = $(this).attr('data-prices').split('\n');
        var sp = "";
        s.sort().forEach(function(item, index, array) {
            sp += "<p>"+item+"</p>";
        });
        
        inf.html( sp );
        y = e.pageY+10;
        if(y - $(document).scrollTop() + inf.height() > $(window).height())
            y = y - inf.height();
        inf.offset ( {
            left: e.pageX+10,
            top: y
        });
        inf.show();
    });
    $(".price-inf").on("mouseout", function(e){
        inf = $('#price-list');
        inf.html( '' );
        inf.offset ( { left: 0, top: 0 } );
        inf.hide();
    });
}

function set_remains_info(){
    $(".remains-inf").on("mouseover", function(e){
        inf = $('#remains-list');
        var s = $(this).attr('data-remains').split('\n');
        var sp = "";
        s.sort().forEach(function(item, index, array) {
            sp += "<p>"+item+"</p>";
        });
        inf.html( sp );
        y = e.pageY+10;
        
        if(y - $(document).scrollTop() + inf.height() > $(window).height())
            y = y - inf.height();
        
        inf.offset ( {
            left: e.pageX+10,
            top: y
        });
        inf.show();
    });
    $(".remains-inf").on("mouseout", function(e){
        inf = $('#remains-list');
        inf.html( '' );
        inf.offset ( { left: 0, top: 0 } );
        inf.hide();
    });
}

function getItems(page){
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
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            tr = "";
            if(data.data.length)
            data.data.forEach(function(item, index, ar){
                tr += "<tr><td class='item-id'>" + item.id + "</td><td>" + item.item + 
                      "</td><td class='remains-inf' data-remains='" + item.allquantity + "'>" + item.quantity + 
                      "</td><td class='price-inf' data-prices='" + item.allprice + "'>" + item.price + 
                      "</td><td>"  +
                      "<a class='btn primary' href='#' onclick='webItem("+item.id+", \""+item.item+"\")'>web</a></td></tr>";
                
                if(index==(ar.length-1)){
                    $('#items-tbl tbody').empty();
                    b = $('#items-tbl tbody');
                    b.append(tr);
                    pages_links_draw(data.current_page, data.last_page, data.path);
                    set_price_info();
                    set_remains_info();
                }
            });
            else{
                $('#items-tbl tbody').empty();
                $("#pages_links").html('');
            }
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
    data = {
        groupId: subgroup,
        tm: 'group',
        td: $("#flt-td").val()
    }
    $.ajax({
        url: '/Api/getGoodsGroup',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            li = "<ul id='group_" + subgroup + "' class='list-group'>";
            data.forEach(function(item, index, ar){
                li += "<li class='list-group-item " + (typeof item.color !== 'undefined' ? " group-" + item.color : "") + "'>";
                li += "<div id='" + item.id + "' data-path='" + item.parsname + "'>";
                if(item.childs){
                    li += '<span class="wr-group plus">+</span>';
                }else{
                    li += '<span class="no-group">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
                }
                li += "&nbsp;&nbsp;&nbsp;<span class='gr-name' >[#" + item.id + "]" + item.title + "</span>";
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
        }
    });
}
//Обновление времени последнего обновления складов
function refreshLastUpdate(){
    $.ajax({
        url: '/lastwarehouserefreshajax',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            $('#last_update').html(data.dateRefresh);
            setTimeout(refreshLastUpdate, 60000);
        },
        error: function (error) {
            console.log('error; ' + eval(error));
            setTimeout(refreshLastUpdate, 60000);
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
/*
*  Получение Web групп
*/
function getWebGroups(){
    data = {
        groupId: 0
    }
    $.ajax({
        url: '/Api/getItemGroups',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            console.log(data);
            let con = $("#frmWeb .addweb-content");
            con.empty();
            con.append(getSubGroup(data, "class='tree' id='tree'", ""));
            //  ловим клики на всём дереве
            tree = document.getElementById('tree');
            tree.onclick = function(event) {

                if (event.target.tagName != 'SPAN') {
                    return;
                }
                $(".addweb-choosen").html(event.target.innerHTML);
                $("#addweb-choosen-id").val(event.target.getAttribute('data-id'));
                let childrenContainer = event.target.parentNode.querySelector('ul');
                
                if (!childrenContainer) return; // нет детей

                childrenContainer.hidden = !childrenContainer.hidden;
            }
        },
        error: function (error) {
            console.log('error; ' + eval(error));
        }
    });
}
//
function getSubGroup(items, str, h){
    let ul = "<ul "+str+h+">";
    items.forEach(function(item, index, ar){
        ul += "<li><span data-id='" + item.id + "'>" + item.title + "</span>";
        if(item.childs.length>0)
           ul += getSubGroup(item.childs, "", " hidden=''");
        "</li>";
    });
    ul += "</ul>";
    return ul;
}
//
function webItem(itemId, item){
    $("#id").val(itemId);
    $("#item-mob").html(item);
    $("#group-mob").html($("#gr-path").html());
    let s = $("#title");
    let f = $("#longTitle");
    if(s.val()=='')
        s.val(item);
    if(f.val()=='')
        f.val(item);
}