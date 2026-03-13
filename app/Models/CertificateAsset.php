<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateAsset extends Model
{
    protected $fillable = [
        'key',
        'path',
        'type',
        'source',
        'status',
        'updated_by',
    ];
    
    // allow storing a human-friendly printed name for the asset (e.g. collaborator printed name)
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Add display_name to fillable for quick updates
    public function getFillable()
    {
        $f = parent::getFillable();
        if (!in_array('display_name', $f)) $f[] = 'display_name';
        return $f;
    }
}
