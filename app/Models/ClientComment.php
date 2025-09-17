<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientComment extends Model
{
    use HasFactory;

    protected $fillable = [
        "client_id",
        "user_id",
        "comment",
        "title",
        "status",
        "type",
    ];

    protected $casts = [
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    /**
     * Get the user that created this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client this comment belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
