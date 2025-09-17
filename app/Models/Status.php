<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = ["name", "description"];

    protected $casts = [
        "name" => "string",
        "description" => "string",
    ];

    /**
     * Get the clients that have this status.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
