<?php

namespace App\Enums;

enum Platform: string
{
    case YouTube = 'youtube';
    case Instagram = 'instagram';
    case X = 'x';

    /**
     * Platforms creators can connect right now (config/social.php). A disabled platform keeps
     * working for accounts that are already connected; it is simply not offered any more.
     *
     * @return list<self>
     */
    public static function enabled(): array
    {
        return array_values(array_filter(self::cases(), fn (self $p) => $p->isEnabled()));
    }

    public function isEnabled(): bool
    {
        return in_array($this->value, config('social.enabled_platforms', []), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::YouTube => 'YouTube',
            self::Instagram => 'Instagram',
            self::X => 'X',
        };
    }

    /** Two-letter tag used in compact tables and lists. */
    public function short(): string
    {
        return match ($this) {
            self::YouTube => 'YT',
            self::Instagram => 'IG',
            self::X => 'X',
        };
    }

    /** "Instagram or X", for copy that lists what creators can connect today. */
    public static function enabledLabels(string $lastGlue = ' and '): string
    {
        return collect(self::enabled())->map(fn (self $p) => $p->label())->join(', ', $lastGlue);
    }

    /** The word the platform uses for its audience count. */
    public function audienceNoun(): string
    {
        return match ($this) {
            self::YouTube => 'subscribers',
            self::Instagram, self::X => 'followers',
        };
    }

    public function profileUrl(string $handle): string
    {
        return match ($this) {
            self::YouTube => "https://www.youtube.com/@{$handle}",
            self::Instagram => "https://www.instagram.com/{$handle}/",
            self::X => "https://x.com/{$handle}",
        };
    }

    /** Content types tracked for this platform, primary first. */
    public function contentTypes(): array
    {
        return match ($this) {
            self::YouTube => [ContentType::Video, ContentType::Short],
            self::Instagram => [ContentType::Reel, ContentType::Post],
            self::X => [ContentType::Post],
        };
    }

    public function primaryContentType(): ContentType
    {
        return $this->contentTypes()[0];
    }
}
