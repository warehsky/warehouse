var fixedElementsOffsetMapGlobal = new Map();
export function fixedElementsOffsetMap(){ return fixedElementsOffsetMapGlobal };
export default class Fixator{
    constructor(){
        this.unfreezeObj = null;
        this.unfixObj = null;
        this.display = ""
        this.fixed = false;
        this.fixedElementsOffsetMap = new Map();
    }
    freeze(elements,fixMethod = 'absolute', end=true){
        for (var i = (end?elements.length-1:0); (end?i>=0:i<elements.length); (end?i--:i++)) {
            let position = this.getRelativePosition(elements[i])
            elements[i].style.position = fixMethod;
            elements[i].style.top = position.top+'px';
            elements[i].style.left = position.left+'px';
        }
    }
    freezeByParent(parent,elements,end=true){
        let rect = parent.getBoundingClientRect();
        let styles = window.getComputedStyle(parent);
        this.unfreezeObj = {
            width:parent.style.width,
            height:styles.height
        };
        parent.style.width = rect.width+'px';
        parent.style.height = rect.height+'px';
        this.freeze(elements,end);
        this.fixed = true;
    }
    unfreeze(elements, end=false){
        for (var i = (end?elements.length-1:0); (end?i>=0:i<elements.length); (end?i--:i++)) {
            elements[i].style.position = 'static';
            elements[i].style.top = undefined;
            elements[i].style.left = undefined;
        }	
    }
    unfreezeByParent(parent, elements, end=false){
        this.unfreeze(elements, end);
        if(!this.fixed)
            return;
        parent.style.width = this.unfreezeObj.width;
        parent.style.height = this.unfreezeObj.height;
    }
    getRelativePosition(element){
        let style = window.getComputedStyle(element);
        let position = {
            top: element.getBoundingClientRect().top + document.documentElement.scrollTop - parseFloat(style.marginTop),
            left: element.getBoundingClientRect().left + document.documentElement.scrollLeft - parseFloat(style.marginLeft)
        }
        return position
    }
    displayElements(elements,hide=false){
        for (let i = 0; i < elements.length; i++) {
            if (hide)
                elements[i].style.display = "none";
            else
                elements[i].style.display = "flex";
        }
    }
    _saveStyles(fixableElement,parent){
        this.unfixObj = {
            fixableElementStyle:fixableElement.style,
            parentStyle:parent.style
        }
    }
    setTopOffset(fixableElement,value){
        if(value=='auto'){
            value = 0;
            this.fixedElementsOffsetMap.forEach((elValue,key)=>{
                if(key!=fixableElement)
                    value += elValue;
            });
        }
        this.fixedElementsOffsetMap.set(fixableElement,value);
        fixedElementsOffsetMapGlobal.set(fixableElement,value);
    }
    fixOnScreen(fixableElement,parent){
        this.fixedElementsOffsetMap.set(fixableElement,0);
        fixedElementsOffsetMapGlobal.set(fixableElement,0);
        this._saveStyles(fixableElement,parent);
        let fixator = this;
        //scroll
        function enable(){
            let rect = fixableElement.getBoundingClientRect();
            parent.style.height = rect.height;
            parent.style.width = rect.width;
            parent.style.position = 'static';
            fixableElement.style.left = rect.left;
            fixableElement.style.width = rect.width;
            fixableElement.style.position = 'fixed';
            fixableElement.style.top = fixator.fixedElementsOffsetMap.get(fixableElement);
            fixableElement.style.zIndex = 1001;
            fixableElement.style.background = 'white';
        }
        function disable(){
            fixableElement.style = fixator.unfixObj.fixableElementStyle;
            parent.style = fixator.unfixObj.parentStyle;
        }
        function onScroll(){
            if(window.pageYOffset+fixator.fixedElementsOffsetMap.get(fixableElement)>parent.offsetTop)
            	enable();
            else
            	disable();
        }
        function update(){
            disable();
			if(window.pageYOffset+fixator.fixedElementsOffsetMap.get(fixableElement)>parent.offsetTop)
				enable();
        }
        window.addEventListener('scroll',onScroll);
        window.addEventListener('resize',update);
        //scroll end

        return this.unfixObj;
    }
}