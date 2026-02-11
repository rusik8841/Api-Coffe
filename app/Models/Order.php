<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    public $timestamps = true;

    protected $fillable = [
        'number_of_person', 'table_id', 'shift_worker_id', 'status_order_id'
    ];

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function shiftWorker()
    {
        return $this->belongsTo(ShiftWorker::class, 'shift_worker_id');
    }

    public function status()
    {
        return $this->belongsTo(StatusOrder::class, 'status_order_id');
    }

    public function orderMenus()
    {
        return $this->hasMany(OrderMenu::class, 'order_id');
    }

    // Связь "многие-ко-многим" через промежуточную таблицу
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'order_menus', 'order_id', 'menu_id')
            ->withPivot('count')
            ->withTimestamps();
    }
}
