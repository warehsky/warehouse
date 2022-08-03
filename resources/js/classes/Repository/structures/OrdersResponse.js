import Order from "../../../components/orders/order.js";

export default class OrdersResponse {
    constructor(props){
        props = props || {};
        this.orders = this.initOrders(props.orders);
        this.statuses = props.statuses || [];
        this.payments = props.payments || [];
    }
    /**
     * @returns {Array<Order>}
     */
    initOrders(orders){
        return orders.map(o=>new Order().set(o)) || [];
    }
}