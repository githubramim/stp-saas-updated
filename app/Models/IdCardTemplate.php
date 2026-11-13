<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdCardTemplate extends Model
{
    public const TYPES = ['student', 'employee'];

    protected $fillable = [
        'institute_id', 'type', 'design',
    ];

    protected function casts(): array
    {
        return [
            'design' => 'array',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public static function defaults(string $type = 'student'): array
    {
        $isStudent = $type !== 'employee';

        return [
            'front' => [
                'title' => $isStudent ? 'Student Identity Card' : 'Employee Identity Card',
                'subtitle' => $isStudent ? 'Training & Skills Development' : 'Staff & Faculty',
                'accent' => '#4f46e5',
                'accent2' => '#7c3aed',
                'background' => '#ffffff',
                'text' => '#0f172a',
                'show_logo' => true,
                'show_photo' => true,
                'show_qr' => true,
                'show_id_no' => true,
                'show_line1' => true,
                'show_line2' => true,
                'show_blood' => $isStudent,
                'show_phone' => true,
                'show_valid' => $isStudent,
                'footer' => 'This card remains the property of the institute.',
            ],
            'back' => [
                'title' => 'Card Holder Information',
                'accent' => '#4f46e5',
                'accent2' => '#7c3aed',
                'background' => '#ffffff',
                'text' => '#0f172a',
                'show_qr' => true,
                'show_address' => true,
                'show_phone' => true,
                'show_email' => true,
                'show_website' => false,
                'instructions' => "If found, please return this card to the institute office.\nThis card is non-transferable and must be produced on request.",
                'address' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
            ],
        ];
    }

    public static function for(int $instituteId, string $type): self
    {
        $type = in_array($type, self::TYPES, true) ? $type : 'student';

        return static::firstOrCreate(
            ['institute_id' => $instituteId, 'type' => $type],
            ['design' => static::defaults($type)]
        );
    }

    public function resolvedDesign(): array
    {
        return array_replace_recursive(
            static::defaults($this->type),
            is_array($this->design) ? $this->design : []
        );
    }
}
