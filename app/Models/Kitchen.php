<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;


class Kitchen extends Model
{
    use SoftDeletes;

    protected $table = 'kitchens';

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'kepala_dapur',
        'nomor_kepala_dapur',
        'region_id'
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
    public function recipe_bahan_baku()
    {
        return $this->hasMany(RecipeBahanBaku::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'kitchen_user', 'kitchen_code', 'user_id', 'kode', 'id');
    }

    public function region()
    {
        return $this->belongsTo(region::class);
    }

    public function submissions(){
        return $this->hasMany(Submission::class);
    }
}
