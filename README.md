# SelectPlus Field

A JSON configured custom field type for Craft CMS 4+

    This is a Beta Release!

## Overview

This field presents similar to a standard Craft CMS [Dropdown Field](https://craftcms.com/docs/4.x/dropdown-fields.html).

Hiding under the hood are a bunch of features aimed at improving the content authoring experience and developer templating flexibility.


## Features

Think of SelectPlus like a CSS file for your dropdown fields.

Instead of _class names & style settings_, SelectPlus manages **select options & extra settings** in external JSON files.

The _"extra settings"_ associated with each `<option>` in the Dropdown field are entirely up to you.

Being configurable via `.json` is just the start, you can also:

 - Present **documentation for indiviudal options** directly inside Craft in the form of tooltips, modal windows and links to external documentation (i.e. style guides, step-by-step instructions, etc).

 - Store **extra information** along side individual field options, so that you can centralize logic and move it out of twig templates and into more visible JSON configs.

 - Create **virtual input fields** that allow content editors to fine-tune their choices while minimalizing the overhead of cluttered UI's and single-use custom fields.

![Sample Select Plus Field Layout](https://simplicate.nyc3.digitaloceanspaces.com/simplicate/assets/site/images/github/reference-field/reference-field-example.png)


## Configuration

Options for each SelectPlus field are stored in a JSON file within the Craft CMS `templates` folder rather than requiring them to be input via the Craft CMS Control Panel.

That means, when you want to add, change, or remove options from a Dropdown, you can do so by editing a JSON file in your code editor, rather than having to go into the Control Panel and edit or re-save individual fields.

This is particularly useful for fields that are used across many Matrix Block types, reducing the need to update the same options in many places, and reducing the risk of human error.


### Sample `.json` files

Sample JSON config files can be found in the `etc` folder of this repository.


## Twig Templating

Each option in a SelectPlus field allows for additional data to be associated with it that you can use in place of creating logic in Twig to match the value of a dropdown field.

Within the JSON configuration for a field, you can have an attribute called `settings` that allows for any number of key-value pairs (strings, arrays, etc) to be passed along into Twig.

For example, if you had a SelectPlus field `fieldName` with this configuration:

```
{
    "label": "Option One",
    "value": "one",
    "settings": {
        "usefulText": "This is some useful text"
    }
}
```

You could then reference the **settings.usefulText** in your Twig templates like: `{{ entry.fieldName.usefulText }}`

This of course would only work if you "Option One" was selected in the `fieldName` field. If a different option were selected, the settings variables and their contents might be different (that's up to you!).

Why would this ever be useful? Plenty of reasons!

    Explain With Examples...


### Instant Updates

Because changes to the JSON file are immediately reflected in all instances of the field, you can use this to easily tweak any number of settings across many templates or entires without having to go into the Control Panel and edit/resave individual records.


## Inline Option Documentation

While Craft CMS allows for us to associate instructions with each field, these only apply to the field as a whole, and not to individual options.

SelectPlus fields allow for the creation of inline documentation for each option, which can be accessed via tooltips, modals, or links to external resources.

Modal content can consist of escaped HTML strings stored within each JSON config or to file path to a Twig files within your `templates` folder.


    TBD: Outline Different documentation settings in the JSON config


## Advanced Features

The SelectPlus field also includes a number of advanced features that can be used to create a more powerful and flexible content authoring experience.


## Virtual Input Fields

Like a regular Dropdown field, the SelectPlus field stores the value of the `<option>` selected by the user.

However, it can also be used to store multiple additional inputs fields associated with each option.

This can be useful for situations where the selected option has fine-tuning "follow-up questions", especially if the extra inputs are presentational rather than content model in nature, and likely don't warrant their own [Custom Field](https://craftcms.com/docs/4.x/fields.html).

Nobody likes a cluttered content model, and this can help keep things tidy.

Available input fields include:

- Plain Text
- Number
- Radio Group
- Lightswitch
- Dropdown

See `etc/all-inputs-sample.json` for an example of how to configure each of these fields.


## Dynamic Configuration

SelectPlus only suggests `.json` files when attempting to autocomplete the file path within the field configuration screen.

However, SelectPlus it is happy to accept any file type that can be parsed as a Twig template provided:

1) You enter the file path manually without the aid of autocomplete (the horror!)
2) The file outputs a valid JSON array of options

SelectPlus provides an `element` variable which can be accessed in any Twig tags or commands from within any SelectPlus config file (regardless of file extension).

This `element` variable contains information about the element that the field is attached to, as well as that elements' owner (if applicable as in cases of matrix fields).

These are the fields that are available on the `element` variable:

`element.section` - The handle for the [Section](https://craftcms.com/docs/4.x/entries.html#sections) associated with the direct element that the field is attached to (if applicable).

`element.type` - The handle for the [Entry Type](https://craftcms.com/docs/4.x/entries.html#entry-types) associated with the direct element that the field is attached to (if applicable).

`element.class` - The class name of the element that the field is attached to. i.e `craft\elements\MatrixBlock` or `craft\elements\Entry`.

`element.id` - The ID of the element that the field is attached to.

Additionally, for cases where the SelectPlus field is attached to a Matrix block, the `element` variable will also contain an `owner` variable which contains the same fields as the `element` variable, but for the owner of the Matrix block.

This means that instead of configuring a SelectPlus field with a static `.json` file, you may consider some of the following techniques.


### Configuration with `.twig` instead of `.json`

**field-config.twig**

```
{% set fieldOptions = [
    {
        "label": "Option One",
        "value": "one",
        "settings": {
            "template" : "_layouts/basic.twig",
            "fizz": true
        }
    },{
        "label": "Option Two",
        "value": "two",
        "settings": {
            "template" : "_layouts/basic.twig",
            "fizz": false
        }
    }] %}

{{ fieldOptions | json_encode }}
```

### Configuration with Twig inside `.json`

e.g. **field-config.json**

```
[
    {
        "label": "Option One",
        "value": "one",
        "settings": {
            "template" : "_layouts/basic.twig",
            "fizz": true
        }
    }
    {% if element.section != 'blog' %}
        ,{
            "label": "Option Two",
            "value": "two",
            "settings": {
                "template" : "_layouts/basic.twig",
                "fizz": false
            }
        }
    {% endif %}
]
```

### Other ~~less sane~~ adventureous methods of configuration

Using `.twig` as a router that points to regular static `.json` files using the Twig [source function](https://twig.symfony.com/doc/3.x/functions/source.html)

e.g. **layout-field-router.twig**

```
{% set section = element.owner.section ?? element.section ?? 'default' %}
{{ source("_config/layout-#{section}.json") }}
```

----

Alternatively, you could use a `.json` file that includes common elements from a `.twig` file using the Twig [block function](https://twig.symfony.com/doc/3.x/functions/block.html)

e.g. **field-config.json**

```
[
    {
        "label": "Option One",
        "value": "one",
        "inputs": [
            {{ block( 'align', '_config/field-inputs.twig' ) }},
            {{ block( 'size',  '_config/field-inputs.twig' ) }},
        ]
    }
   ,{
        "label": "Option Two",
        "value": "two",
        "inputs": [
            {{ block( 'align', '_config/field-inputs.twig' ) }},
            {{ block( 'size',  '_config/field-inputs.twig' ) }},
        ]
    }
]
```

.. and **field-inputs.twig**

```
{% block align %}
    {
        "label": "Alignment",
        "field": "align",
        "type" : "select",
        "value": "left",
        "options": [
            "text-left"  : "Left",
            "text-center": "Center",
            "text-right" : "Right"
        ]
    }
{% endblock %}

{% block size %}
    {
        "label": "Font Size",
        "field": "fontSize",
        "type" : "select",
        "value": "text-base",
        "options": [
            "text-xs"  : "Extra Small",
            "text-sm"  : "Small",
            "text-base": "Normal",
            "text-lg"  : "Large",
            "text-xl"  : "Extra Large"
        ]
    }
{% endblock %}
```

## Button-only mode

It will also be possible to tell a SelectPlus field to operate in **Button Only** mode where if can be configured with a single "option" with multiple virtual input fields which can be accessed by clicking on a button.

You can name the button using the `buttonOnlyLabel` field.

**\-Button only mode is coming soon.**


## Installation

This early release version is not currently available on the Craft Plugin store, and can only be installed via Composer.

**Requires Craft CMS 4+**

Run `composer require simplicateca/selectplus` and then enable the plugin from "Settings > Plugins"


## Credits

Brought to you by  [simplicate.ca](https://simplicate.ca)

Thanks to the [Design Tokens](https://plugins.craftcms.com/designtokens?craft4) field by Trendy Minds and the [Matrix Field Preview](https://plugins.craftcms.com/matrix-field-preview?craft4) plugin by Feral for inspiration.

[Bug reports welcome](https://github.com/simplicateca/craft-selectplus-field/issues).