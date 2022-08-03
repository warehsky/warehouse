Array.prototype.except = function except(array,firstCompareProperty=undefined,secondCompareProperty=undefined){
    let result = [];
    this.forEach((item)=>{
      let contains = false;
      array.forEach((item2)=>{
        if(firstCompareProperty && secondCompareProperty?(item2[secondCompareProperty]==item[firstCompareProperty]):item2==item)
          contains = true;
      });
      if(!contains)
        result.push(item);
    });
    return result;
}
Array.prototype.order = function(indexExp,order){
    let res = [];
    this.forEach((item)=>{ res[order.indexOf(indexExp(item))] = item });
    return res;
}
Array.getMatched = function getMatched(first,second,firstCompareProperty=undefined,secondCompareProperty=undefined){
    let result = [];
    first.forEach((item)=>{
        second.forEach((item2)=>{
        if(firstCompareProperty && secondCompareProperty?(item2[secondCompareProperty]==item[firstCompareProperty]):item2==item)
            result.push({first:item,second:item2});
      });
    });
    return result;
}
Array.prototype.getGroupProperty = function getGroupProperty(propertyName){
    if(!propertyName)
        return [];
    let result = [];
    this.forEach((item)=>{
        if(!item[propertyName])
            return [];
        result.push(item[propertyName]);
    });
    return result;
}
Array.prototype.getItemBy = function getItemBy(conditionCallback){
    let result = null;
    for(let i = 0;i<this.length; i++)
        if(conditionCallback(this[i]))
            return result = this[i];
    return null;
}
Array.prototype.remove = function(...items){
    items.forEach((item)=>{ this.splice(this.indexOf(item),1) });
    return this;
}
Array.prototype.contains = function(callback){
    for(let i=0;i<this.length;i++){
        if(callback(this[i]))
            return true;
    }
    return false;
}
Array.prototype.partmap = function(callback){
    let res = [];
    for(let i=0;i<this.length;i++){
        let newitem = callback(this[i]);
        if(newitem) res.push(newitem);
    }
    return res;
}
Array.prototype.join = function(separator=",",indexFrom=0,count){
    if(indexFrom>this.length) indexFrom = this.length;
    else if(indexFrom<0) indexFrom = 0;
    let to = (indexFrom+count<=this.length?indexFrom+count:0) || this.length;
    let result = "";
    for(let i = indexFrom; i<to;i++){ result += (this[i]==null || this[i]==undefined?"":this[i])+(i+1<to?separator:""); }
    return result;
}
Array.prototype.modify = function(callback,predicate){
    for(let i = 0; i<this.length;i++){
        let item = this[i]
        let set = (item)=>{ this[i]=item; }
        if(!predicate || predicate(item,i,set))
            callback(item,i,set);
    }
    return this;
}
Array.prototype.indexOfBy = function indexOfBy(conditionCallback){
    for(let i = 0; i < this.length; i++)
        if(conditionCallback(this[i]))
            return i;
    return -1;
}
String.prototype.trimSeparators = function(separators){
    let res = this;
        for(let i = 0;i<separators.length;i++) res = res.replaceAll(separators[i],"");
    return res;
}
String.prototype.toRussianChars = function()
{
    var s = ["й","ц","у","к","е","н","г","ш","щ","з","х","ъ","ф","ы","в","а","п","р","о","л","д","ж","э","я","ч","с","м","и","т","ь"];
    var r = ["q","w","e","r","t","y","u","i","o","p","\\[","\\]","a","s","d","f","g","h","j","k","l",";","'","z","x","c","v","b","n","m"];
    s = [...s,"Х","Ъ","Ж","Э"]
    r = [...r,...r.partmap((item)=>{
        switch(item){
            case "\\[":return "\\{" //Х
            case "\\]":return "\\}" //Ъ
            case ";":return ":"     //Ж
            case "'":return "\""    //Э
            default: return;
        }
    })]
    let res = this;
    for (var i = 0; i < r.length; i++)
    {
        var reg = new RegExp(r[i], 'mig');
        res = res.replace(reg, function (a) {
            return a == a.toUpperCase() ? s[i].toUpperCase() :s[i] ;
        });
    }
    return res;
},
String.prototype.multiply = function(value){
    let result = ""
    for(let i=0;i<value;i++) result+=this;
    return result;
}
Date.day = 86400000;
Date.prototype.toShortDateString = function(){ return this.toISOString().split("T")[0] }
String.prototype.insert = function(value="",after=0){
    return this.slice(0, after) + value + this.slice(after);
}
console.push=function(str,...args){
    let result = str;
    if(args.length==0){
        let colors = [];
        let offset = 0;
        let search = str.matchAll(/#\w{6}/g);
        while(true){
            let res = search.next();
            if(res.done)
                break;
            result = result.insert("%c",offset+res.value.index+7).insert("%c",offset+res.value.index)
            colors.push(res.value[0]);
            colors.push("#FFFFFF");
            offset+=4;
        }
        console.log(result,...colors.map(item=>"color:"+item));
    }
    else console.log(str,...args);
}
/**
 * Форматирует объект object по объекту defaults, где defaults выступает, как объект необходимого формата со значаниями по умолчанию
 */
Object.map = function(object={},defaults={},defaultCondition){
    if(!defaultCondition) 
        defaultCondition = (entrie)=>entrie[1]==null||entrie[1]==undefined;
    if(typeof defaultCondition != 'function')
        throw new Error("defaultCondition must to be function");
    if(Array.isArray(defaults))
        return Object.fromEntries(defaults.partmap((item)=>[item,object[item]]));
    return Object.fromEntries(Object.keys(defaults).partmap((key)=>{
        return [key,defaultCondition([key,object[key]])?defaults[key]:object[key]]
    }));
}
Array.prototype.isItems = function(items){
    for(let i = 0; i<items.length; i++){
        let item = items[i];
        if(!this.includes(item)) return false;
    }
    return true;
}