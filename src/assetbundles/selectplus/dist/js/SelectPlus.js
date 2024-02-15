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

    current: null,
    namespace: null,

    init( modalContent = {} ) {

        this.namespace = modalContent.namespace ?? null
        this.current   = modalContent.current   ?? null

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
        Craft.SelectPlusField.Fields.saveModalInput(
            '.selectPlusField__modalContent',
            this.namespace,
            this.current
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
            Craft.SelectPlusField.Fields.selectizeObserver( field )
            this.toggleVisibleOptionFields( field.closest(".selectPlusField"), field.value )
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
        const field     = link.closest('.selectPlusField') ?? null
        const namespace = field.dataset.namespace ?? null
        const current   = link.dataset.option ?? null
        const fieldName = link.dataset.field ?? 'Field'
        const title     = link.dataset.title ?? fieldName + ' Settings'
        const wrapper   = document.querySelector( '#' + namespace + '-inputs-' + current )

        if( wrapper )
        {
            new Craft.SelectPlusField.InputModal({
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
                if( mutations[0].target ) Craft.SelectPlusField.Fields.onChange( mutations[0].target )
            }).observe(
                field, { childList: true }
            )
        }
    },


    // when the value of the field changes,
    onChange( select )
    {
        const field     = select.closest(".selectPlusField")
        const namespace = field.dataset.namespace ?? null
        const optionVal = select.value ?? null
        const fields    = '#' + namespace + "-inputs-" + optionVal

        if( optionVal ) {
            this.toggleVisibleOptionFields( field, optionVal )
            this.setJsonFromFields( fields, namespace )
        }
    },


    toggleVisibleOptionFields( parent, value )
    {
        this.setDisplayStyle( parent, '.selectPlusField__inputs', value )
        this.setDisplayStyle( parent, '.selectPlusField__docs', value )
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


    setJsonFromFields( fields, namespace )
    {
        const jsonInputField = document.querySelector('#' + namespace + '-json')
        if( jsonInputField ) {
            jsonInputField.value = JSON.stringify( this.serializeInputs( fields ) )
        }
    },


    saveModalInput( fields, namespace, optionVal )
    {
        this.setJsonFromFields( fields, namespace )

        const inputWrapper = document.querySelector( '#' + namespace + "-inputs-" + optionVal )
        if( inputWrapper ) {
            inputWrapper.innerHTML = document.querySelector(fields).cloneNode(true).innerHTML

            const elements = inputWrapper.querySelectorAll('input, select, textarea');
            for( i=0; i<elements.length; i++ ) {
                if( elements[i].getAttribute( 'type') !== 'hidden' ) {
                    elements[i].disabled = true;
                }
            }
        }
    },


    serializeInputs( fieldContiner )
    {
        const fields = document.querySelector( fieldContiner ) ?? null
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