export default class Message{
    /**
     * 
     * @param {Number} id 
     * @param {*} author 
     * @param {String} text 
     * @param {String} status 
     * @param {Array} suggestions 
     * @param {String} datetime 
     */
    constructor(id,author,text,status,suggestions,datetime){
        this.id = id;
        this.author = author;
        this.text = text;
        this.status = status;
        this.suggestions = suggestions;
        let dateTime = new Date(datetime?datetime:new Date(Date.now()));
        this.time = dateTime.getTimeOutputString();
        this.date = dateTime.getDateOutputString();
    }
  }