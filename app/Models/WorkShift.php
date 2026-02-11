<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    protected $table = 'work_shifts';
    public $timestamps = true;

    protected $fillable = ['start', 'end', 'active'];

    public function shiftWorkers()
    {
        return $this->hasMany(ShiftWorker::class, 'work_shift_id');
    }
}
