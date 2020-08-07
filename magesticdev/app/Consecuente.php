<?php
namespace App;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
class Consecuente extends Model
{
    use Notifiable;
    protected $table = "consecuentes";

    protected $fillable = [
        'catalogo_id', 'siguiente_catalogo_id', 'id'
    ];
    protected $hidden = [
    ];
}