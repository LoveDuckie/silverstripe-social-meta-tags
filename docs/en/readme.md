# Documentation

Find below an example configuration file that you can copy and paste into your project after installing this package as a dependency.

```yaml
---
Name: portfolio-silverstripe-social-media-tags
After: loveduckie-silverstripe-social-media-tags
---
LoveDuckie\SilverStripe\SocialMetaTags\Extensions\SocialMediaTagsExtension:
  config:
    title:
      default: "Title"
    description:
      default: "Content"
    image:
      default: "ImpressionImage"
    image_width: 1200
    image_height: 627
    description_limit: 300
    description_limit_twitter: 200
    locale: en_GB
    opengraph:
      type:
        default: website
    twitter:
      site: "@YourAliasGoesHere"
      creator: "@YourAliasGoesHere"
      card: summary_large_image
      description_limit: 200
      image_alt_limit: 420
      image_height: 500
      image_width: 958
    social_platforms:
      pinterest:
        image_height: 1200
        image_width: 627
      linkedin:
        image_height: 1200
        image_width: 627
      facebook:
        image_height: 1200
        image_width: 630
      twitter:
        image_height: 800
        image_width: 418
  page_types:
    Portfolio\Models\HomePage:
      description:
        default: "Content"
    SilverStripe\Blog\Model\BlogPost:
      title:
        default: "Title"
      description:
        default: "Content"
      image:
        default: "FeaturedImage"
        facebook: "FeaturedImageFacebook"
        twitter: "FeaturedImageTwitter"
        linkedin: "FeaturedImageLinkedIn"
    Portfolio\Models\CareerJobCompanyPage:
      title:
        default: "Title"
      description:
        default: "Content"
      image:
        default: "CompanyLogo"
    Portfolio\Models\CareerJobPage:
      title:
        default: "Title"
      image:
        default: "CompanyLogo"
    Portfolio\Models\EventPage:
      image:
        default: "Impression"
    Portfolio\Models\ProjectPage:
      twitter:
        card: summary_large_image
      title:
        default: "Title"
      description:
        default: "Content"
      image:
        default: "Impression"
        facebook: "ImpressionFacebook"
        twitter: "ImpressionTwitter"
        linkedin: "ImpressionLinkedIn"
  types:
    SilverStripe\Blog\Model\BlogPost: "article"
    Portfolio\Models\AboutPage: "profile"

```
