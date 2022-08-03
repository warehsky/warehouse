export default class PolygonSettings{
    static getDefault(type,data={},watched = this.options){
        let sots = type=='sot';
        let result = {
            geometry:data?.geometry||null,
            options:Object.map(data,{
                description:data?.description||'',
                fillColor:sots?"#1A78EE":"#74a267",
                fillOpacity:sots?0.1:0.6,
                strokeColor:"#1A78EE",
                strokeOpacity:"1",
                strokeWidth: 3,
                deleted: false
            }),
            properties:Object.map(data, {
                id:-1,
                type,
                state:["default"]
            }),
            data
        }
        watched.forEach((opt)=>{ result.properties['old_'+opt] = result.options[opt] || (opt=="geometry"?JSON.stringify(data?.[opt]):data?.[opt]); });
        return result;
    }
    static isChanged(settings){
        let props = Object.entries(settings.properties)
        for(let i = 0; i<props.length; i++){
            let prop = props[i];
            let oldkey = prop[0];
            let key = oldkey.slice(4);
            if(key == "geometry" && JSON.stringify(settings.geometry) != settings.properties['old_geometry'])
                return true;
            if(oldkey.search("old_")!=0 || !settings.options.hasOwnProperty(key))  continue;
            if(settings.options[key] != prop[1])
                return true;
        }
        return false;
    }
}
PolygonSettings.options = ['geometry','description','fillColor','fillOpacity','strokeColor','strokeOpacity','strokeWidth'];
// PolygonSettings.properties = ['id','type','state','old-geometry','old-description'];