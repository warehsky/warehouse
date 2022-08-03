import { loadYmap } from "vue-yandex-maps";
import Eventor from "./Eventor";
export default class MtMap{
    /**
     * @param {{ coords:Number[2], addr:String }} defaultPosition Местонахождение по умолчанию.
     * @param {Boolean} detailed Определяет, нужно ли показывать соты на карте и номер соты, в которой находится метка
     * @param { String } mode Режим Yandex/Mt api
     */
    constructor(defaultPosition = { coords:[37.80329, 48.003406], addr:"" }, detailed = false, mode = "Yandex"){
        this.geocodeMode = mode;
        this.detailed = detailed;
        this._eventor = new Eventor(["load","placemarkDragend","clickPolygon"])
        this.addEventListener = (type,listener)=>{ this._eventor.addEventListener(type,listener) }
        this.removeEventListener = (type,listener)=>{ this._eventor.removeEventListener(type,listener) }
        this.addr = defaultPosition.addr;
        this.customerCoord = defaultPosition.coords;
        this.ymapsReady = false;
        this.ymapSettings = {
            apiKey:"2d081426-c403-4005-ac1e-6b911b5638a4",
            lang:"ru_RU",
            coordorder:"longlat",
            version:"2.1",
        };
        this.geolocationSettings = {
            mapStateAutoApply:false,
            provider:"browser",
        };
        this.ymapsInzoneIcon = "islands#blueStretchyIcon"; //islands#blueDeliveryIcon
        this.ymapsOutzoneIcon = "islands#redStretchyIcon";
        this.placemarkOptions = {
            draggable:true,
            preset:this.ymapsInzoneIcon,
            hasBalloon:false,
            cursor:"grab",
        };
        this.suggestsPromisesCount = -1;
        this.zones = null;
        this.sots = null;
    }
    /**
     * 
     * @param { Number[] } center Координаты центра карты
     * @param { Number } zoom Приближение карты
     * @returns { Promise }
     */
    init(center = [0,0],zoom = 10){
        return new Promise((resolve,reject)=>{
            loadYmap({ ...this.ymapSettings, debug: true }).then(()=>{
                //"zoomControl","searchControl","typeSelector","geolocationControl","fullscreenControl","trafficControl","rulerControl"
                this.myMap = new ymaps.Map("map", { center, zoom, controls: ["zoomControl", "geolocationControl"] });
                ymaps.ready((...params)=>{
                    this.ymapsReady = true;
                    this._eventor.dispatchEvent("load",params);
                    resolve(params);
                });
            }).catch(reject);
        });
    }
    loadCustomFullscreenControl(){
        let customMapButton = new ymaps.control.FullscreenControl({
            data:{ content: 'На весь экран' },
            options:{
                layout: ymaps.templateLayoutFactory.createClass(
                    "<div class='mapButton [if state.selected]mapButtonSelected[endif]' title='$[data.title]'>" + 
                            "<p>" + "$[data.content]" + "</p>" +
                    "</div>")
            }
        });

        customMapButton.events.add('click',()=>{
            let fullscreen = customMapButton.state.get("fullscreen",false);
            customMapButton.data.set({ content: fullscreen?'На весь экран':'Выход из полноэкранного режима' }) 
        });

        this.myMap.controls.add(customMapButton);
    }
    loadPolygon(type,geometry,options,properties){// загрузка полигонов зон и сот доставки доставки
        if(type!="zones" && type!="sots")
            throw new Error("value of argument \"type\" was:" + type + " must be \"zones\" or \"sots\".")
        this[type] = this[type]||[];
        let polygon = new ymaps.Polygon(geometry);
        if(options)
            polygon.options.set(options);
        if(properties)
            polygon.properties.set(properties);
        if(type=="zones")
            polygon.events.add("click",(e)=>{ this._eventor.dispatchEvent("clickPolygon",e); });
        this.myMap.geoObjects.add(polygon);
        this[type].push(polygon);
    }
    setPlacemarkContent(callback){
        if(this.placemark)
            this.placemark.properties.set("iconContent",callback(this.placemark.properties.get("iconContent")))
    }
    locateCustomer(){
        return new Promise((resolve,reject)=>{
            if(!this.ymapsReady)
                reject(new Error("ymaps is not ready"));
            this.placemark = this.createPlacemark();
            resolve();
        });
    }
    createPlacemark(coordinates, preset = "islands#blueStretchyIcon"){
        let placemark = new ymaps.Placemark(coordinates);
        placemark.options.set(this.placemarkOptions);
        placemark.options.set("preset", preset);
        placemark.events.add("dragstart", () => {
          this.placemarkDragstart(placemark);
        });
        placemark.events.add("drag", () => {
          this.updatePlacemark(placemark);
        });
        placemark.events.add("dragend", () => {
          this.placemarkDragend(placemark);
        });
        this.myMap.geoObjects.add(placemark);
        //this.placemark = placemark;
        return placemark;
    }
    movePlacemarkTo(el,coords){
        el.geometry.setCoordinates(coords);
        return this.geocode(coords).then((response) => {
            let output = "";
            if(this.geocodeMode == "Yandex") {
                let geocodeResult = response.geoObjects.get(0);
                output = this.getYandexOutputName(geocodeResult.properties._data);
            }
            else{
                output = this.getMtApiOutputName(response.data);
            }
            let dop = "";
            if(this.detailed){
                let index = this.getZoneContains(coords, this.sots);
                if(index>=0)
                    dop = "("+this.sots[index].properties.get("id")+")";
            }
            el.properties.set("iconContent", output+dop);
            this.addr = output;
            this.customerCoord = coords;
            localStorage.customerCoord = JSON.stringify(this.customerCoord);
            localStorage.addr = this.addr;
            this._eventor.dispatchEvent('placemarkDragend',output);
        })
    }
    placemarkDragstart(el) {
        this.oldPlacemarkCoords = el.geometry._coordinates;
    }
    updatePlacemark(el) {
        if (this.getZoneContains(el.geometry._coordinates, this.zones) == -1)
            el.options.set("preset", this.ymapsOutzoneIcon);
        else el.options.set("preset", this.ymapsInzoneIcon);
    }
    placemarkDragend(el) {
        //console.log(el.geometry._coordinates,"zone:"+ this.getZoneContains(el.geometry._coordinates, this.zones));
        if (this.getZoneContains(el.geometry._coordinates, this.zones) == -1)
            el.geometry.setCoordinates(this.oldPlacemarkCoords);
        this.movePlacemarkTo(el,el.geometry._coordinates);
        this.updatePlacemark(el);
    }
    geocode(coordinates){
        return (this.geocodeMode == "Yandex"?ymaps.geocode(coordinates, { results: 1 }):
        axios.get("/Api/getAddress",{ params:{ lng:coordinates[0], lat:coordinates[1] } }))
    }
    /**
     * 
     * @param {*} value Искомый адрес
     * @param {*} results Результаты поиска
     * @param {*} predicate Только если изпользуется Api Yandex. Условие отбора по точкам.
     * @returns Promise
     */
    getSuggests(value,results = 100,predicate = undefined) {//поиск в пределах полигона по строке 
        return new Promise((resolve,reject)=>{
            try{
                let promiseId = ++this.suggestsPromisesCount;
                if (value.length < 1)
                    return;
                if(this.geocodeMode == "Yandex")
                    ymaps.geocode(value, { results: results }).then((l) => {
                        let result = [];
                        this.getAllGeoObjects(l.geoObjects).forEach((e) => {
                            let zone = this.getZoneContains(e.geometry._coordinates, this.zones);
                            if (predicate?predicate(e.geometry._coordinates):zone >= 0)
                                result.push({
                                    name: e.properties._data.name,
                                    output: this.getYandexOutputName(e.properties._data),
                                    coordinates:e.geometry._coordinates,
                                    zone:zone
                                });
                        });
                        if(promiseId == this.suggestsPromisesCount)
                            resolve(result);
                        else reject({ type:"cancel" })
                    });
                else{
                    axios
                        .get("/Api/searchAdress",{ params:{ q:value.toRussianChars().trimSeparators([",","."]).replace(/ул/ig,"улица").replace(/дом/ig,"") } })
                        .then(({ data:addresses })=>{
                            let result = [];
                            addresses.forEach((address)=>{
                                let name = this.getMtApiOutputName(address,"short");
                                name = name.replace(name[0],name[0].toUpperCase());
                                let outputText = this.getMtApiOutputName(address,"long");
                                result.push({
                                    name:name,
                                    output:outputText.replace(outputText[0],outputText[0].toUpperCase()).replace(/Ул\./ig,"ул."),
                                    coordinates:[address.longitude, address.latitude],
                                    zone:address.zone
                                })
                            })
                            if(promiseId == this.suggestsPromisesCount)
                                resolve(result);
                            else reject({ type:"cancel" })
                        })
                }
            } catch(e){ reject({ type:"error", error:e}) }
        });
    }
    /**
     * 
     * @param {Array} coordinates 
     * @param {Array} zones 
     * @returns Индекс зоны, в которой находится точка 
     */
    getZoneContains(coordinates, zones) {
        //Возвращает индекс зоны, в которой находится точка
        let zoneId = -1;
        for (let i = 0; i < zones.length; i++){
            zoneId = zones[i].geometry.contains(coordinates)
            ? i
            : zoneId;
            if(zoneId>=0)
                break;
        }
        return zoneId;
    }
    /**
     * 
     * @param {*} data формат: { name:String, output:String, coordinates:Array(2), zone:Number  }
     * @returns Promise
     */
    setMainGeoObject(data) { //обработка события при выборе варианта из подсказок(замена старой метки на новую)
        if (this.placemark != undefined)
            this.myMap.geoObjects.remove(this.placemark);
        this.placemark = this.createPlacemark(data.coordinates);
        this.placemark.properties.set("iconContent", data.name);
        this.updatePlacemark(this.placemark);
        this.myMap.setCenter(data.coordinates);
        this.addr = data.output;
        this.customerCoord = data.coordinates;
        localStorage.customerCoord = JSON.stringify(this.customerCoord);
        localStorage.addr = this.addr;
    }
    /**
     * 
     * @param {*} data В случае с Yandex Api: geoobject.properties._data.
     * @returns 
     */
    getYandexOutputName(data){
        let exceptItems = ["Украина"];
        let resArray = data.description.split(", ").filter(item=>!exceptItems.includes(item));
        resArray.push(data.name);
        return resArray.join(", ");
    }
    getMtApiOutputName({nameElements,addrElements},type = "long",reverse = false){
        let output = [];
        if(type == "long"){
            output = [
                addrElements.house_number?"дом "+addrElements.house_number:undefined,
                ((addrElements.road?"ул. "+addrElements.road.replace("улица",""):undefined) || (addrElements.square?"площадь "+addrElements.square.replace("площадь",""):undefined)),
                addrElements.neighbourhood,
                addrElements.landuse,
                addrElements.industrial,
                addrElements.borough,
                addrElements.subdistrict,
                addrElements.city,
                !addrElements.city?addrElements.municipality:undefined,
                addrElements.state,
                addrElements.country
            ].filter(item=>item != undefined).modify((item,index,set)=>set(item.trimStart().trimEnd()));
            return !reverse?output.join(", ",0,3):output.reverse().join(", ",output.length-3,3);
        } 
        else if(type == "short"){
            return nameElements.name_ru || nameElements.name || nameElements.name_uk || 
            (addrElements.house_number?"дом "+addrElements.house_number:"")+
            (addrElements.road?"ул. "+addrElements.road.replace("улица",""):"")+
            (addrElements.square?"площадь "+addrElements.square.replace("площадь",""):"") ||
            addrElements.neighbourhood || addrElements.landuse || addrElements.industrial ||  addrElements.borough ||
            addrElements.subdistrict || addrElements.city || addrElements.state || addrElements.country
        }
    }
    getAllGeoObjects(geoObjects) {
        let results = [];
        for (let i = 0; i < geoObjects.getLength(); i++)
            results.push(geoObjects.get(i));
        return results;
    }
    destroy(){
        this._eventor.removeAllListeners("placemarkDragend");
        this.myMap.destroy();
    }
}