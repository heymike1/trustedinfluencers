<?php

namespace Tests\Unit;

use App\Enums\Platform;
use App\Social\Support\HandleNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HandleNormalizerTest extends TestCase
{
    private HandleNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new HandleNormalizer;
    }

    #[DataProvider('youtubeInputs')]
    public function test_youtube_inputs_resolve_to_the_same_handle(string $input): void
    {
        $result = $this->normalizer->normalize(Platform::YouTube, $input);

        $this->assertSame('john', $result->handle);
        $this->assertSame('https://www.youtube.com/@john', $result->profileUrl);
    }

    public static function youtubeInputs(): array
    {
        return [
            ['https://youtube.com/@john'],
            ['youtube.com/@john'],
            ['www.youtube.com/@John/videos'],
            ['https://m.youtube.com/@john?si=abc'],
            ['@john'],
            ['John'],
            ['  @JOHN  '],
            ['youtube.com/c/john'],
        ];
    }

    public function test_youtube_channel_urls_carry_the_provider_id(): void
    {
        $result = $this->normalizer->normalize(Platform::YouTube, 'https://www.youtube.com/channel/UC_x5XG1OV2P6uZZ5FSM9Ttw');

        $this->assertSame('UC_x5XG1OV2P6uZZ5FSM9Ttw', $result->providerAccountId);
    }

    #[DataProvider('instagramInputs')]
    public function test_instagram_inputs_resolve_to_the_same_handle(string $input): void
    {
        $this->assertSame('john.smith', $this->normalizer->normalize(Platform::Instagram, $input)->handle);
    }

    public static function instagramInputs(): array
    {
        return [
            ['https://www.instagram.com/john.smith/'],
            ['instagram.com/John.Smith'],
            ['@john.smith'],
            ['john.smith'],
            ['https://instagram.com/john.smith?igsh=xyz'],
        ];
    }

    #[DataProvider('xInputs')]
    public function test_x_inputs_resolve_to_the_same_handle(string $input): void
    {
        $this->assertSame('john_smith', $this->normalizer->normalize(Platform::X, $input)->handle);
    }

    public static function xInputs(): array
    {
        return [
            ['https://x.com/john_smith'],
            ['https://twitter.com/John_Smith/status/123'],
            ['x.com/john_smith'],
            ['@john_smith'],
        ];
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_inputs_are_rejected(Platform $platform, string $input): void
    {
        $this->assertNull($this->normalizer->normalize($platform, $input));
    }

    public static function invalidInputs(): array
    {
        return [
            [Platform::YouTube, 'https://instagram.com/john'],
            [Platform::YouTube, 'ab'],
            [Platform::Instagram, 'https://youtube.com/@john'],
            [Platform::Instagram, 'has spaces'],
            [Platform::Instagram, 'https://www.instagram.com/p/Cxyz123/'],
            [Platform::X, 'this_handle_is_far_too_long'],
            [Platform::X, 'https://x.com/i/status/123'],
            [Platform::X, ''],
        ];
    }

    public function test_it_detects_the_platform_from_urls_only(): void
    {
        $this->assertSame(Platform::YouTube, $this->normalizer->detectPlatform('youtube.com/@john'));
        $this->assertSame(Platform::Instagram, $this->normalizer->detectPlatform('https://www.instagram.com/john/'));
        $this->assertSame(Platform::X, $this->normalizer->detectPlatform('https://twitter.com/john'));
        $this->assertNull($this->normalizer->detectPlatform('@john'));
        $this->assertNull($this->normalizer->detectPlatform('john.smith'));
    }
}
