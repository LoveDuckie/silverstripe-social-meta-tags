<div align="center">

# silverstripe-social-meta-tags

</div>

This [SilverStripe](https://silverstripe.org/) module provides an easy-to-use solution for integrating metadata that ensures compatibility with sharing previews across social media platforms. With this module, you can automatically generate [Open Graph](https://ogp.me/), [Twitter Card](https://developer.x.com/en/docs/x-for-websites/cards/overview/abouts-cards), and other platform-specific metadata tags, allowing you to control how your content appears when shared. It’s ideal for enhancing social media engagement by delivering visually appealing and relevant previews when your website links are posted. This module is fully customizable and simple to integrate, making it a valuable tool for improving your site's visibility on social platforms.

---

<div align="center">

![image](https://user-images.githubusercontent.com/692378/160485859-58540384-9ef3-440c-b41f-f3e4ce15663e.png)

</div>

## Requirements

* SilverStripe ^4.0
* [Yarn](https://yarnpkg.com/lang/en/), [NodeJS](https://nodejs.org/en/) (6.x) and [npm](https://npmjs.com) (for building
  frontend assets)

## Installation

```shell
composer require loveduckie/silverstripe-social-meta-tags
```

## License

See [License](license.md)

We have included a 3-clause BSD license you can use as a default. We advocate for the BSD license as
it is one of the most permissive and open licenses.

## Documentation

* [Documentation readme](docs/en/readme.md)

Add links into your docs/<language> folder here unless your module only requires minimal documentation
in that case, add here and remove the docs folder. You might use this as a quick table of content if you
mhave multiple documentation pages.

## Example configuration (optional)

Refer to the example configuration below for altering the behaviour of this module.

```yaml

Page:
  config_option: true
  another_config:
    - item1
    - item2
  
```

## Maintainers

* Luc Shelton <lucshelton@gmail.com>

## Bugtracker

Bugs are tracked in the issues section of this repository. Before submitting an issue please read over
existing issues to ensure yours is unique.

If the issue does look like a new bug:

* Create a new issue
* Describe the steps required to reproduce your issue, and the expected outcome. Unit tests, screenshots
 and screencasts can help here.
* Describe your environment as detailed as possible: SilverStripe version, Browser, PHP version,
 Operating System, any installed SilverStripe modules.

Please report security issues to the module maintainers directly. Please don't file security issues in the bugtracker.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.
