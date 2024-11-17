<?php


namespace LoveDuckie\SilverStripe\SocialMetaTags\Extensions;

use Exception;

class SocialPlatforms
{
    public const PLATFORM_LINKEDIN = 'LinkedIn';
    public const PLATFORM_FACEBOOK = 'Facebook';
    public const PLATFORM_TWITTER = 'Twitter';
    public const PLATFORM_PINTEREST = 'Pinterest';

    /**
     * Validates the user agent and throws an exception if invalid.
     */
    private static function validateUserAgent(?string $userAgent): void
    {
        if (empty($userAgent)) {
            throw new Exception("User agent is missing or invalid.");
        }
    }

    /**
     * Returns an associative array of platform-specific user agent validators.
     */
    public static function getPlatformValidators(): array
    {
        return [
            self::PLATFORM_FACEBOOK => fn($ua) => self::isFacebookUserAgent($ua),
            self::PLATFORM_LINKEDIN => fn($ua) => str_contains($ua, 'linkedinbot'),
            self::PLATFORM_TWITTER => fn($ua) => str_contains($ua, 'twitterbot'),
            self::PLATFORM_PINTEREST => fn($ua) => str_contains($ua, 'pinterestbot'),
        ];
    }

    private static function isFacebookUserAgent(string $userAgent): bool
    {
        self::validateUserAgent($userAgent);
        return str_contains($userAgent, 'facebookexternalhit') || str_contains($userAgent, 'facebookcatalog');
    }

    /**
     * Identifies the platform making the request based on user agent.
     */
    public static function identifyPlatform(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        if (!$userAgent) {
            return null;
        }

        foreach (self::getPlatformValidators() as $platform => $validator) {
            if ($validator($userAgent)) {
                return $platform;
            }
        }

        return null;
    }
}
