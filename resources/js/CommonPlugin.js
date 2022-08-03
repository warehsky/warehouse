export default {
    install(Vue, options) {
        Vue.prototype.camelPad = window.camelPad = function(str){ //pascalCase => Word Case
            return str
            .replace(/([A-Z]+)([A-Z][a-z])/g, ' $1 $2')
            .replace(/([a-z\d])([A-Z])/g, '$1 $2')
            .replace(/([a-zA-Z])(\d)/g, '$1 $2')
            .replace(/^./, function(str){ return str.toUpperCase(); })
            .trim();
        }
        Vue.prototype.$saveInCart = function(itm) { // сохранение товарной позиции в корзине
            let item = {};
            item.id = itm.id;
            item.title = itm.title;
            item.price = itm.price;
            item.discountPrice = itm.discountPrice;
            item.discountBound = itm.discountBound;
            item.stockPrice = itm.stockPrice;
            item.image = itm.image;
            item.quantity = itm.quantity;
            item.groupTitle = itm.groupTitle;
            item.pickup = itm["pickup"] || 0;
            let mtCart = localStorage.mtCart;
            try{
                mtCart = JSON.parse(mtCart);
            }catch(e){
                mtCart = [];
            }
            
            if(Array.isArray(mtCart)){
                let id = -1;
                for(let i=0; i<mtCart.length; i++){
                    if(mtCart[i].id==itm.id){
                        
                        mtCart[i].price = itm.price;
                        mtCart[i].discountPrice = itm.discountPrice;
                        mtCart[i].stockPrice = itm.stockPrice;
                        mtCart[i].discountBound = itm.discountBound;
                        mtCart[i].quantity = itm.quantity;
                        item.groupTitle = itm.groupTitle;
                        id=itm.id;
                        break;
                    }
                }
                
                if(id<0)
                mtCart.push(item);
            }
            else{
                mtCart = [];
                mtCart.push(item);
            }
            var serialObj = JSON.stringify(mtCart);
            localStorage.mtCart = serialObj;
            this.localStorage.count = mtCart.length;
            window.dispatchEvent(new CustomEvent('cartChanged', {
                detail: {
                  mtCart: localStorage.mtCart
                }
            }));
        },
        Vue.prototype.$cart_del_item = function(itm){ // удалить товар из заказа
            let item;
            
            let mtCart = localStorage.mtCart;
            try{
                mtCart = JSON.parse(mtCart);
            }catch(e){
                mtCart = [];
            }
            if(mtCart==null)
              mtCart = [];
              
            if(Array.isArray(mtCart)){
              let id = -1;
              for(let i=0; i<mtCart.length; i++){
                if(mtCart[i].id==itm){
                  mtCart.splice(i, 1);
                  break;
                }
              }
              
            }
             
            var serialObj = JSON.stringify(mtCart);
            localStorage.mtCart = serialObj;
            this.localStorage.count = mtCart.length;
            if(this.localStorage.count==0){
                localStorage.codeWaite = 0;
                this.codeWaite = 0;
            }
            window.dispatchEvent(new CustomEvent('cartChanged', {
                detail: {
                  mtCart: localStorage.mtCart
                }
            }));
        }
        Vue.prototype.$getCurrencyPrice = function (value, course, UAH) {
            course = Number(course) || this.$course;
            UAH = UAH == undefined?Boolean(this?.$root?.UAH):Boolean(UAH);
            value = Number(value) || 0;
            return  UAH ? Math.ceil(value / course) : value;
        };
        Vue.getCurrencyPrice = Vue.prototype.$getCurrencyPrice;

        Vue.prototype.log = console.log;
        Vue.prototype.warn = console.warn;
        Vue.prototype.error = console.error;
        //global string filters
        // Vue.filter('currencydecimal',function(value){
        //     if (!value) return "";
        //     if (typeof value == "string") value = parseFloat(value);
        //     return value.toFixed(2) + "\u00A0₽";
        // })

        const setVisible = function(el,binding){
            if(binding.value){
                el.style.visibility = 'visible';
            }
            else{
                el.style.visibility = 'hidden';
            }
        }
        Vue.directive('visible',{
            update:setVisible,
            bind:setVisible
        })
    }
}