# Select Plus Custom Field

## JSON Driven Custom Field for Craft CMS 4+

This custom field type acts like a `<select>` dropdown with extra features to improve both content author experience and frontend templating flexibility.

![Sample Select Plus Field Layout](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/reference-field-example.png)


## Features

- Field options & values are controlled by JSON files stored in your `templates` folder.

- Documentation can be associated with each option in the JSON file and made accessible inside Craft to help content editors better understand their options.

- Supported documentation types include: images, local video, HTML, and links to external sites.

- Store additional configuration information alongside each option that can be accessed from inside your twig templates.

### Reference content loaded inside modals

![Modal Screenshot](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/modal-screenshot.png)


## Usage

### Creating the Field



## Installation

This early release version is not currently available on the Craft Plugin store, and can only be installed via Composer.

Run `composer require simplicateca/selectplus-field` and enable the plugin from "Settings > Plugins"


## Compatibility

Currently working in Craft CMS 4.0

## Roadmap

-   Get Entry Type takeover working inside Quick Edit / Slide Outs
-   Better process for dealing with fields with a value that no longer exists inside the reference JSON
-   Allow use of Reference links to trigger modal windows in Template UI Twig files
-   Improve a11y support & aria labeling for fields when made visible

## Credits

Brought to you by  [simplicate.ca](https://www.simplicate.ca/). Written by  [Steve Comrie](https://github.com/stevecomrie).

Thanks to the [Design Tokens](https://plugins.craftcms.com/designtokens?craft4) field by Trendy Minds and the [Matrix Field Preview](https://plugins.craftcms.com/matrix-field-preview?craft4) plugin by Feral for inspiration to create this field.

## Contributing

Bug reports, feedback, and pull requests welcome.