<template>
  <div class="order-edit-view">
    <div class="order-title">
      Редактирование заказа
      <div class="view-settings">
        <div class="settings-louncher" @click="settingsOpened = !settingsOpened">Настройки</div>
        <float-panel v-if="settingsOpened" :opened="true" style="z-index:1;">
          <div class="setting">
            <label for="markDelivery">Подсвечивать пункт "Доставка" в разделе "Итого"</label><input type="checkbox" id="markDelivery" v-model="markDelivery" />
          </div>
          <div class="setting">
            <label for="noteLoadingMode">Режим загрузки примечания</label>
            <select id="noteLoadingMode" v-model="noteLoadingMode">
              <option value="add">Добавление</option>
              <option value="replace">Замена</option>
            </select>
          </div>
        </float-panel>
      </div>
    </div>
    <div class="edit-context">
      <div class="properties">
        <h2>
          <span v-if="order.id">Заказ #{{order.id}}</span>
          <span v-else>Новый заказ</span>
        </h2>
        <div ref="properties">
          <editor-property :disabled="limitedMode" iclass="checkout_text" name="Имя" type="text" v-model="order.name" />
          <editor-property :disabled="limitedMode" name="Телефон заказчика">
            <custom-select class="country-select"
              selectclass="checkout_text additional-select"
              panelclass="country-select-panel"
              optionclass="custom-option"
              :panelStyle="{ height:`${phonePrefixes.length*125}%` }"
              :items="phonePrefixes"
              priority="prefix"
              v-model="order.phonePrefix"
              :disabled="limitedMode"
              @init="updateMask($event.type,false)"
              @change="updateMask($event.type)">
              <template #default="{ item, isOption }">
                <span v-if="item.type==1">{{item.prefix}}</span>
                <div :class="['cso-title',{ option:isOption }]" v-else-if="isOption || item.type==2">
                  <span v-if="isOption">{{item.title}}</span>
                  <img width="20" v-for="(image,index) in item.images" :key="index" :src="image"/>
                </div>
              </template>
            </custom-select>
            <input ref="phone"
              class="checkout_text"
              type="text"
              v-mask="order.phoneMask"
              v-model="order.phone"
              placeholder="введите телефон"
              @change="onPhoneChange">
          </editor-property>
          <editor-property :disabled="limitedMode" name="Телефон получателя">
            <select class="checkout_text additional-select" v-model="order.phoneConsigneePrefix">
              <option v-for="prefix in prefixes" :key="prefix">{{prefix}}</option>
            </select>
            <input
              class="checkout_text"
              type="text"
              v-model="order.phoneConsignee"
              v-mask="'(###) ###-##-##'"
              placeholder="введите телефон">
          </editor-property>
          <editor-property :disabled="limitedMode" name="Адрес">
            <div style="height:min-content">
              <span v-if="isInvalidAddress" style="color:red;">[Адрес вне доступной зоны или соты]</span>
              <address-input ref="mapComponent"
                inputClass="checkout_text long-input"
                :zones="zones"
                :sots="sots"
                :map="map"
                @error="error($event)"
                @change="order.update(); updateGeoInfo();">
              </address-input>
              <group-box>
                <legend>Уточнение адреса</legend>
                <div style="display:grid;">
                  <editor-property iclass="checkout_text" name="Дом" v-model="order.address.houseReal"/>
                </div>
              </group-box>
            </div>
          </editor-property>
          <editor-property :disabled="limitedMode" name="Примечание" class="note-prop">
            <div style="display:flex;" v-if="profile">
              <button class="checkout_text" :disabled="!profile.noteUser || (profile.noteUser == order.note && noteLoadingMode=='replace')" @click="loadNote">Загрузить</button>
              <button class="checkout_text" :disabled="profile.noteUser == order.note" @click="setProfile({ noteUser:order.note })">Сохранить</button>
            </div>
            <textarea class="checkout_text long-input" v-model="order.note"></textarea>
          </editor-property>
          <editor-property :disabled="limitedMode" name="Акция">
            <associative-select class="checkout_text" v-model="order.gift" :options="gifts" style="max-width: 260px;" @select="order.giftTitle = $event.option.title">
              <option v-for="(gift,index) in gifts" :key="index" :value="gift.value">{{gift.title}}</option>
            </associative-select>
          </editor-property>
          <group-box title="По умолчанию отображаются только доступные по времени">
            <legend>Период доставки</legend>
            <editor-property :disabled="limitedMode"
              :iclass="['checkout_text',{ 'invalid-selection':!showAllWaves && availableWaves.length<1 && order.deliveryDate == today.toShortDateString() }]"
              name="Дата"
              type="date"
              v-model="order.deliveryDate"
              :min="today.toShortDateString()"/>
            <editor-property :disabled="limitedMode" name="Временная волна">
              <div>
                <div style="text-align: right;">
                  <input id="showAllWaves" :disabled="isInvalidAddress || limitedMode" type="checkbox" style="height:auto" v-model="showAllWaves">
                  <label for="showAllWaves" class="center-label">Показать все волны</label>
                  <select :disabled="isInvalidAddress || !deliveryWaves.length || limitedMode" class="checkout_text" v-model="order.waveId" style="min-width:165px;">
                    <option v-for="wave,index in deliveryWaves" :key="index" :value="wave.id" :class="['deliveryWave',{ disabled:wave.disabled }]">{{wave.value}}</option>
                  </select>
                </div>
                <div>
                  <div v-if="isInvalidAddress" style="color:red">[Адресс не выбран или некорректен]</div>
                  <div v-else-if="!deliveryWaves.length" style="color:red">[Нет волн на выбранную дату или зону]</div>
                  <div v-else-if="!orderWave.wave" style="color:red">[Не удалось идентифицировать выбранную волну (id:{{orderWave.id}})]</div>
                  <div v-else-if="!orderWave.available" style="color:red;">[Выбранная волна {{orderWave.wave.value}} недоступна]</div>
                </div>
              </div>
            </editor-property>
          </group-box>
          <group-box v-show="discountMethods.length>1">
            <legend>Персональная скидка</legend>
            <editor-property :disabled="limitedMode" name="Cпособ предоставления скидки">
              <select
                ref="personDiscountMethod"
                class="checkout_text"
                v-model="order.discountMethod"
                @input="onChangeMethod"
                :items="discountMethods">
                <option v-for="discountMethod in discountMethods" :key="discountMethod.id" :value="discountMethod.id">
                  {{discountMethod.value}}
                </option>
              </select>
            </editor-property>
            <editor-property :disabled="limitedMode" name="Дисконтная карта" v-if="discountCards.length>0 && order.discountMethod==discountMethodsTypes.discountCard">
              <select
                ref="couponSelect"
                class="checkout_text"
                v-model="order.promocode">
                <!-- intType == 3 - сертификаты -->
                <option :value="null">Не выбрана</option>
                <option v-for="discountCard in discountCards" :key="discountCard.id" :value="discountCard.data.promocode">
                  -{{discountCard.data.discount}}% до {{discountCard.date}}
                </option>
              </select>
            </editor-property>
            <editor-property :disabled="limitedMode" name="Сертификат" v-if="certificates.length>0 && order.discountMethod==discountMethodsTypes.certificate">
              <select
                ref="couponSelect"
                class="checkout_text"
                v-model="order.promocode">
                <!-- intType == 3 - сертификаты -->
                <option :value="null">Не выбран</option>
                <option v-for="certificate in certificates" :key="certificate.id" :value="certificate.data.promocode">
                  -{{certificate.data.discount | currencydecimal(true) }} до {{certificate.date}}
                </option>
              </select>
            </editor-property>
            <editor-property :disabled="limitedMode" name="Промокод" v-show="order.discountMethod==discountMethodsTypes.promocode">
              <promocode-input ref="promoInput" v-model="order.promocode" :promocodes="defaultPromocodes" :phone="order.phonePrefix+order.phone"></promocode-input>
            </editor-property>
            <div v-if="order.discountMethod==discountMethodsTypes.bonus">
              <div class="bonus-info">
                <div class="bonus-info-unite">Всего:{{order.bonusUser}}</div>
                <div class="bonus-info-unite">Начислено:{{order.bonus}}</div>
                <div class="bonus-info-unite">Доступно:{{availableBonus}}</div>
              </div>
              <editor-property
                iclass="checkout_text"
                name="Использовать:"
                type="number"
                min="0"
                :max="availableBonus"
                :disabled="!order.bonusUser || limitedMode"
                v-model="order.bonus_pay"
                @change="updateBonusPay" />
            </div>
          </group-box>

          <editor-property name="Способ оплаты">
            <select class="checkout_text" v-model="order.payment" :disabled="order.status == 2 || order.status == 6" @change="update()">
              <option v-for="payment in payments" :key="payment.id" :value="payment.id">
                {{payment.title}}
              </option>
            </select>
          </editor-property>
          <editor-property :disabled="limitedMode" name="Льгота">
            <select class="checkout_text" v-model="order.pension" @change="update()">
              <option v-for="lgotItem in lgots" :key="lgotItem.id" :value="lgotItem.id">{{lgotItem.title}}</option>
            </select>
          </editor-property>
          <editor-property name="Статус" :disabled="baseOrderStatus == 4">
            <select class="checkout_text" v-model="order.status">
              <option v-for="statusItem in statuses" :key="statusItem.id"
                :value="statusItem.id"
                :disabled="!availableStatuses.includes(statusItem)">
                {{statusItem.title}}
              </option>
            </select>
          </editor-property>
          <group-box>
            <legend>Итого</legend>
            <div>Сумма(без учета скидки по товарам): {{ getTotal(false) | currencydecimal }}</div>
            <div>
              <span>Сумма(по товарам): {{ goodsTotal | currencydecimal}}</span>
              <span v-if="showMinSum"
                style="color:red;">
                Минимум: {{ $getCurrencyPrice(zone.conditions.limit_min, course) | currencydecimal}}
              </span>
            </div>
            <div :class="{ marked:currencyDeliveryCost>0 && markDelivery }">Доставка: {{currencyDeliveryCost | currencydecimal}}
              <span v-if="isInvalidAddress" style="color:red;">[Адресс не выбран или некорректен]</span>
            </div>
            <div>Персональная скидка: {{personalDiscount | currencydecimal}}</div>
            <div>Общая скидка: {{discount | currencydecimal}}</div>
            <div><b>К оплате:</b> {{ toPayOutput | currencydecimal}}</div>
          </group-box>
          <group-box>
            <legend>Действия</legend>
            <div v-if="order.status == 6" class="restore-button" @click="restoreOrder(order)">
              <circle-loading v-if="order.refresh && order.refresh == 1" :radius="36" :ringWeight="16" style="padding: 5px 0px;"></circle-loading>
              <span v-show="!order.refresh">Восстановить заказ</span>
              <span v-show="order.refresh && order.refresh == -1">Ошибка!<br>Повторить восстановление</span>
            </div>
            <div>
              <button class="btn btn-active" :disabled="state == states.loading" @click="cancelEdit">Отмена</button>
              <async-button class="btn btn-active" @click="save" @wait="state = states.loading" @error="state = states.error">
                <template #default>Сохранить</template>
                <template #wait>Сохранение...</template>
              </async-button>
              <span v-if="state==states.error" style="color:red">{{outputError}}</span>
            </div>
          </group-box>
        </div>
      </div>

      <div v-if="!limitedMode" class="property-additional-panel">
        <h2>Товары</h2>
        <div class="properties">
          <editor-property :disabled="limitedMode" name="Валюта"><currency-switch/></editor-property>
        </div>
        <goods-edit-panel
          :items="order.items"
          :shop_url="shop_url"
          :errorRate="errorRate"
          :course="course"
          @count-edited="update"
          @goods-changed="update"
          @item-deleted="update">
          <template #before>
            <button @click="updateItemsWeights">Получить кол-во со склада</button>
			      <span v-if="updatedMessage" style="color:green">{{updatedMessage}}</span>
          </template>
        </goods-edit-panel>
      </div>
    </div>
  </div>
</template>

<script>
import editorProperty from '../../editor-property.vue'
import AddressInput from '../../address-input.vue';
import AssociativeSelect from '../../../../UI/inputs/associative-select.vue';
import MtMap from '../../../../../yandex-map.js';
import Order from "../../../order.js";
import PromocodeInput from '../../promocode-input.vue';
import FloatPanel from '../../../../UI/panels/float-panel.vue';
import GoodsEditPanel from './goods-edit-panel/goods-edit-panel.vue';
import CustomSelect from  '../../../../UI/inputs/custom-select.vue';
import CurrencySwitch from '../../currency-switch.vue';
import ItemsCollectionMixin from '../../../mixins/items-collection.js';
import EditorPanelMixin from '../editor-panel.js';
import Repository from '../../../../../classes/Repository/Repository';
import UserCoupon from '../../../../../classes/Repository/structures/UserCoupon';

export default {
  mixins:[EditorPanelMixin, ItemsCollectionMixin],
  components: { 
    editorProperty,
    AddressInput,
    AssociativeSelect,
    PromocodeInput,
    FloatPanel,
    GoodsEditPanel,
    CustomSelect,
    CurrencySwitch
  },
  name:"order-edit-view",
  props:{
    waves:Array,
    availableWaves:Array,
    payments:Array,
    statuses:Array,
    limitedMode:Boolean,
    phonePrefixes:{
      type:Array,
      default:[]
    }
  },
  data(){
    return{
      prefixes:["+38","+7"],
      lgots:[{id:0, title:'нет'}, {id:1, title:'пенсионер'}],
      showAllWaves:this.limitedMode,
      today:new Date(),
      map:this.createMap(),
      state:1,
      states:{
        error:-1,
        loading:0,
        loaded:1
      },
      gifts:[],
      panelHeight:0,
      updatedMessage:null,
      markDelivery:localStorage.markDelivery?JSON.parse(localStorage.markDelivery):false,
      noteLoadingMode:localStorage.noteLoadingMode || "add",
      baseOrderStatus:this.order.status,
    }
  },
  computed:{
    toPayOutput(){
      const sum = this.getTotalWithDeliveryCost(true);
      return Math.round(sum - Math.min(sum,this.personalDiscount))
    },
    discountCards(){ return this.coupons.filter(c=>c.data.intType != 3) },
    certificates(){ return this.coupons.filter(c=>c.data.intType == 3) },
    defaultDiscount(){
      if(this.order.discount && (!this.order.discount.webUserId || (this.profile && this.order.discount.webUserId == this.profile.id))){
        let discountData = this.order.discount;
        return  new UserCoupon({
          discount:discountData.discount,
          disposable:discountData.type!=1,
          expiration:discountData.expiration,
          expirationtm:new Date(discountData.expiration).getTime(),
          id:discountData.id,
          intType:discountData.type,
          promocode:discountData.title,
          type:"использованная",
          webUserId:discountData.webUserId
        });
      }
      return null;
    },
    items(){
      return this.order.items;
    },
    showMinSum(){
			return this.map.ymapsReady && this.zone && this.$getCurrencyPrice(this.zone.conditions.limit_min, this.course) > (this.goodsTotal - this.getRoundDiscount(this.goodsTotal))
    },
    orderChanged(){
      return !Order.isEqual({...this.order}, this.orderDraft)
    },
    availableBonus(){
      return Math.min(Math.round(this.goodsTotal/100*this.order.proc),this.order.bonusUser) || 0;
    },
    sot(){
      if(this.isInvalidAddress = !this.map.sots)
        return null;
      let index = this.map.getZoneContains([this.order.lng, this.order.lat],this.map.sots);
      if(this.isInvalidAddress = index == -1){
        console.warn(new Error("Invalid Address"));
        return null;
      }
      let id = this.map.sots[index].properties.get('id')
      return this.sots.find((sot)=>sot.properties.id == id);
    },
    zone(){
      if(this.isInvalidAddress = !this.map.zones)
        return null;
      let index = this.map.getZoneContains([this.order.lng, this.order.lat],this.map.zones);
      if(this.isInvalidAddress = index == -1){
        console.warn(new Error("Invalid Address"));
        return null;
      }
      let id = this.map.zones[index].properties.get('id')
      return this.zones.find((zone)=>zone.properties.id == id);
    },
    deliveryWaves(){
      let waves = (!this.showAllWaves && this.order.deliveryDate==this.today.toShortDateString()?this.availableWaves:this.waves)
      return [...this.zone?waves.filter(wave=>wave.zoneId==this.zone.properties.id):waves];
    },
    orderWave(){
      return {
        id:this.order.waveId,
        wave:this.waves.find(w=>w.id==this.order.waveId),
        available:this.deliveryWaves.contains(w=>w.id==this.order.waveId)
      };
    },
    availableStatuses(){
      if(this.baseOrderStatus == 4) return [];//если отгружен, то изменить статус нельзя
      const limited = this.limitedMode;
      return this.statuses.filter(statusItem=>{
        const sended1c = statusItem.id == 2;
        const notHandled = statusItem.id == 1;
        const handled1c = statusItem.id == 5;
        const sendedToPay = statusItem.id == 6;
        const cashless = this.order.payment == 2;
        if(limited && statusItem.canedit) return (sendedToPay && cashless) || sended1c || handled1c || notHandled;
        return statusItem.canedit && !(cashless && sended1c) && !(!cashless && sendedToPay);
      })
    }
  },
  watch:{
    availableBonus:function(){
      this.updateBonusPay();
    },
    markDelivery:function(){
      localStorage.markDelivery = this.markDelivery;
    },
    noteLoadingMode:function(){
      localStorage.noteLoadingMode = this.noteLoadingMode;
    }
  },
  updated(){
    if(this.orderChanged)
      this.saveOrderDraft(true);
  },
  beforeMount(){
    if(this.defaultDiscount && !this.defaultDiscount.webUserId)
      this.defaultPromocodes = [this.defaultDiscount];
  },
  mounted(){
    this.getGifts(this.goodsTotal).then((gifts)=>this.gifts = gifts).then(()=>{ this.saveOrderDraft(); });
    this.$watch(()=>this.goodsTotal,()=>{ this.getGifts(this.goodsTotal).then((gifts)=>this.gifts = gifts); });
    this.updateDefaults();
    this.observer = new ResizeObserver(()=>{
      this.panelHeight = this.$refs.properties?.getBoundingClientRect().height;
    });
    this.observer.observe(this.$refs.properties);
    this.updateItemsWeights();
    this.updateProfile();
  },
  beforeDestroy(){
    this.observer.disconnect();
  },
  methods:{
    /**
     * Обнуляет выбранный промокод. При этом, если есть defaultDiscount, изпользует его в качестве значения.
     */
    onChangeMethod(e){
      if(e.target.value==this.getDefaultDiscountMethod())
        this.order.promocode = this.defaultDiscount.promocode;
      else this.order.promocode = null;
    },
    async onPhoneChange(){
      await this.updateProfile();
      await this.updateDiscountMethods();
    },
    updateMask(phoneType, clear = true){
      let oldMask = this.order.phoneMask;
      this.order.phoneMask = this.getPhoneMask(phoneType);
      if(clear && this.order.phoneMask != oldMask)
        this.order.phone = "";
    },
    getPhoneMask(phoneType=1){
      return phoneType==1?'(###) ###-##-##':'+###############';
    },
    updateDefaults(){
      this.order.phonePrefix = this.order.phonePrefix || this.phonePrefixes[0].prefix;
      this.order.phoneConsigneePrefix = this.order.phoneConsigneePrefix || this.prefixes[0];
      this.order.deliveryDate = this.order.deliveryDate || new Date().toJSON().split("T")[0];
      this.order.waveId = Number(this.order.waveId)!=NaN?this.order.waveId:(this.deliveryWaves[0]?.id||this.waves[0]?.id);
    },
    update(){
      this.order.update();
      this.order.deliveryCost = this.getDeliveryCost();
    },
    updateBonusPay(){
      this.order.bonus_pay = Math.max(0,Math.min(this.order.bonus_pay,this.availableBonus));
    },
    updateItemsWeights(){
      this.updatedMessage = null;
      axios.get("/Api/getOrderItems", { headers:{ "X-Access-Token":Globals.api_token }, params:{ orderId:this.order.id } })
        .then(({data})=>{
          if(data.error) throw new Error(data.error);
          data.forEach(newitem=>{
            let item = this.order.items.find(i=>i.itemId==newitem.itemId);
            if(!item || !newitem.workerId || !newitem.weightId) return;
            if(item.quantity != newitem.quantity_warehouse)
              item.quantityOld = item.quantity;
            item.quantity = newitem.quantity_warehouse;
            item.workerId = newitem.workerId;
            item.scaned = item.workerId>0;
          });
          this.updatedMessage = "Выполнено";
          setTimeout(()=>{ this.updatedMessage = null; },3000);
        })
    },
    setMapData(coords,addr){
      if(!coords[0] || !coords[1] || !addr){
        coords = null;
        addr  = null;
      }
      this.$refs.mapComponent.reload(coords,addr);
    },
    updateGeoInfo(){
      this.order.addr = this.map.addr;
      this.order.lat = this.map.customerCoord[1];
      this.order.lng = this.map.customerCoord[0];
      //после обновления координат объект zone обновиться только на следующий тик
      this.$nextTick(()=>{
        this.order.deliveryZone = Number(this.zone?this.zone.properties.id:-1);
        this.order.deliveryZoneIn = Number(this.sot?this.sot.properties.id:-1);
        this.order.deliveryCost = this.getDeliveryCost();
      })
    },
    createMap(){
      let coords = Order.defaultAddress.coords
      let addr = Order.defaultAddress.addr;
      let mtMap = new MtMap({ coords, addr },localStorage.uname == 'admintest');
      mtMap.init(Order.defaultAddress.center).then(()=>{
        mtMap.loadCustomFullscreenControl();
        let setPolygons = (type,polygons)=>{
          polygons.forEach((zone)=>{
            if(zone.geometry)
              mtMap.loadPolygon(type,zone.geometry,zone.options,zone.properties)
            else console.warn("at createMap: zone geometry not found",zone);
          });
        }
        setPolygons('sots',this.sots);
        setPolygons('zones',this.zones);
        return mtMap.locateCustomer().catch(console.error);
      })
      .then(()=>{
        mtMap.addEventListener("clickPolygon",(clickEvent)=>{ mtMap.movePlacemarkTo(mtMap.placemark,clickEvent.get("coords")) });
        if(this.order.lng && this.order.lat && this.order.addr) this.setMapData([this.order.lng,this.order.lat], this.order.addr);
      });
      return mtMap;
    },
    async updateDiscountMethods(){
      await this.updateBonus(),
      await this.updateCouponsAndCertificates(),
      await this.$refs.promoInput?.checkPromocode("",true),
      await this.updateFriendPromocode()
      this.discountMethods = [{ value:'Не использовать', id:this.discountMethodsTypes.dontUse }];
      if(this.discountCards.length) this.discountMethods.push({ value:'Дисконтная карта', id:this.discountMethodsTypes.discountCard });
      if(this.certificates.length) this.discountMethods.push({ value:'Сертификат', id:this.discountMethodsTypes.certificate })
      if(this.$refs.promoInput?.enabled || this.friendPromocode){
        this.discountMethods.push({ value:'Промокод', id:this.discountMethodsTypes.promocode });
        if(this.defaultPromocodes)
          this.$nextTick(()=>this.$refs.promoInput.checkPromocode(this.order.promocode));
      }
      if(this.order.proc && this.order.bonusUser)
        this.discountMethods.push({ value:`Бонусы (${this.order.proc}% от суммы основного заказа)`, id:this.discountMethodsTypes.bonus });
      this.order.discountMethod = this.getDefaultDiscountMethod();
      this.order.promocode = this.defaultDiscount?this.defaultDiscount.promocode:null;
    },
    getDefaultDiscountMethod(){
      let method = this.discountMethodsTypes.dontUse;
      if(this.defaultDiscount){
        if(this.defaultDiscount.webUserId) { //Если есть webUserId, то discount - это дисконтная карта или сертефикат
          method = this.defaultDiscount.intType == 3?this.discountMethodsTypes.certificate:this.discountMethodsTypes.discountCard;
        } else {
          method = this.discountMethodsTypes.promocode;
        }
      }
      if(this.order.proc && this.order.bonusUser && this.order.bonus_pay)
        method = this.discountMethodsTypes.bonus;
      return method;
    },
    updateFriendPromocode(){
      return axios.get("/Api/checkPromocode",{  params:{ phone: this.order.phonePrefix + this.order.phone } })
        .then(({data})=>{ this.friendPromocode = data.enabled; })
        .catch(console.error);
    },
    async updateCouponsAndCertificates(){
      let discounts = await Repository.getUserCoupons(this.order.phonePrefix + this.order.phone);
      this.coupons = [];
      if(this.defaultDiscount && this.defaultDiscount.webUserId)//Если нет webUserId, значит это промокод(он здесь не нужен)
        discounts.unshift(this.defaultDiscount);//добавляем использованную скидку в список, так как она не возвращается
      discounts.forEach((discount)=>{
        let expiration = new Date(discount.expiration);
        let selectData = { data:discount, date:expiration.toLocaleDateString() };
        this.coupons.push(selectData);
      });
    },
    updateBonus(){
      return axios
        .get("/Api/getUserBonus",{ params: { phone: this.order.phonePrefix + this.order.phone } })
        .then((response) => {
          if(response.data.code == 200 || response.data.code == 400){
            this.order.bonusUser = response.data.bonus||0;
            this.order.proc = response.data.proc||0;
            if(this.availableBonus)
              this.order.bonus_pay = Math.max(0,Math.min(this.order.bonus_pay,this.availableBonus));
          }
          else console.warn("GET "+window.location.origin+"/Api/getUserBonus "+response.data.code+": "+response.data.msg);
        })
        .catch((e)=>{
          console.error(e);
        });
    },
    getGifts(summ){
      return new Promise((resolve,reject)=>{
        axios
          .get("/Api/getGifts",{ headers: {'X-Access-Token': Globals.api_token }, params:{ s:summ } })
          .then(({data})=>{
            resolve([{ minSum:0, title:"Нет", value:0 },...data.gifts]);
          })
          .catch((e)=>{ reject(e); console.error(e); this.alertRequestError(e); })
      })
    },
    getDeliveryCost(){
			let deliveryCost = 0;
      let limit = 0;
      if(this.zone){
        if(this.order.pension==1)
          limit = this.zone.conditions.limit_lgot;
        else
          limit = this.zone.conditions.limit
        if (this.getTotal(true, false) < limit)
          deliveryCost = this.zone.conditions.cost;
      }
      else deliveryCost = 0;      
			return deliveryCost
    },
    restoreOrder(){
      Vue.set(this.order,'refresh',1);
      axios
        .get("/Api/refreshOrder", {
          headers:{ 'X-Access-Token':Globals.api_token },
          params:{ orderId:this.order.id } })
        .then(({data})=>{
          if(data.code == 200)
            window.addEventListener('getAlerts',({ detail:{ orderstimeout } })=>{
              if(!this.badOrders.includes(this.order.id))
                this.order.refresh = 0;
              else
                this.order.refresh = -1;
            }, { once:true });
        })
        .catch((e)=>{
          console.error(e);
          this.order.refresh = -1;
          this.alertRequestError(e);
        });
    },
    save(){
      if(this.limitedMode){
        let propsList = ['payment', 'status'];
        let props = propsList.filter(prop=>this.order[prop]!=this.orderDraft[prop]);
        this.saveProperties(props.map( (name)=>[name, this.order[name]] ));
      }
      else this.saveWithValidation();
    },
    async saveWithValidation(){
      if(!this.zone || !this.sot){
        let message = "Адрес вне доступной зоны или соты."
        this.saveError(message);
        throw new Error(message)
      }
      await this.order.validate([
        { name:"name", description:"Имя", func:name=>name && name.trim().length>0 },
        { name:"phone", description:"Телефон" },
        { name:"addr", description:"Адрес" },
        { name:"deliveryDate", description:"Период доставки -> Дата" },
        { 
          name:"waveId",
          description:"Период доставки -> Временная волна",
          func:waveId=>this.deliveryWaves.contains(wave=>wave.id==waveId)
        },
        { name:"deliveryZone", error:"Ошибка получения зоны." },
        { 
          name:"sum_total",
          description:"Cумма",
          error:"Минимальная сумма: "+this.$getCurrencyPrice(this.zones.length>this.order.deliveryZone?this.zone.conditions.limit_min:0, this.course),
          func:()=>!this.showMinSum
        },
        { name:"gift", error:"Сохранение отменено", func:gift=>gift=="0" && this.gifts.length>1?confirm("Акция не выбрана.\nПродолжить сохранение?"):true },
        {
          name:"items",
          error:"Сохранение отменено",
          func:items=>{
            if(this.order.id!=0 && items.contains(item=>item.weightId))
              if(items.contains(item=>Boolean(item.quantity_warehouse)))
                return true;
              else return confirm("Убедитесь, что вы получили кол-ва со склада.\nДа - сохранить. Нет - отменить сохранение.");
            else return true;
          }
        }
      ])
      .then(this.saveOrder)
      .catch((e)=>{
        let message = "";
        let errorMessage = "";
        if(e instanceof Error){ 
          message = "Неизвестная ошибка. Обратитесть к программисту.";
          errorMessage = e.message;
        } else {
          message = e.error || "Поле \""+e.description+"\" не заполнено.";
          errorMessage = message;
        }
        this.saveError(message);
        throw errorMessage
      });
    },
    saveOrder(){
      this.order.items.forEach(item=>item.scaned=0);
      this.state = this.states.loading;
      switch(this.order.discountMethod){
        case this.discountMethodsTypes.dontUse:
          this.promocode = "";
          this.bonus_pay = 0;
          break;
        case this.discountMethodsTypes.bonus:
          this.order.promocode = "";
          break;
        default:break;
      }
      this.order.course = this.$root.UAH?this.order.course || this.$course:0;
      this.$emit("save",this.order,this._onSave,this._onSaveError);
    },
    _onSave(){
      this.saveOrderDraft();
      this.state = this.states.loaded;
      window.location.reload();
    },
    _onSaveError(error){
      if(error.type=="thrown"){
        this.saveError("Ошибка "+(error.response.data.code || error.response.data.error));
        this.state = this.states.error;
      }
      else{
        console.error(error.response);
        this.errored = true;
        this.saveError(error.response.message);
      }
    },
    /**
     * @param {Array<Array>} props Поля в формате вхождений: [ [key:value], [key:value] ]
     */
    async saveProperties(props){
      for(let [field, value] of props)
      await axios
        .get("/Api/setOrderFeature", { params:{ orderId:this.order.id, field, value }})
        .then(this._onSave).catch(this._onSaveError);
    },
    cancelEdit(){
      if(this.order.id==0){
        window.location.reload();
        return;
      }
      this.$emit('cancel',this.order,this.orderDraft);
    },
    saveOrderDraft(onlyStorage=false){
      let draft = {...this.order};
      localStorage.orderDraft = JSON.stringify(draft);
      if(!onlyStorage) 
        this.orderDraft = draft;
      this.$emit("save-draft",localStorage.orderDraft);
    },
    updateProfile(){
      return axios.get("/Api/getProfile", { params: { phone: this.order.phonePrefix + this.order.phone } })
        .then((response)=>{ 
          if(response.data.code!=200 || response.data.code==400) throw new Error(`/Api/getProfile (${response.data.msg}) ${response.data.code}`);
          this.profile = response.data.profile;
          if(!this.order.id)
            this.order.pension = this.profile.pension;
        })
        .catch(console.error);
    },
    setProfile(params){
      return axios.get("/Api/setProfile",{ params: { phone: this.order.phonePrefix + this.order.phone, ...params } })
        .then(()=>{ if(this.profile) this.profile = { ...this.profile, ...params }; })
        .catch(console.error);
    },
    loadNote(){
      switch(this.noteLoadingMode){
        case("add"):this.order.note = (!this.order.note?'':this.order.note) + this.profile.noteUser; break;
        case("replace"):this.order.note=this.profile.noteUser || ''; break;
        default:alert("Сначала выберите режим загрузки в настройках"); break;
      }
    },
  }
}
</script>

<style lang="scss">
.country-select{
  .country-select-panel{
    display: flex;
    flex-direction: column;
    // height: 200%;
    border: 1px solid #cbcbcb !important;
    border-radius: 4px;
    margin-top: 2px;
    padding: 3px 0;
    transition: all 1s ease-in-out;
    font-size: 18px;
    .custom-option{
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-width: max-content;
      align-items: center;
      font-size: initial;
    }
  }
  .cso-title{
    display: flex;
    height: 100%;
    align-items: center;
    &.option{
      display: contents;
    }
  }
}
.deliveryWave{
  &.disabled {
    background: pink;
  }
}
.center-label{
  height:30px;
  align-self: center;
}
.order-edit-view{
  border: 1px solid #bfbfbf;
  border-radius: 8px;
  padding: 8px;
  background: #ebebeb;
  min-width: min-content;
  input[type="checkbox"]{
    transform: scale(1.3);
  }
  .order-title{
    font-size: 18px;
    font-weight: 600;
    display: flex;
  }
  .view-settings{
    font-size: 18px;
    .settings-louncher{
      cursor: pointer;
      margin-left: 20px;
      color:gray;
      &:hover{
        color: black;
      }
    }
    .setting{
      display: flex;
      padding:5px;
      margin: 5px;
      border-bottom: 1px solid gray;
      align-items: center;
      label{
        display:block;
        width:100%;
      }
      cursor: pointer;
      &:hover{
        background:#dddddd;
      }
    }
    .float-panel{
      width:auto;
      height: auto;
      padding:4px;
      border-radius: 4px;
      box-shadow: 0px 0px 4px 0px gray;
      border:none;
    }
  }
  .edit-context{
    display: flex;
    .properties, .property-additional-panel {
      width: 100%;
    }
    .properties{
      height: max-content;
      position: sticky;
      top: 0;
      .editor-property{
        margin: 3px 0;
        &:hover{
          background: #dddddd;
        }
        &.note-prop{
          button{
            margin: 0 1px;
          }
        }
      }
      .info{
        border:1px solid #bfbfbf;
        background: whitesmoke;
      }
      .checkout_text{
        padding: 0px 4px;
        width: max-content;
        font-size: initial;
        &.sselect{
          height: 100%;
        }
        &.additional-select{
          margin: 0 3px;
          width:65px;
        }
        &.long-input{
          width: 500px;
          min-height: 30px;
          max-width: 500px;
          min-width: 500px;
        }
        &:hover{
          background: #fdfdfd;
        }
        &:disabled{
          background: whitesmoke;
          border-color: #c1c1c1;
          color: gray;
          cursor: default;
        }
      }
      button, select, input[type="button"], input[type="checkbox"]:not(:disabled){
        cursor: pointer;
      }
      input, textarea{
        &.checkout_text{
          cursor: text;
        }
      }
      .bonus-info{
        display: flex;
        .bonus-info-unite{
          border: 1px solid;
          border-left: none;
          padding: 4px;
          &:first-child{
            border-radius: 4px 0px 0px 4px;
            border-left: 1px solid;
          }
          &:last-child{
            border-radius: 0px 4px 4px 0px;
          }
        }
      }
    }
  }
  .property-additional-panel{
    .properties{
      padding: 0px 5px;
      .editor-property{
        min-height: 30px;
      }
    }
    .inner-goods{
      margin: 5px;
      max-height: 100%;
      display: flex;
      flex-direction: column;
      .quantity{
        padding: 3px;
        min-width: 6em;
      }
      .goods-table{
        max-height: 100%;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        table{
          border: none;
          tr{
            &.weight{
              box-shadow: inset 0px 0px 0px 2px #c9c9ff;
              background: #ededff;
            }
            &.parentId{
              background: #c8c4bf;
            }
            &.scaned{
              box-shadow: inset 0px 0px 0px 2px #beffbb;
              background: #daffda;
            }
          }
          th,td{
            border: 1px solid #cbcbcb;
          }
        }
      }
      button{
        width: fit-content;
      }
    }
  }
  .marked{
    color: #eb4747;
    animation: text-pulse 1s ease-in 0s infinite
  }
  @keyframes text-pulse {
    0%{
      text-shadow: 0 0 0px red;
    }
    50%{
      text-shadow: 0 0 3px red;
    }
    100%{
      text-shadow: 0 0 0px red;
    }
  }
}
</style>