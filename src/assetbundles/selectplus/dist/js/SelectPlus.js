Craft.SelectPlusField = typeof Craft.SelectPlusField === 'undefined' ? {} : Craft.SelectPlusField;


/**
 * SelectPlusField - Documentation Modal
 */
Craft.SelectPlusField.DocumentationModal = Garnish.Modal.extend({

    init( modalContent = {} ) {

        Object.keys(modalContent).forEach( k => {
            if ( modalContent[k] === null || modalContent[k] == "" )
                delete modalContent[k]
        });

        const content = Object.assign({}, {
            title: 'Documentation',
            more : 'More',
            url  : null,
            html : null,
        }, modalContent )

        this.$form = $('<form class="modal fitted" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);

        var $header = $('<div class="header"><h1>' + content.title + '</h1></header>').appendTo(this.$form);

        if( content.html ) {
            this.$body = $('<div class="body" style="padding: 24px; height: 100%; max-height: calc(100% - 120px); overflow:auto; max-width: 740px;"><div class="selectPlusField__modalContent">' + content.html + '</div></div>').appendTo(this.$form);
        }

        var $footer = $('<div class="footer"/>').appendTo(this.$form);

        var $mainBtnGroup = $('<div class="buttons right"/>').appendTo($footer);

        if( content.url ) {
            this.$moreBtn = $('<a href="' + content.url + '" target="_blank" class="btn submit">' + Craft.t('selectplus', content.more) + '</a>').appendTo($mainBtnGroup);
            this.addListener(this.$moreBtn, 'click', 'onFadeOut');
        }

        this.$cancelBtn   = $('<input type="button" class="btn" value="' + Craft.t('app', 'Close') + '"/>').appendTo($mainBtnGroup);
        this.addListener(this.$cancelBtn, 'click', 'onFadeOut');

        Craft.initUiElements(this.$form);

        this.base(this.$form);
    },

    onFadeOut() {
        this.$form.remove();
        this.$shade.remove();
    }
});


/**
 * SelectPlusField - Settings Modal
 */
Craft.SelectPlusField.InputModal = Garnish.Modal.extend({

    field    : null,
    current  : null,
    namespace: null,

    init( modalContent = {} ) {

        this.namespace = modalContent.namespace ?? null
        this.current   = modalContent.current   ?? null
        this.field     = modalContent.field     ?? null

        const content = Object.assign({}, {
            html : null,
            title: 'Field Settings',
            json : '{}',
        }, modalContent )

        // modal header and body
        this.$form = $('<form class="modal fitted" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
        $('<div class="header"><h1>' + content.title + '</h1></header>').appendTo(this.$form);
        $('<div class="body" style="padding: 24px; overflow:auto; max-width: 620px;"><div class="selectPlusField__modalContent">' + content.html + '</div></div>').appendTo(this.$form);

        // footer and buttons
        var $footer = $('<div class="footer"/>').appendTo(this.$form);
        var $mainBtnGroup = $('<div class="buttons right"/>').appendTo($footer);
        this.$saveBtn = $('<input type="button" class="btn" value="' + Craft.t('app', 'Close') + '"/>').appendTo($mainBtnGroup);
        this.addListener(this.$saveBtn, 'click', 'onFadeOut');

        // init the modal
        Craft.initUiElements(this.$form);
        this.base(this.$form);

        // make sure the form values are correct
        this.setStartingFieldValues( content.json )
    },


    // make sure when the form is moved from the body to the modal,
    // that all the inputs are populated properly
    setStartingFieldValues( jsonString )
    {
        const json    = JSON.parse( jsonString )
        const modal   = document.querySelector( '.selectPlusField__modalContent' )
        const fields  = modal.querySelectorAll('input, select, textarea')

        if( json && fields ) {
            fields.forEach( function (input) {
                input.disabled = false
                if( input.name ) {
                    if( match = input.name.match(/\[([^[\]]+)\]$/) ) {
                        if( input.type === 'checkbox' || input.type === 'radio' ) {
                            input.checked = ( json[match[1]] == input.value )
                        } else {
                            input.value = json[match[1]]
                        }
                    }
                }
            });
        }

        // remove the `noteditable` class from lightswitches which prevents user interaction
        const switchs = modal.querySelectorAll('button.lightswitch.noteditable')
        if( switchs ) {
            switchs.forEach( function (lightswitch) {
                lightswitch.classList.remove('noteditable')
            });
        }

        // remove the disabled class from radiogroups for the same reason
        const radiogroups = modal.querySelectorAll('div.radio-group.disabled')
        if( radiogroups ) {
            radiogroups.forEach( function (group) {
                group.classList.remove('disabled')
            });
        }
    },

    onFadeOut()
    {
        Craft.SelectPlusField.Fields.saveVirtuals(
            '.selectPlusField__modalContent',
            this.namespace
        )

        this.$form.remove();
        this.$shade.remove();
    }
});



/**
 * SelectPlusField - Primary JS
 */
Craft.SelectPlusField.Fields = {
    init() {
        this.monitorSelectizeFields()
        this.jqueryListeners()
    },

    // jQuery is <fart> but it makes adding the click listeners way easier,
    // particularly when it comes to not having to think about adding listeners
    // for dynamically added rows (matrix, etc.)
    jqueryListeners()
    {
        (function($) {
            $(document).on('keypress click', '.selectPlusField__inputs a.settings', function(e) {
                e.preventDefault();
                Craft.SelectPlusField.Fields.triggerSettingsModal( e.target )
            });

            $(document).on('keypress click', '.selectPlusField__docs a', function(e) {
                e.preventDefault();
                Craft.SelectPlusField.Fields.triggerDocumentationModal( e.target )
            });
        })(jQuery);
    },

    // Adds onChange() listeners to existing selectize fields, and creates
    // listeners to do the same when new selectize fields added to the page
    monitorSelectizeFields()
    {
        selectizeFields = document.querySelectorAll('.selectPlusField select.selectized')
        selectizeFields.forEach((field) => {
            const selectplus = field.closest('.selectPlusField') ?? null
            Craft.SelectPlusField.Fields.toggleVisibleOptionFields( selectplus, field.value )
            Craft.SelectPlusField.Fields.selectizeObserver( field )
        });


        // create listeners for new selectize fields added to the page (matrix, etc.)
        new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if( mutation.addedNodes.length > 0 ) {
                    mutation.addedNodes.forEach(function(node) {
                        if( node.classList
                            && node.classList.contains('selectized')
                            && node.tagName === 'SELECT'
                            && node.parentNode.classList.contains('selectPlusField__selectize') ) {
                            Craft.SelectPlusField.Fields.selectizeObserver( node )
                        }
                    })
                }
            })
        }).observe(
            document.body, { childList: true, subtree: true }
        )
    },


    // Loads the documentation modal
    triggerDocumentationModal( link ) {

        const parent  = link.closest('div.note')

        const modalSettings = {
            title: parent.dataset.title ?? null,
            more:  parent.dataset.more  ?? null,
            url:   parent.dataset.url   ?? null,
            html:  parent.dataset.html  ?? null,
        }

        new Craft.SelectPlusField.DocumentationModal( modalSettings )
    },


    // Loads the settings modal and manages the serialization
    // and display of individual setting fields
    triggerSettingsModal( link )
    {
        const selectplus = link.closest('.selectPlusField') ?? null
        const namespace  = selectplus.dataset.namespace ?? null
        const current    = link.dataset.option ?? null
        const fieldName  = link.dataset.field ?? 'Field'
        const title      = link.dataset.title ?? fieldName + ' Settings'
        const wrapper    = document.querySelector( '#' + namespace + '-inputs-' + current )

        if( wrapper )
        {
            new Craft.SelectPlusField.InputModal({
                field    : selectplus,
                title    : title,
                current  : current,
                namespace: namespace,
                html     : wrapper.cloneNode(true).innerHTML,
                json     : document.querySelector('#' + namespace + '-json').value ?? '{}'
            })

            wrapper.innerHTML = '' // if we don't do this, radiogroups get fucky
        }
    },


    // observe a selectize field for changes
    selectizeObserver( field ) {
        if( field ) {
            new MutationObserver(function(mutations) {
                if( mutations[0].target ) Craft.SelectPlusField.Fields.onSelectizeChange( mutations[0].target )
            }).observe( field, { childList: true } )

            const selectplus = field.closest('.selectPlusField') ?? null
            const namespace  = selectplus.dataset.namespace ?? null
            const selected   = field.value ?? null
            const fieldModal = '#' + namespace + "-inputs-" + selected

            if( fieldModal ) {
                this.setJsonFromFields( namespace, this.serializeInputs( fieldModal ) )
            }
        }
    },

    // when the value of the selectize field changes
    onSelectizeChange( field )
    {
        const selectplus = field.closest(".selectPlusField")
        const namespace  = selectplus.dataset.namespace ?? null
        const optionVal  = field.value ?? null

        if( optionVal ) {
            this.toggleVisibleOptionFields( selectplus, optionVal )
            this.preserveMatchingVirtualFieldsValuesOnChange( namespace )
        }
    },


    toggleVisibleOptionFields( parent, value )
    {
        this.setDisplayStyle( parent, '.selectPlusField__inputs', value )
        this.setDisplayStyle( parent, '.selectPlusField__docs',   value )
    },


    setDisplayStyle( parent, selector, value )
    {
        const elements = parent.querySelectorAll( selector );
        if( elements && elements[0] && elements[0].children ) {
            Array.from(elements[0].children).forEach((element) => {
                element.style.display = (element.dataset.option == value) ? 'flex' : 'none';
            });
        }
    },

    // when changing the value of the primary Selectize dropdown field, if the
    // newly selected option has some of the same virtual input fields as the
    // previous option (and allowing the same value to be saved), we want to
    // preserve those values between selecting dropdown changes.
    preserveMatchingVirtualFieldsValuesOnChange( fields, namespace )
    {
        // here's the general gist of what's going on here.
        //
        // `jsonInputField` is the hidden input field that contains the JSON
        // values we want to save for the virtual fields associated with the
        // currently selected selectize option.
        //
        // `inputs` contains a JSON representation of all the input fields
        // and options that are available for the currently selected option.
        //
        // `oldVals` contains the virtual field values for the PREVIOUS
        //  selected option in the primary selectize dropdown field.
        //
        // `newVals` contains the virtual field values for the CURRENT
        //  selected option in the primary selectize dropdown field.
        //
        // we're going to loop through jsonOptions, and for each field, if the
        // field is in both `oldVals` and `newVals` we're going to set the value
        // in `newVals` to match `oldVals`.
        //
        // For fields with multiple values (checkboxes, select, etc), we also need
        // to verify that the `oldVal` is available in the `jsonOptions` array,
        // otherwise we'll use the default value.
        const virtualFieldJSON = document.querySelector('#' + namespace + '-json')
        if( virtualFieldJSON )
        {

            const optionVal = document.querySelector( '#' + namespace + " select" ).value ?? null
            const fields    = '#' + namespace + "-inputs-" + optionVal

            const jsonOpts  = document.querySelector(fields + '-json')
            const inputs    = jsonOpts ? JSON.parse( jsonOpts.value ) : []
            const oldVals   = JSON.parse( virtualFieldJSON.value )
            const newVals   = this.serializeInputs( fields )

            inputs.forEach( function (input) {
                if( ( input.field ?? null ) && ( oldVals[input.field] ?? null) )
                {
                    // if this field has options, we need to make sure the old value
                    // is still available in the new options array.
                    if( input.options ?? null )
                    {
                        let options = input.options
                        let oldval  = oldVals[input.field]
                        let allowed = false;

                        if (Array.isArray(options)) {
                            allowed = options.some(option => option.value === oldval);
                        } else {
                            allowed = options.hasOwnProperty(oldval);
                        }

                        if( allowed ) {
                            newVals[input.field] = oldval
                        }

                    // if the field doesn't have options (text, lightswitch, etc),
                    // we can just map the old value to the new value.
                    } else {
                        newVals[input.field] = oldVals[input.field]
                    }
                }
            })

            this.setJsonFromFields( namespace, newVals )
        }
    },


    setJsonFromFields( namespace, fieldValues )
    {
        const virtualFieldJSON = document.querySelector('#' + namespace + '-json')
        if( virtualFieldJSON ) {

            const optionVal = document.querySelector( '.selectPlusField[data-namespace="' + namespace + '"] select' ).value ?? null
            const fields    = '#' + namespace + "-inputs-" + optionVal
            const jsonOpts  = document.querySelector(fields + '-json')
            const inputs    = jsonOpts ? JSON.parse( jsonOpts.value ) : []

            // When a virtual field has an options array, it can be defined two ways.
            //
            // 1) As an object, like this:
            //
            //      "options" : {
            //          "normal": "Normal",
            //          "large" : "Large",
            //      },
            //
            // 2) As an array of objects, like this:
            //
            //      "options" : [
            //          { "label" : "Auto Link",
            //            "value" : "autolink",
            //            "extravalue" : "something" }
            //         ,{ "label" : "Summary Modal",
            //            "value" : "modal",
            //            "othervalue" : "anything" }
            //      ],
            // ]
            //
            // In the case of the first example, label & value are explicit, and there can be no extra field values associated
            // with any selected option. In the second example, we can include additional fields values we want saved when
            // that option is selected.
            //
            // This code is what figured that out:
            inputs.forEach( function (input) {
                if( ( input.field ?? null ) && ( fieldValues[input.field] ?? null ) && ( input.options ?? null ) ) {
                    let options = input.options
                    if (Array.isArray(options)) {
                        let selopt = options.find(option => option.value === fieldValues[input.field]);
                        let extras = Craft.SelectPlusField.Fields.findExtraOptionData( selopt )
                        if( Object.keys(extras) ) {
                            fieldValues = { ...fieldValues, ...extras };
                        }
                    }
                }
            })

            virtualFieldJSON.value = JSON.stringify( fieldValues )
        }
    },


    findExtraOptionData( option )
    {
        if( typeof option === "object" ) {
            for ( let key in option ) {
                if ( key === "value" || key === "label" ) {
                    delete option[key]
                }
            }

            return option ?? null
        }

        return null
    },


    saveVirtuals( fieldContainer, namespace )
    {
        // save the fields
        this.setJsonFromFields( namespace, this.serializeInputs( fieldContainer ) )

        // close the modal and revert cloned vitual field form
        const optionVal    = document.querySelector( '.selectPlusField[data-namespace="' + namespace + '"] select' ).value ?? null
        const inputWrapper = document.querySelector( '#' + namespace + "-inputs-" + optionVal )
        if( inputWrapper ) {
            inputWrapper.innerHTML = document.querySelector(fieldContainer).cloneNode(true).innerHTML
            const elements = inputWrapper.querySelectorAll('input, select, textarea');
            for( i=0; i<elements.length; i++ ) {
                if( elements[i].getAttribute( 'type') !== 'hidden' ) {
                    elements[i].disabled = true;
                }
            }
        }
    },


    serializeInputs( fieldContainer )
    {
        const fields = document.querySelector( fieldContainer ) ?? null
        const inputs = fields ? fields.querySelectorAll('input, select, textarea') : null

        if( !fields || !inputs ) return {};

        let data = {};
        inputs.forEach( function (input) {
            if( input.name ) {
                if( match = input.name.match(/\[([^[\]]+)\]$/) ) {
                    if( input.type === 'checkbox' || input.type === 'radio' ) {
                        data[match[1]] = input.checked ? input.value : data[match[1]];
                    } else {
                        data[match[1]] = input.value;
                    }
                }
            }
        });

        return data;
    }
}


document.addEventListener('DOMContentLoaded', () => {
    Craft.SelectPlusField.Fields.init();
});
