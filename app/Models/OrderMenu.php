<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMenu extends Model
{
    protected $table = 'order_menus';
    public $timestamps = true;

    protected $fillable = ['menu_id', 'order_id', 'count'];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
