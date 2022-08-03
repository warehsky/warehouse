class Globals {
    static api_token = "";
};
const PropNamePrefix = "_pr-";
class UserPermissions{
    constructor(){ return UserPermissions; }
    static clear(){
        for(var property in UserPermissions) delete UserPermissions[PropNamePrefix+property];
    }
    /**
     * Adds permitions from entries
     * @param {Iterable} entries Array, Iterable or MapIterator
     * 
     */
    static fromEntries(entries){
        if(!entries[Symbol.iterator] || typeof entries[Symbol.iterator] !== 'function') return;
        for(var entrie of entries){
            UserPermissions[PropNamePrefix+entrie[0]]=entrie[1];
        }
    }
    /**
     * 
     * @param {String} name
     * @returns {Boolean}
     */
    static can(name){
        return PropNamePrefix+name in UserPermissions;
    }
    /**
     * @param {Object | Map | Array} data
     */
    static init(data){
        let invalidTypeError = "invalid type of property data. Expected Object | Map | Array.";
        if(!data) throw new Error(invalidTypeError);
        switch(data.constructor.name){
            case('Object'):data = Object.entries(data); break;
            case('Map'):data = data.entries(); break;
            case('Array'):data = data.map(item=>[item,true]); break;
            default: throw new Error(invalidTypeError);
        }
        return UserPermissions.fromEntries(data);
    }
}