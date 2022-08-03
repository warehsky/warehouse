<?php

namespace App\Model;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'tagentId', 'clientId'
    ];
    protected $connection = 'mysql';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public $timestamps = false;
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /** */
    public function generateToken()
    {
        $this->api_token = \Str::random(60);
        
        $this->save();

        return $this->api_token;
    }
    /** */
    public function getToken(){
        return $this->api_token;
    }
    public static function loginByToken($t){
        $user = User::where('api_token', $t)->first();
        if($user)
            \Auth::login($user);
        return $user;
    }
    public function getJWTIdentifier()
    {
      return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
      return [];
    }
    /**
     * 
     */
    public static function getSchedule($u){
        return json_encode(['week' => $u->week, 'daySchedule' => $u->daySchedule]);
    }
    /**
     * Админ панель
    * Установливает признак gen в таблице пользователей для всех пользователей выбранного торгового направления
    * Входные параметры:
    * id - ID торгового направления
    */
    public static function setGeneretion($id){
        $d = User::where("tradeDirection", "=", $id)->increment("gen", 1);
        return $d;
    }
    /**
     * Админ панель
    * Установливает признак gen в таблице пользователей для одного пользователя
    * Входные параметры:
    * id - ID пользователя
    */
    public static function setUserGeneretion($id){
        if($id==0)
            $d = User::where("id", ">", $id)->increment("gen", 1);
        else
            $d = User::where("id", "=", $id)->increment("gen", 1);
        return $d;
    }
    /**
     * Админ панель
    * Возвращает список торговых агентов
    * Входные параметры:
    * id - ID торгового агента
    * f - строка для поиска по полю ФИО
    * td - ID торгового направления
    * order - имя поля для сортировки
    * dir - направление сортировки (asc - от а до я, desc - от я до а)
    */
    public static function getUsers($id, $f, $td, $order, $dir, $tradePerm=-1, $storeCheckPerm=-1, $areaId=0){
        $users = User::select('users.id as id', 'Login', 'Password', 'fio', 'areas.title as Area', 
                              'priceTypes as PriceTypes', 'users.guid as guid', 'tradeDirections.title as tdTitle', 
                              'is_locked', 'users.tradeDirection', 'users.tradePerm', 'users.storeCheckPerm', 'users.test', 
                              'users.updateTm', 'f2percent', 'f2time', 'users.checkDevice')
        ->join('areas', 'areaId', '=', 'areas.id')
        ->join('tradeDirections', 'tradeDirection', '=', 'tradeDirections.id');
        if( $id )
            $users = $users->where( 'users.id', $id );
        if( $f )
            $users = $users->where( 'fio', 'like', "%" . $f . "%" )
                           ->orwhere( 'login', 'like', "%" . $f . "%" );
        if( $td )
            $users = $users->where( 'tradeDirection', $td );
        if($tradePerm >= 0)
            $users = $users->where('users.tradePerm', $tradePerm);
        if($storeCheckPerm >= 0)
            $users = $users->where('users.storeCheckPerm', $storeCheckPerm);
        if($areaId > 0)
            $users = $users->where('users.areaId', $areaId);
        if( $order )
            $users = $users->orderBy( $order, $dir );
        $users = $users->with("sessions")->paginate(config('loadapi.PGINATE_USERS'));
        $users->links = $users->links();
        
        return $users;
    }
}
