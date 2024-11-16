<?php

namespace LoveDuckie\SilverStripe\SocialMetaTags\Extensions;

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
        if ($pageType && isset($config['page_types'][$pageType][$property])) {
            $typeConfig = $config['page_types'][$pageType][$property];
            if ($platform && isset($typeConfig[$platform])) {
                return $typeConfig[$platform];
            }
            return $typeConfig['default'] ?? null;
        }

        // Default configuration
        if (isset($config[$property])) {
            if ($platform && isset($config[$property][$platform])) {
                return $config[$property][$platform];
            }
            return $config[$property]['default'] ?? $config[$property];
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
        $parts = [$siteConfig->getWebsiteTitle()];

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
            'image_height' => $height,
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
        foreach (['type', 'site_name', 'title', 'image', 'description', 'url'] as $key) {
            if (isset($properties[$key])) {
                $tags .= $this->constructMetaTag('property', "og:{$key}", 'content', $properties[$key]);
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
            'site_name' => $siteConfig->getWebsiteTitle(),
            'type' => $this->getPageConfig('opengraph', null, 'type') ?? 'website',
            'url' => Director::absoluteURL($owner->Link()),
        ];

        // Render meta tags
        $this->renderOpenGraphTags($tags, $properties);
        $this->renderTwitterTags($tags, $properties);
    }
}
