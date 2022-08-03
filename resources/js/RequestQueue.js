import axios from "axios";
import Eventor from "./Eventor.js"

export default class RequestQueue
{
    constructor(stopTimeout){
        if(stopTimeout && !Number(stopTimeout)) throw new Error("stopTimeout must be number");
        this.requests = new Array();
        this._eventor = new Eventor(["next","stop"]);
        this.stopTimeout = Number(stopTimeout);
        this.timeout = -1;
    }
    /**
     * 
     * @param {Request} request 
     */
    add(request){
        this.requests.unshift(request);
        if(this.isWait){
            this.start();
            this.isWait = false;
        }
    }
    addEventListener(type,listener){ this._eventor.addEventListener(type,listener); }
    addEventListener(type,listener){ this._eventor.addEventListener(type,listener); }
    /**
     * Запускает асинхронную отправку запросов очереди. Если очередь пуста, запустит отправку при следующем добавлении запроса в очередь
     * @returns 
     */
    async start(){
        if(this.started){
            console.error("RequestQueue is already started");
            return;
        }
        let next = ()=>{
            //console.log("tryNext");
            if(this.requests.length<1){
                this.isWait = true;
                return;
            }
            if(this.stopTimeout){//set stop timeout
                clearTimeout(this.timeout);
                this.timeout = setTimeout(()=>{
                    this.stop()
                    this.isStop = false;
                    this._eventor.dispatchEvent("stop",{ byTimeout:true });    
                },this.stopTimeout);
            }
            if(this.isStop){
                this.isStop = false;
                this._eventor.dispatchEvent("stop", { byTimeout:false });
                return;
            }
            //console.log("next");
            let isSkip = false;
            let request = this.requests.pop();
            this._eventor.dispatchEvent("next",{ request:request, skip:()=>{ isSkip = true; } })
            if(!isSkip)
                (request.method=="get"?
                axios[request.method](request.path, request.options):
                axios[request.method](request.path, request.options.params, { headers:request.options.headers }))
                .then(request.thenCallback)
                .catch(request.catchCallback)
                .finally(()=>{request.finallyCallback(); next(); });
            else next();
        };
        next();
    }
    stop(){
        this.isStop = true;
        this.isWait = false;
        if(this.stopTimeout){
            clearTimeout(this.timeout);
            this.timeout = -1
        }
    }
}
export class Request{
    /**
     * 
     * @param {['get','post']} method тип запроса
     * @param {""} path тело запроса
     * @param {{ params:, headers: }} options параметры запроса
     * @param {function} thenCallback 
     * @param {function} catchCallback 
     * @param {function} finallyCallback 
     */
    constructor(method, path, options,thenCallback=()=>{},catchCallback=()=>{},finallyCallback=()=>{}){
        if(method != 'get' && method != 'post')
            throw 'RequestQueue error: unknown method: '+method;
        this.method = method;
        this.path = path;
        this.options = options;
        this.thenCallback = thenCallback;
        this.catchCallback = catchCallback;
        this.finallyCallback = finallyCallback;
    }
    /**
     * Sets then callback
     * @param {function (arg0)} callback
     * @returns Request
     */
    then(callback){
        this.thenCallback = callback;
        return this;
    }
    /**
     * Sets catch callback
     * @param {function (arg0)} callback
     * @returns Request
     */
    catch(callback){
        this.catchCallback = callback;
        return this;
    }
    /**
     * Sets finally callback
     * @param {function (arg0)} callback
     * @returns Request
     */
    finally(callback){
        this.finallyCallback = callback;
        return this;
    }
}