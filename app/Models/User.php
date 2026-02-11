<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    public $timestamps = true;
    // Указываем, что первичный ключ автоинкрементный


    protected $fillable = [
        'name',
        'surname',
        'patronymic',
        'login',
        'password',
        'photo_file',
        'api_token',
        'status',
        'role_id'
    ];



    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function shiftWorkers(): hasMany
    {
        return $this->hasMany(ShiftWorker::class, 'user_id');
    }
}
