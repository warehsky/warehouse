<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    protected $table = 'items';
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item', 'cargoId', 'note', 'price'
    ];
    /** */
	public function cargo()
    {
        return $this->belongsTo(Cargos::class, 'cargoId', 'id');
    }
}
