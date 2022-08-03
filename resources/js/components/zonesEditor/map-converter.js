export default class MapConverter{
    /**
     * @param { [x:Number,y:Number] } point
     * @returns { [x:Number,y:Number] }
     */
    static getPageOffset(map, point){
        let projection = map.options.get('projection');
        let r = map.converter.globalToPage(projection.toGlobalPixels(point,map.getZoom()))
        return r;
    }
    /**
     * @param { [x:Number,y:Number] } point
     * @returns { [x:Number,y:Number] }
     */
    static getMapOffset(map, point){
        let projection = map.options.get('projection');
        let r = projection.fromGlobalPixels(map.converter.pageToGlobal(point),map.getZoom());
        return r;
    }
}