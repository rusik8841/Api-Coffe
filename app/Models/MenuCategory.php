<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';
    public $timestamps = false;

    protected $fillable = ['name'];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_category_id');
    }
}
