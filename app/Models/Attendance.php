<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_HADIR = 'hadir';
    public const STATUS_IZIN_KETERANGAN = 'izin_keterangan';
    public const STATUS_IZIN_TANPA_KETERANGAN = 'izin_tanpa_keterangan';

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'notes',
        'check_in_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_HADIR => 'Hadir',
            self::STATUS_IZIN_KETERANGAN => 'Izin (Dengan Keterangan)',
            self::STATUS_IZIN_TANPA_KETERANGAN => 'Izin (Tanpa Keterangan)',
            default => ucfirst(str_replace('_', ' ', $this->status ?? '-')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_HADIR => 'badge-success',
            self::STATUS_IZIN_KETERANGAN => 'badge-primary',
            self::STATUS_IZIN_TANPA_KETERANGAN => 'badge-danger',
            default => 'badge-neutral',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_HADIR => '✅',
            self::STATUS_IZIN_KETERANGAN => '📝',
            self::STATUS_IZIN_TANPA_KETERANGAN => '⚠️',
            default => '⚪',
        };
    }

    public function getFormattedDateAttribute(): string
    {
        if (!$this->date) {
            return '-';
        }

        return Carbon::parse($this->date)->translatedFormat('l, d F Y');
    }

    public function getFormattedTimeAttribute(): string
    {
        if (!$this->check_in_time) {
            return Carbon::parse($this->created_at)->format('H:i') . ' WIB';
        }

        return substr($this->check_in_time, 0, 5) . ' WIB';
    }
}
