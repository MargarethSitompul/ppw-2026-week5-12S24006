<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model {
    protected $fillable = ['code', 'name', 'credits', 'semester', 'quota', 'lecturer'];

    public function enrollments(): HasMany {
        return $this->hasMany(Enrollment::class);
    }
}