<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class CertificateTemplate extends Model
{
    protected $fillable = ['name','course_id','template_data'];

    protected $casts = [
        'template_data' => 'array'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
