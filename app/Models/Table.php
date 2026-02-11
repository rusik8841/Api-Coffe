<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $table = 'tables';
    public $timestamps = false;

    protected $fillable = ['name', 'capacity'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
