<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proxy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "url",
        "type",
        "port",
        "username",
        "password",
        "country",
        "city",
        "is_active",
        "description",
        "extra_fields",
        "last_tested_at",
        "status",
        "response_time",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_active" => "boolean",
        "extra_fields" => "array",
        "last_tested_at" => "datetime",
        "port" => "integer",
        "response_time" => "integer",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    /**
     * Get the available proxy types.
     *
     * @return array<string, string>
     */
    public static function getTypes(): array
    {
        return [
            "http" => "HTTP",
            "https" => "HTTPS",
            "socks4" => "SOCKS4",
            "socks5" => "SOCKS5",
        ];
    }

    /**
     * Get the available statuses.
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            "untested" => "Untested",
            "working" => "Working",
            "failed" => "Failed",
        ];
    }

    /**
     * Scope active proxies.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }

    /**
     * Scope working proxies.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWorking($query)
    {
        return $query->where("status", "working");
    }

    /**
     * Scope by proxy type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where("type", $type);
    }

    /**
     * Get formatted proxy URL with authentication if available.
     *
     * @return string
     */
    public function getFullUrlAttribute(): string
    {
        $url = $this->url;

        if ($this->port) {
            $url .= ":" . $this->port;
        }

        if ($this->username && $this->password) {
            return $this->username . ":" . $this->password . "@" . $url;
        }

        return $url;
    }

    /**
     * Get status badge class for UI.
     *
     * @return string
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            "working" => "bg-success",
            "failed" => "bg-danger",
            "untested" => "bg-warning",
            default => "bg-secondary",
        };
    }

    /**
     * Get type badge class for UI.
     *
     * @return string
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            "http" => "bg-primary",
            "https" => "bg-success",
            "socks4" => "bg-info",
            "socks5" => "bg-secondary",
            default => "bg-light",
        };
    }

    /**
     * Check if proxy needs testing (hasn't been tested or failed).
     *
     * @return bool
     */
    public function needsTesting(): bool
    {
        return in_array($this->status, ["untested", "failed"]) ||
            ($this->last_tested_at &&
                $this->last_tested_at->diffInHours() > 24);
    }

    /**
     * Get location string.
     *
     * @return string|null
     */
    public function getLocationAttribute(): ?string
    {
        if ($this->city && $this->country) {
            return $this->city . ", " . $this->country;
        }

        return $this->country ?? ($this->city ?? null);
    }

    /**
     * Get extra fields formatted for display.
     *
     * @return string
     */
    public function getExtraFieldsFormatted(): string
    {
        if (empty($this->extra_fields)) {
            return "No extra fields";
        }

        $fields = [];
        foreach ($this->extra_fields as $key => $value) {
            $fields[] = ucfirst($key) . ": " . $value;
        }

        return implode(", ", $fields);
    }
}
