<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "name",
        "email",
        "password",
        "role",
        "email_verified_at",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    /**
     * Check if user is admin
     * Supports both legacy role column and Spatie permission system
     */
    public function isAdmin(): bool
    {
        // Check legacy role column for backward compatibility
        if ($this->role === "admin") {
            return true;
        }

        // Check Spatie permission system
        return $this->hasRole("admin");
    }

    /**
     * Check if user has any administrative privileges
     */
    public function hasAdminAccess(): bool
    {
        return $this->hasAnyRole(["admin", "manager"]) ||
            $this->can("admin.dashboard");
    }
}
