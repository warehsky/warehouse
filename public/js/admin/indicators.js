class IndicatorPanel{
    constructor(className="i-panel",value="panel",link=null){
        this.element = document.createElement("div");
        this.element.className = className;
        this.value = value;
        if(link){
            this.linkElement = document.createElement('a');
            this.linkElement.href = link;
            this.linkElement.innerHTML = value;
            this.element.appendChild(this.linkElement);
        }
        else
            this.element.innerHTML = value;
    }
    setValue(value){
        this.value = value;
        if(this.linkElement)
            this.linkElement.innerHTML = value;
        else this.element.innerHTML = value;
    }
}
class Indicator{
    constructor(indicatorSelector,label,value,link) {
        this.indicatorRoot = $(indicatorSelector)[0];
        this.leftPanel = new IndicatorPanel("i-panel left", label, link);
        this.rightPanel = new IndicatorPanel("i-panel right", value);
        this.indicatorRoot.appendChild(this.leftPanel.element);
        this.indicatorRoot.appendChild(this.rightPanel.element);
        window['indicator'+label] = this;
        Indicator._map.set(indicatorSelector, this);
    }
    static getIndicator(indicatorSelector){
        return Indicator._map.get(indicatorSelector);
    }
    static getIndicatorSelectors(indicatorSelector){
        return Indicator._map.keys();
    }
    getValue(){
        return this.rightPanel.value;
    }
    setValue(value){
        this.rightPanel.setValue(value);
        this.indicatorRoot.className = this.rightPanel.value > 0?"indicator indicate":"indicator";
    }
}
Indicator._map = new Map();