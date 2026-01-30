<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    //
    protected $table = "bank_accounts";

    protected $fillable = [
        'bank_name',
        'account_holder_name',
        'account_number',
    ];

    public function suppliers(){
        return $this->hasMany(Supplier::class);
    }
}
