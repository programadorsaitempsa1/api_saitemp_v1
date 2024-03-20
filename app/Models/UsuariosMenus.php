<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\carbon;

class UsuariosMenus extends Model
{
    use HasFactory;
    protected $table = 'usr_app_usuarios_menus';
    public function fromDateTime($value)
    {
        return Carbon::parse(parent::fromDateTime($value))->format('Y-d-m');
    }
}
