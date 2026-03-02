<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Campo que pode ser preenchido automaticamente
    protected $fillable = ['name'];

    /**
     * Um cargo (role) pode estar associado a vários utilizadores.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
