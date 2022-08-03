export default class units{
    static get(value){
        if(typeof value != 'string')
            return { value:value, unit:typeof value }
        let val = parseFloat(value);
        return { value:val, unit:value.replace(val,"") }
    }
}