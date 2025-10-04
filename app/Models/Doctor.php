<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // 1. قم باستيراد HasMany

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo_path',
        'department_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // 2. أضف هذه الدالة لتعريف العلاقة
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}