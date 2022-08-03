/**
 * Таймер с возможностью восстановления после перезагрузки страници.
 * Использует локальное хранилище(localStorage) для сохранения состояния.
 * Запускается присоздании экземпляра.
 * После перезагрузки страницы при создании экземпляра с тем же id, таймер восстановится или запустится заново.
 * */
class GTimer{
    /**
     * Создает/восстанавливает и запускает таймер.
     */
    constructor(id, callback, timeout, checkInterval = timeout/10){
        this.storageName = "g_timer_"+id;
        let data;
        try{
            data = JSON.parse(localStorage[this.storageName]);
            if(!data) throw "";
        } catch{
            data = { time:new Date().getTime(), timeout, checkInterval:checkInterval>=1?checkInterval:1 }
            localStorage[this.storageName] = JSON.stringify(data);
        }
        this._interval = setInterval(()=>{
            if(new Date().getTime()-data.time>=data.timeout){
                this.stop();
                callback();
            }
        },data.checkInterval);
    }
    stop(){
        clearInterval(this._interval);
        localStorage.removeItem(this.storageName);
    }
}
/**
 * 
 * Восстанавливает и запускает таймер.
 */
GTimer.continue = function(id,callback){
    let storageName = "g_timer_"+id;
    let data;
    try{ data = JSON.parse(localStorage[storageName]); } catch { return false; }
    if(!data) return false;
    return new GTimer(id,callback,data.timeout,data.checkInterval);
}