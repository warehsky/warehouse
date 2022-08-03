import Eventor from './Eventor.js';
import RequestQueue, { Request } from './RequestQueue.js';

export default class Watcher{
    /**
     * Если передан параметр requestQueue, запросы добавляются в очередь запросов каждый interval времени.
     * @param {['get','post']} method тип запроса
     * @param {""} query тело запроса
     * @param {{ params:, headers: }} options параметры запроса
     * @param {1000} interval интервал между запросами в милисекундах
     * @param {RequestQueue} requestQueue очередь запросов(не обязательно). Если 
     */
    constructor(method, query="", options=null, interval=1000, requestQueue){
        if(method != 'get' && method != 'post')
            throw 'Watcher error: unknown method: '+ method;
        this.method = method;
        this.watching = false;
        this.interval = interval;
        this._eventor = new Eventor(['receive','error']);
        this.query = query;
        this.options = options;
        this.requestQueue = requestQueue;
    }
    /** Метод добавления слушателя события на получение ответа от сервера или ошибки ['receive','error'] */
    addEventListener(type,listener){ this._eventor.addEventListener(type, listener); }
    /** Метод удаления слушателя события на получение ответа от сервера или ошибки ['receive','error'] */
    removeEventListener(type,listener){ this._eventor.removeEventListener(type, listener); }
    removeAllListeners(){ this._eventor.removeAllListeners(); }
    stop(){
        this.watching = false;
    }
    start(){
        if(!this.query)
            throw "query required.";
        if(this.watching)
            return;
        let thenHandler = (response)=>{ this._eventor.dispatchEvent('receive', response); };
        let catchHandler = (e)=>{ this._eventor.dispatchEvent('error',e); };
        let this1 = this;
        function finallyHandler(){if(this1.watching) setTimeout(next,this1.interval); }
        let next = ()=>{
            if(this.requestQueue)
                this.requestQueue.add(new Request(this.method,this.query,this.options)
                    .then(thenHandler).catch(catchHandler).finally(finallyHandler));
            else
                axios[this.method](this.query, this.options)
                    .then(thenHandler).catch(catchHandler).finally(finallyHandler)	
        }
        this.watching = true;
        next();
    }
    /**
     * Делает запрос без очереди.
     */
    request(){
        axios[this.method](this.query, this.options)
            .then((response)=>{ this._eventor.dispatchEvent('receive', response); })
            .catch((e)=>{ this._eventor.dispatchEvent('error',e); });
    }
}