<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AttendanceRecord extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'session_id',
        'fellow_id',
        'clock_in_time',
        'clock_out_time',
        'status',
        'leave_reason',
        'admin_notes',
        'is_manually_adjusted',
    ];

    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'is_manually_adjusted' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function fellow()
    {
        return $this->belongsTo(User::class, 'fellow_id')->withTrashed();
    }
}
