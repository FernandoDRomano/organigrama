<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /*
        CONSTANTES
    */
    const ROLE_ADMIN = "admin";
    const ROLE_CUSTOMER = "customer";
    const STATUS_ACTIVE = "active";
    const STATUS_BLOCKED = "blocked";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    public function updateStatusAndTokens(){
        $this->changeStatus();
        $this->removeTokens();
    }

    public function changeStatus() :void {
        $this->status === User::STATUS_ACTIVE 
            ? $this->status = User::STATUS_BLOCKED 
            : $this->status = User::STATUS_ACTIVE;
    }

    public function removeTokens(){
        if($this->status === User::STATUS_BLOCKED)
            $this->tokens()->delete();
    }

    public function organizations(){
        return $this->hasMany(Organization::class);
    }

}
