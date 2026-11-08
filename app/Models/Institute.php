<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institute extends Model
{
    protected $fillable = [
        'name', 'name_bn', 'code', 'mobile', 'email', 'address', 'logo', 'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
