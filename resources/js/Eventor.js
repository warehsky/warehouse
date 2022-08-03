export default class Eventor {
    constructor(eventsList) {
        this.eventsList = eventsList;
        this.eventsMap = new Map();
    }
    dispatchEvent(event, detail) {
        if (!this.eventsMap.has(event))
            return;
        this.eventsMap.get(event).forEach((listener) => {
            listener(detail);
        });
    }
    addEventListener(type, listener) {
        if (!this.eventsList.includes(type))
            return;
        if (this.eventsMap.has(type))
            this.eventsMap.get(type).push(listener);
        else
            this.eventsMap.set(type, [listener]);
    }
    removeEventListener(type, listener) {
        if (!this.eventsList.includes(type) || !this.eventsMap.has(type))
            return;
        let listeners = this.eventsMap.get(type)
        listeners.forEach((l, index) => {
            if (listener == l) {
                listeners.splice(index, 1);
                return;
            }
        });
        if (listeners.length == 0)
            this.eventsMap.delete(type);
    }
    removeAllListeners(type=null){
        if(!type)
            this.eventsMap.clear();
        else{
            if (!this.eventsList.includes(type) || !this.eventsMap.has(type))
                return;
            this.eventsMap.delete(type);
        }

    }
}