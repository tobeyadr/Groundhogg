(function( $ ) {

    $.fn.wpghToolBar = function() {

        var html =
        '<wpgh-toolbar class="action-icons">' +
            '<div>' +
                '<span class="dashicons dashicons-admin-page"></span>' +
                '<span class="dashicons dashicons-move handle"></span>' +
                '<span class="dashicons dashicons-trash"></span>' +
            '</div>' +
        '</wpgh-toolbar>';

        this.each(function() {

            var row = $( this );

            if ( row.find( 'wpgh-toolbar' ).length === 0 )
                row.prepend( html );

        });

        return this;

    };

}( jQuery ));


var wpghEmailEditor;

( function( $, editor ) {

    wpghEmailEditor = {

        editor:     null,
        actions:    null,
        active:     null,
        alignment:  null,
        sidebar:    null,
        htmlcode:   null,


        /**
         * Initialize the editor
         */
        init: function () {

            this.editor  = $( '#email-body' );
            this.actions = $( '#editor-actions' );

            this.editor.on( 'click', function (e) {
                e.preventDefault();
                wpghEmailEditor.feed( e.target );
            } );

            this.editor.on( 'click', 'span.dashicons-admin-page', function ( e ) {
                e.preventDefault();
                wpghEmailEditor.duplicateBlock( e.target );
            });

            this.editor.on( 'click', 'span.dashicons-trash', function ( e ) {
                e.preventDefault();
                wpghEmailEditor.deleteBlock( e.target );
            });

            this.makeSortable();
            this.makeDraggable();

            /* Activate Spinner */
            $('form').on( 'submit', function( e ){
                wpghEmailEditor.save( e );
            });

            this.sidebar = new StickySidebar('.editor-actions-inner', {
                topSpacing: 32,
                bottomSpacing: 0
            });

            $( '.row' ).wpghToolBar();

            this.alignment = $( '#email-align' );
            this.alignment.on( 'change', function () {
                var email =  $( '#email-inside' );
                if ( $( this ).val() === 'left' ){
                    email.css( 'margin-left', '0' );
                    email.css( 'margin-right', 'auto' );
                } else {
                    email.css( 'margin-left', 'auto' );
                    email.css( 'margin-right', 'auto' );
                }
            } );

            /* Size the editor to full screen if being views in an Iframe. */
            if ( this.inFrame() ){
                // $( 'body' ).html( $( '#wpbody' ) );
                // $( '#screen-meta-links' ).remove();
                $( 'html' ).css( 'padding-top', 0 );
                $( '#wpcontent' ).css( 'margin', 0 );
                $( '#wpadminbar' ).addClass( 'hidden' );
                $( '#adminmenuwrap' ).addClass( 'hidden' );
                $( '#adminmenuback' ).addClass( 'hidden' );
                $( '#wpfooter' ).addClass( 'hidden' );
                $( '.title-wrap' ).css( 'display', 'none' );
                $( '.funnel-editor-header' ).css( 'top', 0 );
                $(  '.popup-save', parent.document ).on( 'click', function( e ){
                    wpghEmailEditor.save( e );
                } );
            }

            this.editorSizing();
            $( window ).resize(function() {
                wpghEmailEditor.editorSizing();
            });


            /*
                CODE EDITOR
             */

            this.htmlCode = CodeMirror.fromTextArea( document.getElementById("html-code"), {
                lineNumbers: true,
                mode: "text/html",
                matchBrackets: true,
                indentUnit: 4,
                specialChars: /[\u0000-\u001f\u007f-\u009f\u00ad\u061c\u200b-\u200f\u2028\u2029\ufeff]/
            });

            this.htmlCode.on('change', function() {
                $('#email-inside').html(wpghEmailEditor.htmlCode.doc.getValue());
            });

            this.htmlCode.setSize( null, this.editor.height() );

            $('#editor-toggle').change(function(){
                if ($(this).is(':checked')) {

                    $('#email-content').hide();
                    $('#html-editor').show();

                    $('wpgh-toolbar').remove();
                    wpghTextBlock.destroyEditor();

                    wpghEmailEditor.htmlCode.doc.setValue( html_beautify( $('#email-inside').html() ) );

                } else {
                    $( '.row' ).wpghToolBar();
                    $('#html-editor').hide();
                    $('#email-content').show();
                }
            }).change();

        },

        inFrame: function () {
            try {
                return window.self !== window.top;
            } catch (e) {
                return true;
            }
        },

        save: function ( e ) {

            e.preventDefault();

            $('.spinner').css('visibility','visible');

            $('.row').removeClass('active');

            $('wpgh-toolbar').remove();

            wpghTextBlock.destroyEditor();

            $('#content').val( $('#email-inside').html() );

            var fd = $('form').serialize();

            fd = fd +  '&action=gh_update_email';

            var ajaxCall = $.ajax({
                type: "post",
                url: ajaxurl,
                dataType: 'json',
                data: fd,
                success: function ( response ) {

                    // response = JSON.parse(response);
                    console.log( response );
                    $( '#notices' ).html( response.notices );
                    $( '.spinner' ).css( 'visibility','hidden' );
                    wpghEmailEditor.makeDismissible();

                }
            });

        },

        makeDismissible: function()
        {
            $( "<button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss This Notice</span></button>" ).appendTo( '.is-dismissible' );
            $( '.notice-dismiss' ).on( 'click', function ( e ) {
                $(this).parent().fadeOut( 500, function () {
                    $(this).remove();
                } );
            } )
        },

        editorSizing: function (){
            $('.funnel-editor-header').width( $('#poststuff').width() );
            $('#email-body').height( $('#postbox-container-1').height() - 87 );
        },

        /**
         * Make the blocks sortable
         */
        makeSortable: function(){
            $( ".email-sortable" ).sortable({
                placeholder: "sortable-placeholder",
                axis: 'y',
                start: function(e, ui){
                    ui.placeholder.height(ui.item.height());
                },
                handle: '.handle',
                stop: function (e, ui) {
                }
            });
        },

        /**
         * Make the blocks draggable
         */
        makeDraggable: function(){
            $( ".email-draggable" ).draggable({
                connectToSortable: ".email-sortable",
                helper: "clone",
                start: function ( e, ui ) {
                    var el = this;
                    var block_type = el.id.replace( '-block', '' );
                    var html = $( '.' + block_type + '-template' ).children().first().clone();
                    $('#temp-html').html( html );
                },
                stop: function ( e, ui ) {
                    $('#email-content').find('.email-draggable').replaceWith( $('#temp-html').html() );

                }
            });
        },

        /**
         * Show the block settings
         * Make the block active
         *
         * @param e
         */
        feed: function( e ) {

            // console.log( {e: e} );

            /* Make Current Block Active*/
            if ( e.parentNode === null ){
                return;
            }

            var block = $( e ).closest( '.row' );

            /* check if already active */
            if ( block.hasClass( 'active' ) ){
                return;
            }

            // console.log( {e: block} );

            if ( ! block.hasClass( 'row' ) ){

                this.editor.find( '.row' ).removeClass( 'active' );
                this.actions.find( '.postbox' ).addClass( 'hidden' );
                this.actions.find( '#email-editor' ).removeClass( 'hidden' );

                $(document).trigger( 'madeInactive' );

                return;

            }

            this.active = block;

            /* Make all blocks inactive */
            this.editor.find( '.row' ).removeClass( 'active' );
            block.addClass( 'active' );
            var blockType = block.attr( 'data-block' );

            if ( typeof blockType === 'undefined' && typeof block !== 'undefined' ){

                /* backwards compat */
                var $content = block.find( '.content-wrapper' );
                var classes = $content.attr( 'class' );
                blockType = /\w+_block/.exec( classes )[0];
                blockType = blockType.replace( '_block', '' );

            }

            /* Hide All Settings */
            this.actions.find( '.postbox' ).addClass( 'hidden' );
            /* Show block Settings */
            this.actions.find( '#' + blockType + '-block-editor' ).removeClass( 'hidden' );

            $(document).trigger( 'madeActive', [ block, blockType ] );
            // console.log( { block_type: blockType, block: block });

            this.sidebar.updateSticky();

        },

        /**
         * Delete a block
         *
         * @param e
         */
        deleteBlock: function( e ){
            $( e ).closest( '.row' ).remove();

        },

        /**
         * Duplicate a block
         *
         * @param e
         */
        duplicateBlock: function( e ){
            $(document).trigger( 'duplicateBlock' );
            $(e).closest('.row').removeClass('active');
            $(e).closest('.row').clone().insertAfter( $(e).closest('.row') );

        },

        getActive: function () {
            return this.active;
        }

    };

    $(function () {
        wpghEmailEditor.init();
    })

} )( jQuery );