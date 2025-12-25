<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * الأدوار المرتبطة بهذا المستخدم
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * التحقق من أن المستخدم لديه دور معين
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * التحقق من أن المستخدم مدير النظام
     * إما من خلال is_admin أو من خلال دور super_admin
     */
    public function isSystemAdmin(): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->roles()->where('name', 'super_admin')->exists();
    }

    /**
     * التحقق من أن المستخدم لديه صلاحية معينة
     */
    public function hasPermission(string $permissionName): bool
    {
        // إذا كان المستخدم مدير النظام (is_admin أو super_admin)، لديه جميع الصلاحيات
        if ($this->isSystemAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    /**
     * الحصول على جميع الصلاحيات للمستخدم
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        // إذا كان المستخدم مدير النظام (is_admin أو super_admin)، لديه جميع الصلاحيات
        if ($this->isSystemAdmin()) {
            return Permission::all();
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }
}
