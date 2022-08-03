/*
*  Получение Web групп
*/
function getWebGroups(){
    data = {
        groupId: 0
        
    }
    $.ajax({
        url: '/Api/getMenuItems',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            let con = $("#web-groups");
            con.empty();
            con.append(getSubGroup(data, "class='tree' id='tree'", "", "class='menu-top'"));
            //  ловим клики на всём дереве
            tree = document.getElementById('tree');
            
            tree.onmouseover = function(event) {
                
                if (event.target.tagName !== 'DIV' && event.target.tagName !== 'IMG') {
                    return;
                }
                
                let childrenContainer = event.target;
                
                for(i=0; i<event.target.getAttribute('data-level'); i++)
                    childrenContainer = childrenContainer.parentNode;
                
                childrenContainer = childrenContainer.querySelector('ul');
                if(childrenContainer != null)
                    childrenContainer.hidden = !childrenContainer.hidden;
                else
                    return;
                childrenContainer.onmouseover = function(event) {
                    event.target.parentNode.hidden = false;
                }
                
            }
            tree.onmouseout = function(event) {
                
                if (event.target.tagName !== 'DIV' && event.target.tagName !== 'IMG') {
                    return;
                }
                
                let childrenContainer = event.target;
                
                for(i=0; i<event.target.getAttribute('data-level'); i++)
                    childrenContainer = childrenContainer.parentNode;
                
                childrenContainer = childrenContainer.querySelector('ul');
                if(childrenContainer != null)
                    childrenContainer.hidden = true;
                
            }
            
        },
        error: function (error) {
            console.log('error; ' + eval(error));
        }
    });
    function getSubGroup(items, str, h, top){
        openGroups=[];
        let ul = "<div "+str+h+">";
        items.forEach(function(item, index, ar){
            if(str == "class='menu-h'"){
                txt = "inner-menu-text";
                icon = "";
                
            }
            else{
                txt = "top-menu-text";
                icon = "<div data-level='1' class='top-menu-icon'><img data-level='2' src='/img/img/catalog/icons/" + item.id + ".svg' /></div>";
            }
            ul += "<div "+top+"><div style='width: 100%;' data-level='0' data-id='" + item.id + "'>"+icon;
            ul += "<div data-level='1' class='" + txt + "'>" + item.title + "</div>";
            ul += "";
            if(openGroups.includes(item.id))
                hh = "";
            else
                hh =  " hidden=''";
            if( typeof item.children != 'undefined' && item.children.length>0){
                //  ul += getSubGroup(item.children, "class='menu-h'", hh, "class='menu-inner'");
                ul += "<ul hidden=''>";
                item.children.forEach(function(item, index, ar){
                    ul += "<li><a href='/?group=" + item.id + "' >" + item.title + "</a></li>";
                });
                ul += "</ul>";
            }
            ul += "</div></div>";
        });
        ul += "</div>";
        return ul;
    }
    
}
/**
 * получить товары
*/
function getItems(parentId){
    data = {
        parentId: parentId
        
    }
    $.ajax({
        url: '/getItemsJson',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            let itemscont = $("#main-content");
            let tbl = getWebItemsTbl(data);
            itemscont.empty().append(tbl);
            initial_btns();
        },
        error: function (error) {
            console.log('error; ' + eval(error));
        }
    });
}
/**
 * строит таблицу из товаров, возвращает в виде строки
 */
function getWebItemsTbl(data){
    if(data[0].length==0)
        return '<h2>Товары временно отсутствуют в данной категории</h2>';
    let tbl = "";
    
    data[0].forEach(function(item, index, ar){
        if(item.cart){
            btn = 'btn-incart';
            btn_title = 'В корзине';
        }
        else{
            btn = 'btn-buy';
            btn_title = 'Купить';
        }
        tbl += "<div class='good-items'>";
        tbl += "<div class='good-items-empty'>" + (item.quantity>0 ? "&nbsp;" : "Товара нет в наличии") + "</div>";
        tbl += "<div class='good-items-img'><img src='" + item.img_small + "' /></div>";
        tbl += "<div class='good-items-firm'>&nbsp;</div>";
        tbl += "<div class='good-items-title'>" + item.title + "</div>";
        //tbl += "<div class='good-items-price'>" + item.price + " &#8381;</div>";
        tbl += "<div class='good-items-btn'><div class='btn-container-l'><a class='btn " + btn + "' data-id='" + item.id + 
        "' href='#'>" + btn_title + "</a></div><div class='btn-container-r'>" + item.price + " &#8381;</div></div>";
        tbl += "</div>";
    });
    
    return tbl;
}
/**
 * закрывает верхний уровень меню
*/
function hideTopMenu(){
    // скрыть подменю 
    let menus = document.getElementsByClassName('menu-h');
    for(i=0; i<menus.length; i++)
        menus[i].hidden = true;
    // убрать выделение
    menus = document.getElementsByClassName('menu-top');
    for(i=0; i<menus.length; i++)
        menus[i].className = 'menu-top';
}

/**
 *  инициирует кнопки товаров
*/
function initial_btns(){
    $(".btn-buy").on("click", function(e){ //кнопка купить
        if(e.target.getAttribute('data-incart')==1)
            return;
        addtocart(e.target.getAttribute('data-id'), 1);
        e.target.className = 'btn btn-buy btn-incart';
        e.target.setAttribute('data-incart', 1);
        e.target.text = 'В корзине';
    });
}