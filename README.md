# Reference Field 

**Supercharge your dropdown fields!**

This is custom dropdown field type for Craft CMS 4 with some extra feature to improve both content author experience and frontend templating flexibility.

![Sample Reference Field Layout](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/reference-field-example.png)


## Feature Summary

- Field options & values are controlled by JSON files stored in your `templates` folder.

- Documentation can be associated with each option in the JSON file and made accessible inside Craft to help content editors better understand their options.

- Supported documentation types include: images, local video, HTML, and links to external sites.

- Store additional configuration information alongside each option that can be accessed from inside your twig templates.

### Reference content loaded inside modals

![enter image description here](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/modal-screenshot.png)
   

## Setup

### Creating the Field

1. Select the "Dropdown (reference)" from the `Field Type` dropdown

2. The `Reference JSON` field will autofill all JSON files found in your templates directory. Start typing the name of the file you want to use to configure this field and select the one you want from the list.

![enter image description here](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/setup.png)


### JSON File Structure

This is the most basic JSON file that is necessary for this field type to work properly:


    [{
	    "label": "Transparent Background",
	    "value": "bgNone"
	 },{
		 "label": "Dark Red Background",
		 "value": "bgRed"
	}]

Each record must have a `label` and `value` field.

Once you start using the field, try not to change the `value` field. Elements using the old value are not automatically updated the way they are when using a native Craft Dropdown field type,


## Reference UI Options

Additional fields can be included in the JSON struture to add references & documentation inside the editing UI based on the Dropdown option that is currently selected.

#### Note

A short description that will appear beneath the dropdown field when the option it's associated with has been selected. This is the most frequently used reference field.

	"note": "Use this element sparingly or it will lose it's effectiveness"
![enter image description here](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/reference-note-highlight.png)

#### Image

Adds a link within the reference note to view a large photo or screenshot. The image will appear inside a modal window without leaving the CMS.

	"image": "/assets/static/media-text-left.jpg"

#### Direct Video

Adds a link within the reference note to watch a video inside the modal window.

	"video": "/assets/static/how-to-edit-articles.mp4"

#### HTML

Adds a link within the reference note to read a longer block inside the modal window.

	"html": "<h1>Try not to go to crazy here</h1> <p>I'm not sure how much text is too much</p>"

#### Modal Title

The text content to appear in the title bar of the modal when it's loaded to view image/video/html reference content.

	"title": "Layout Example for Media Block - Text Left / Media Right"

#### More URL

If you have a link to an external documentation (pattern library, Google doc, Figma file, etc), you can use this field to add a button inside the modal for users to see access that information directly.

	"moreUrl": "/site-docs/components/media/#textLeft"

#### More Label

The text label to use on the `moreURL` button.

	"moreLabel": "Additional Documentation"

![enter image description here](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/modal-screenshot-annotated.png)

### Adding Reference UI to JSON Config

You can add the above reference fields to a flat JSON structure like this:

	{
		"label"    : "Text Left / Media Right",
		"value"    : "textLeft",
		"note"     : "50/50 split. Text on the left, media player on the right.",
		"title"    : "Layout Example for Media Block - Text Left / Media Right",
		"image"    : "/assets/static/media-text-left.jpg",
		"moreUrl"  : "/site-docs/components/media/#textLeft",
		"moreLabel": "Additional Examples"
	}

Or you can include them inside a nested `reference` array like this:

	{
		"label" : "Text Left / Media Right",
		"value" : "textLeft",
		"reference": {
			"note" : "50/50 split. Text on the left, media player on the right.",
			"title" : "Layout Example for Media Block - Text Left / Media Right",
			"image" : "/assets/static/media-text-left.jpg",
			"moreUrl" : "/site-docs/components/media/#textLeft",
			"moreLabel": "Additional Examples"
		}
	}

Preference is given to the explicit `reference` fields over any fields with matching names in the root. 

Currently only supports adding one of: `image/video/HTML` fields to a single record. Trying to add more than one might result in _wonkiness_.

## Adding Extra Data to JSON Config

You can store other data in the JSON config that can be accessed from your twig templates.

For example, your reference JSON for a "Background" field might look like this:

	[{
		"label"    : "Transparent / No Color",
		"value"    : "bgNone",
		"note"     : "Transparent backgrounds on content elements allow you to see selected page background",
		"bgColor"  : "transparent",
		"bgHex"    : "",
		"headColor": "yellow-600",
		"textColor": "stone-900",
		"linkColor": "red-800"
	},{
		"label"    : "Dark Red Background",
		"value"    : "bgRed",
		"note"     : "Use when you need the content item to standout. Text changes to white.",
		"bgColor"  : "red-800",
		"bgHex"    : "#991b1b",
		"headColor": "white",
		"textColor": "white",
		"linkColor": "yellow-600"
	}]

## Accessing Reference JSON from Twig Templates

From your twig templates you can access this additional content in a number of ways:

### Access the currently selected value

i.e. "value" : "bgRed"

	{{ entry.backgroundReferenceField }}
	{# Outputs the value for the selected option (i.e. bgNone, bgRed, etc) #}

### Access a specific field of the currently selected option

i.e. "bgColor" : "red-800"

	{{ entry.backgroundReferenceField.reference('bgColor') }}
	{# Outputs the bgColor for the selected option (i.e. transparent, red-800, etc) #}

### Access the entire reference for the currently selected option

	{% set background = entry.backgroundReferenceField.reference() %}
	{{ background.textColor }}
	{# Outputs the textColor for the selected option (i.e. stone-900, white, etc) #}

## Nesting JSON config

If you're storing a lot of additional information alongside each option, you can nest your JSON structure to keep things cleaner.

	[{
		"label": "Transparent / No Color",
		"value": "bgNone",
		"reference": {
			"note": "Transparent backgrounds on content elements allow you to see selected page background"
		}, 
		"bg": {
			"color": "transparent",
			"hex": ""
		},
		"text": {
			"head": "yellow-600",
			"text": "stone-900",
			"link": "red-800"
		}
	},{
		"label": "Dark Red Background",
		"value": "bgRed",
		"reference": {
			"note": "Use when you need the content item to standout. Text changes to white."
		},
		"bg": {
			"color": "red-800",
			"hex": "#991b1b"
		},
		"text": {
			"head": "white",
			"text": "white",
			"link": "yellow-600"
		}
	}]

Note: When nesting like this, you can't access a nested value directly using the twig function. You have to go through the full reference or the parent reference.

For example, if you wanted to access the `link text color` value in the above configuration you would have to use:

	{% set background = entry.backgroundReferenceField.reference() %}
	{{ background.bg.link }}
	{# Outputs "red-800", "yellow-600", etc. #}

or:

	{% set backgroundBg = entry.backgroundReferenceField.reference('bg') %}
	{{ bg.link }}
	{# Outputs "red-800", "yellow-600", etc. #}


## Entry Type Takeover

This plugin also allows you to takeover the "Entry Type" select field, and replace it with a Reference Field that provides more information about each option.

This also moves the Entry Type field out of the right hand info section, and places it more prominently above the content editing area.

![enter image description here](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/entry-type-takeover.png)

### Takeover Setup

Currently, the plugin looks for a file in `cms/templates/_sections/entrytype.json` to find reference information about each of your entry types.

#### Sample `entrytypes.json` config

	[{
		"type": "basic",
		"section": "pages",
		"note": "Very versatile single column layout. Configurable hero & footer. No sidebar.",
		"image": "/assets/reference/basic-page-layout.jpg",
		"moreUrl": "/site-docs/layouts/basic"
	},{
		"type": "withSidebar",
		"section": "pages",
		"note": "Less layout flexibility than basic page. Configurable hero, footer & sidebar.",
		"image": "/assets/reference/sidebar-page-layout.jpg",
		"moreUrl": "/site-docs/layouts/sidebar"
	},{
		"type": "featuredNews",
		"section": "news",
		"note": "A larger more prominent template for long-form featured articles",
		"image": "/assets/reference/news-featured-layout.jpg",
		"moreUrl": "/site-docs/layouts/featured-news"
	}]

Entry types from all sections can be managed through this one file.

## Installation

This beta version is not currently available on the Craft Plugin store, and can only be installed via Composer.

Run `composer require simplicateca/reference-fields` and enable the plugin from "Settings > Plugins"


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