<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';
    public $timestamps = true;

    protected $fillable = [
        'name', 'description', 'price', 'menu_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function orderMenus()
    {
        return $this->hasMany(OrderMenu::class, 'menu_id');
    }
}
