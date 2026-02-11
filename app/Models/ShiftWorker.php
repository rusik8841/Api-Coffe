<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftWorker extends Model
{
    protected $table = 'shift_workers';
    public $timestamps = true;

    protected $fillable = ['work_shift_id', 'user_id'];

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'shift_worker_id');
    }
}
