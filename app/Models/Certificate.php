<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'template_id',
        'certificate_data', // JSON: name, date, etc.
        'file_path', // Path to generated certificate file
    ];

    protected $casts = [
        'certificate_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }
}
