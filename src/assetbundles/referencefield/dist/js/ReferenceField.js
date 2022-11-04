/**
 * ReferenceField - Craft Admin JS
 */

if( typeof Craft.ReferenceField === 'undefined' ) {
    Craft.ReferenceField = {}
}

Craft.ReferenceField.Fields = {
    init( config = {} ) {

        this.config = Object.assign({}, {

        }, config )

        document.querySelectorAll(`div.select.referenceField select, #entryType-field select.referenceField`).forEach(select => {
            this.showReferenceNote( select )
        })

        // also have to have mutation listener for slideouts:
        // "div.so-body"

        document.addEventListener('input', (event) => {
            if( event.target.classList.contains('referenceField') || event.target.parentElement.classList.contains('referenceField') ) {
                this.showReferenceNote( event.target )
            }
        }, false)
    },

    clickReferenceLink( link ) {
        const parent  = link.closest('div.note')

        const modalSettings = {
            image:     parent.dataset.image     ?? null,
            video:     parent.dataset.video     ?? null,
            html:      parent.dataset.html      ?? null,
            title:     parent.dataset.title     ?? null,
            moreUrl:   parent.dataset.moreUrl   ?? null,
            moreLabel: parent.dataset.moreLabel ?? null,
        }

        new Craft.ReferenceField.Modal( modalSettings )
    },

    showReferenceNote( select ) {

        const noteField = select.closest("div.field").parentElement.querySelector('div.referenceField__note')
    
        if(!noteField) return
    
        Array.from(noteField.children).map( (child) => {
            if( child.dataset.value == select.value ) {
                child.style.display = 'flex'
            } else {
                child.style.display = 'none'
            }
        })
    }
}

Craft.ReferenceField.Modal = Garnish.Modal.extend({

    init( modalContent = {} ) {
        
        Object.keys(modalContent).forEach( k => {
            if ( modalContent[k] === null || modalContent[k] == "" )
                delete modalContent[k]
        });

        const content = Object.assign({}, {
            image: null,
            video: null,
            html: null, 
            title: 'Reference Example',
            moreUrl: null,
            moreLabel: 'More Examples',
            moreTarget: '_blank',
        }, modalContent )
       
        this.$form = $('<form class="modal" method="post" accept-charset="UTF-8"/>').appendTo(Garnish.$bod);
        
        var $header = $('<div class="header"><h1>' + content.title + '</h1></header>').appendTo(this.$form);
        
        if( content.image ) {
            this.$body = $('<div class="body" style="padding: 24px; height: 100%; max-height: calc(100% - 120px); overflow:auto;"><div><img src="' + content.image +'"></div></div>').appendTo(this.$form);
        }

        if( content.video ) {
            console.log( content.video )
            this.$body = $('<div class="body" style="padding: 24px; height: 100%; max-height: calc(100% - 120px); overflow:auto;"><div style="display: flex; align-items: center; justify-content: center; max-height: 100%; height: 100%;"><video style="width:auto; max-height: 100%;" controls width="100%"><source src="' + content.video +'"></video></div></div>').appendTo(this.$form);
        }

        if( content.html ) {
            this.$body = $('<div class="body" style="padding: 24px; height: 100%; max-height: calc(100% - 120px); overflow:auto;"><div class="referenceField__modalContent">' + content.html + '</div></div>').appendTo(this.$form);
        }

        var $footer = $('<div class="footer"/>').appendTo(this.$form);

        var $mainBtnGroup = $('<div class="buttons right"/>').appendTo($footer);

        if( content.moreUrl ) {
            this.$moreBtn = $('<a href="' + content.moreUrl + '" target="'+ content.moreTarget +'" class="btn submit">' + Craft.t('referencefield', content.moreLabel) + '</a>').appendTo($mainBtnGroup);
            this.addListener(this.$moreBtn, 'click', 'onFadeOut');
        }

        this.$cancelBtn   = $('<input type="button" class="btn" value="' + Craft.t('referencefield', 'Close') + '"/>').appendTo($mainBtnGroup);
        this.addListener(this.$cancelBtn, 'click', 'onFadeOut');

        Craft.initUiElements(this.$form);

        this.base(this.$form);
    },

    onFadeOut() {
        this.$form.remove();
        this.$shade.remove();
    }
});

document.addEventListener('DOMContentLoaded', (event) => {
    Craft.ReferenceField.Fields.init()
});


(function($) {
    $(document).on('click', '.referenceField__note a', function(e) {
        e.preventDefault();
        Craft.ReferenceField.Fields.clickReferenceLink( e.target )
    });

    $(document).on('click', '.referenceField__note strong[data-action-mmtab]', function(e) {
        const tabTitle = $(this).data('mmtab');

        $(this)
            .closest('div.matrixblock.matrixmate-has-tabs')
            .find( 'ul.matrixmate-tabs li a:contains(' + tabTitle + ')' )
            [ 0 ].click()
    });

    $(document).on('click', '.referenceField__note strong[data-action-button]', function(e) {
        const buttonTitle = $(this).data('button');

        $(this)
            .closest('div.matrixblock .fields')
            .find( 'button:contains(' + buttonTitle + ')' )
            [ 0 ].click()
    });

})(jQuery);