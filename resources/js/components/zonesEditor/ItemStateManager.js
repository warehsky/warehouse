import PolygonSettings from "./PolygonSettings";

export default class ItemStateManager{
    static get(item){
        return this.states.sort((s,s1)=>s.priority-s1.priority).find((st)=>item.properties.get("state").includes(st.name))||this.states[0];
    }
    static set(item,state,value){
        let properties = item.properties;
        let currentState = item.properties.get("state");
        let sindex = currentState.includes(state);
        if(sindex && value || !sindex && !value)
            return;
        if(!value) currentState.remove(state);
        else currentState.push(state);
        properties.set("state",currentState);
    }
    static get states(){  
        return [//расставлено по приоритету на убывание
            { name:"default", color:"transparent", title:"", priority: 3 },
            { name:"deleted", color:"#d63139", title:"Будет удалено при выгрузке", priority: 0 },
            { name:"edited", color:"#0C7D9D", title:"Изменено", priority: 2 },
            { name:"new", color:"#099818", title:"Добавлено", priority: 1 },
        ]
    }
    static getChanges(item){
        return {
            geometry:PolygonSettings.isChanged({ 
				geometry:item.geometry.getCoordinates(),
				options:item.options.getAll(),
				properties:item.properties.getAll() 
			}),
            
        }
    }
}