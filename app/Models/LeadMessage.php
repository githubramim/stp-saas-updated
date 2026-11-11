<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadMessage extends Model
{
    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_NOTE = 'note';

    public const CHANNELS = [
        self::CHANNEL_WHATSAPP => 'WhatsApp',
        self::CHANNEL_SMS => 'SMS',
        self::CHANNEL_NOTE => 'Note',
    ];

    protected $fillable = [
        'institute_id', 'lead_id', 'user_id', 'channel', 'direction',
        'status', 'body', 'provider_response', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->channel] ?? ucfirst((string) $this->channel);
    }

    public function channelIcon(): string
    {
        return match ($this->channel) {
            self::CHANNEL_WHATSAPP => 'bi-whatsapp',
            self::CHANNEL_SMS => 'bi-chat-dots',
            default => 'bi-sticky',
        };
    }
}
