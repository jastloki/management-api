<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "email",
        "phone",
        "company",
        "address",
        "status_id",
        "user_id",
        "is_email_valid",
        "email_status",
        "email_provider",
        "email_sent_at",
        "imported_from",
        "email_validation_reason",
        "email_validation_details",
        "email_last_validated_at",
        "email_validation_attempts",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "status" => "string",
        "is_email_valid" => "boolean",
        "email_sent_at" => "datetime",
        "email_validation_details" => "array",
        "email_last_validated_at" => "datetime",
        "email_validation_attempts" => "integer",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ClientComment::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, "status_id", "id");
    }

    /**
     * Get the status name (accessor for backward compatibility).
     */
    public function getStatusNameAttribute(): ?string
    {
        return $this->status?->name;
    }

    /**
     * Check if client has a specific status.
     */
    public function hasStatus(string $statusName): bool
    {
        return $this->status?->name === $statusName;
    }

    /**
     * Scope to filter clients by status name.
     */
    public function scopeWithStatusName($query, string $statusName)
    {
        return $query->whereHas("status", function ($q) use ($statusName) {
            $q->where("name", $statusName);
        });
    }

    /**
     * Scope to filter clients by status ID.
     */
    public function scopeWithStatus($query, int $statusId)
    {
        return $query->where("status_id", $statusId);
    }
}
