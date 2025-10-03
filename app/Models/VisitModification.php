<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitModification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'visit_id',
        'modified_by',
        'action',
        'changes',
        'reason',
        'modified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'changes' => 'array',
        'modified_at' => 'datetime',
    ];

    /**
     * Get the visit that was modified.
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Get the user who made the modification.
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by')->withTrashed();
    }

    /**
     * Get a human-readable description of the action.
     */
    public function getActionDescriptionAttribute(): string
    {
        return match($this->action) {
            'created' => 'Visit created (Clock In)',
            'clock_out' => 'Visit completed (Clock Out)',
            'note_updated' => 'Progress notes updated',
            'note_deleted' => 'Progress notes deleted',
            default => 'Visit modified',
        };
    }

    /**
     * Get a formatted description of what changed.
     */
    public function getChangesDescriptionAttribute(): ?string
    {
        if (!$this->changes) {
            return null;
        }

        $descriptions = [];
        foreach ($this->changes as $field => $change) {
            $descriptions[] = match($field) {
                'clock_out_time' => 'Clock out time recorded',
                'progress_notes' => $this->formatNoteChange($change),
                'clock_out_signature_path' => 'Clock out signature captured',
                default => ucwords(str_replace('_', ' ', $field)) . ' changed',
            };
        }

        return implode(', ', $descriptions);
    }

    /**
     * Format the progress notes change description.
     */
    private function formatNoteChange(array $change): string
    {
        if (isset($change['from']) && isset($change['to'])) {
            if (empty($change['from']) && !empty($change['to'])) {
                return 'Progress notes added';
            } elseif (!empty($change['from']) && empty($change['to'])) {
                return 'Progress notes removed';
            } else {
                return 'Progress notes modified';
            }
        }
        return 'Progress notes changed';
    }
}