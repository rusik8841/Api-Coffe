<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusOrder extends Model
{
    protected $table = 'status_orders';
    public $timestamps = false;

    protected $fillable = ['name', 'code'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'status_order_id');
    }
}
