<?php

namespace LoveDuckie\SilverStripe\SocialMetaTags\Extensions;

use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Director;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;

class SocialMediaTagsExtension extends DataExtension
{
    use Configurable;

    public const TITLE_DELIMITER = ' » ';
    public const ELLIPSIS = ' ...';

    /**
     * Retrieves a configuration property with optional overrides for page type and platform.
     */
    private function getPageConfig(string $property, ?string $pageType = null, ?string $platform = null)
    {
        $config = $this->config();

        // Check page type overrides
        if ($pageType) {
            $pageTypeConfig = $config->get('page_types')[$pageType] ?? null;
            if ($pageTypeConfig && isset($pageTypeConfig[$property])) {
                $typeConfig = $pageTypeConfig[$property];
                if ($platform && isset($typeConfig[$platform])) {
                    return $typeConfig[$platform];
                }
                return $typeConfig['default'] ?? null;
            }
        }

        // Default configuration
        $propertyConfig = $config->get($property) ?? null;
        if ($propertyConfig) {
            if ($platform && isset($propertyConfig[$platform])) {
                return $propertyConfig[$platform];
            }
            return $propertyConfig['default'] ?? $propertyConfig;
        }

        return null;
    }

    /**
     * Generates a truncated description with an optional character limit.
     */
    private function truncateDescription(string $description, int $limit): string
    {
        return strlen($description) > $limit
            ? substr($description, 0, $limit - strlen(self::ELLIPSIS)) . self::ELLIPSIS
            : $description;
    }

    /**
     * Builds the page title based on configuration and owner properties.
     */
    private function buildPageTitle(string $title = '', bool $includeTagline = true): string
    {
        $siteConfig = SiteConfig::current_site_config();
//        $parts = [$siteConfig->getWebsiteTitle()];
        $parts = [$siteConfig->Title];

        if ($includeTagline && $siteConfig->Tagline) {
            $parts[] = $siteConfig->Tagline;
        }

        if (!empty($title)) {
            $parts[] = $title;
        }

        return implode(self::TITLE_DELIMITER, $parts);
    }

    /**
     * Generates the main image metadata for the current page.
     */
    private function generatePageImage(): array
    {
        $owner = $this->owner;
        $pageType = get_class($owner);
        $siteConfig = SiteConfig::current_site_config();

        $defaultImageField = $this->getPageConfig('image', $pageType);
        $imageField = $defaultImageField ? $owner->$defaultImageField : $siteConfig->ImpressionImage;

        $platform = SocialPlatforms::identifyPlatform();
        $imageWidth = $this->getPageConfig('image_width', null, $platform) ?? 1200;
        $imageHeight = $this->getPageConfig('image_height', null, $platform) ?? 627;

        if ($imageField && $imageField instanceof Image && $imageField->exists()) {
            return $this->generateImageProperties($imageField, $imageWidth, $imageHeight);
        }

        return [];
    }

    /**
     * Generates properties for an image, including dimensions and type.
     */
    private function generateImageProperties(Image $image, int $width, int $height): array
    {
        $resizedImage = $image->Fill($width, $height);

        return [
            'image' => $resizedImage->AbsoluteLink(),
            'image_width' => $width,
            'image:width' => $width,
            'image_height' => $height,
            'image:height' => $height,
            'image_type' => $resizedImage->MimeType,
            'image_alt' => htmlspecialchars($image->AltDescription ?? ''),
        ];
    }

    /**
     * Renders OpenGraph meta tags.
     */
    public function renderOpenGraphTags(string &$tags, array $properties): void
    {
        $tags .= "\n<!-- OpenGraph Meta Tags -->\n";
        foreach (['type', 'site_name', 'locale', 'title', 'image', 'description', 'url', 'image:width', 'image:height', 'image:alt'] as $key) {
            if (isset($properties[$key])) {
                $tags .= $this->constructMetaTag('property', "og:{$key}", 'content', $properties[$key]);
            }
        }
    }

    /**
     * Renders Profile meta tags.
     */
    public function renderProfileTags(string &$tags, array $properties): void
    {
        $tags .= "\n<!-- Profile Meta Tags -->\n";
        foreach (['first_name', 'last_name'] as $key) {
            if (isset($properties[$key])) {
                $tags .= $this->constructMetaTag('name', "profile:{$key}", 'content', $properties[$key]);
            }
        }
    }

    /**
     * Renders Twitter meta tags.
     */
    public function renderTwitterTags(string &$tags, array $properties): void
    {
        $tags .= "\n<!-- Twitter Meta Tags -->\n";
        $twitterCard = $this->getPageConfig('twitter', null, 'card') ?? 'summary';

        $tags .= $this->constructMetaTag('name', 'twitter:card', 'content', $twitterCard);

        foreach (['title', 'description', 'image'] as $key) {
            if (isset($properties[$key])) {
                $tags .= $this->constructMetaTag('name', "twitter:{$key}", 'content', $properties[$key]);
            }
        }
    }

    /**
     * Constructs a single meta tag.
     */
    private function constructMetaTag(string $attrName, string $name, string $attrValue, string $value): string
    {
        return "<meta {$attrName}=\"{$name}\" {$attrValue}=\"{$value}\" />\n";
    }

    /**
     * Generates all meta tags for the current page.
     */
    public function MetaTags(string &$tags): void
    {
        $owner = $this->owner;
        $pageType = get_class($owner);
        $siteConfig = SiteConfig::current_site_config();

        $title = $this->buildPageTitle($owner->Title ?? '', true);
        $description = $this->truncateDescription(strip_tags($siteConfig->Tagline ?? ''), $this->getPageConfig('description_limit') ?? 300);
        $image = $this->generatePageImage();

        $properties = [
            'title' => $title,
            'description' => $description,
            'image' => $image['image'] ?? '',
            'locale' => $this->getPageConfig('locale', null, null) ?? 'en_GB',
            'profile:first_name' => $this->getPageConfig('first_name', null, null),
            'profile:last_name' => $this->getPageConfig('last_name', null, null),
//            'site_name' => $siteConfig->getWebsiteTitle(),
            'site_name' => $siteConfig->Title,
            'type' => $this->getPageConfig('opengraph', null, 'type') ?? 'website',
            'url' => Director::absoluteURL($owner->Link()),
        ];

        $this->getOwner()->extend('updateSocialMetaTagsProperties', $properties);

        // Render meta tags
        $this->renderProfileTags($tags, $properties);
        $this->renderOpenGraphTags($tags, $properties);
        $this->renderTwitterTags($tags, $properties);
    }
}
