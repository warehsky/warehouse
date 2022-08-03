var CssClassController = {
    addClass(element,className){
        if (!element.className.includes(className))
          element.className += (element.className[element.className.length-1]==" "?"":" ") + className;
    },
    removeClass(element,className){
        if (!element.className.includes(className))
          return;
        let wordStart = element.className.indexOf(className);
        let newWordStart = wordStart;
        if (wordStart>0)
        for(let i=wordStart-1;i>0;i--){
          if(element.className[i]==" ")
            break;
            newWordStart=i;
        }
        if(wordStart!=newWordStart)
          return;
        else
        element.className = element.className.replace(className,"");
    },
    replaceClass(element,first,second){
        if(!element.className.includes(first))
          return false;
        this.removeClass(element,first);
        this.addClass(element,second);
        return true;
    }
}
export default CssClassController;