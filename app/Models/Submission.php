<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = 'submissions';

    protected $fillable = [
        'kode',
        'tanggal',
        'kitchen_id',
        'menu_id',
        'porsi',
        'total_harga',
        'status',
        'parent_id',
        'supplier_id'
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_id', 'id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function details()
    {
        return $this->hasMany(SubmissionDetails::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
