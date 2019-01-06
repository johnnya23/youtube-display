(function(blocks, editor, components, i18n, element) {

    var el = wp.element.createElement
    var registerBlockType = wp.blocks.registerBlockType
    var RichText = wp.editor.RichText
    var BlockControls = wp.editor.BlockControls
    var AlignmentToolbar = wp.editor.AlignmentToolbar
    var MediaUpload = wp.editor.MediaUpload
    var InspectorControls = wp.editor.InspectorControls
    var TextControl = components.TextControl

    registerBlockType('jmayt-single/block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
        title: i18n.__('Single YouTube Responsive Video'), // The title of our block.
        description: i18n.__('A custom block for displaying responsive YouTube videos.'), // The description of our block.
        icon: 'video-alt3', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
        category: 'common', // The category of the block.

        edit: function(props) {
            var attributes = props.attributes
            var alignment = props.attributes.alignment
            var video_id = props.attributes.video_id
            var width = props.attributes.width
            var item_font_length = props.attributes.item_font_length
            var start = props.attributes.start
            var ServerSideRender = wp.components.ServerSideRender

            function onChangeAlignment(newAlignment) {
                props.setAttributes({
                    alignment: newAlignment
                })
            }

            return [
                el(BlockControls, {
                        key: 'controls'
                    }, // Display controls when the block is clicked on.

                    // Display alignment toolbar within block controls.
                    el(AlignmentToolbar, {
                        value: alignment,
                        onChange: onChangeAlignment
                    })
                ),
                el(InspectorControls, {
                        key: 'inspector'
                    }, // Display the block options in the inspector panel.
                    el(components.PanelBody, {
                            title: i18n.__('YouTube Video Values'),
                            className: 'jmaty-values',
                            initialOpen: true
                        },
                        el('p', {}, i18n.__('Values for display of single responsive YouTube Video.')),
                        // Video id text field option.
                        el(TextControl, {
                            type: 'text',
                            label: i18n.__('YouTube Video ID'),
                            value: video_id,
                            onChange: function(newvideo_id) {
                                props.setAttributes({
                                    video_id: newvideo_id
                                })
                            }
                        }),
                        el('p', {}, i18n.__('The values below are all optional and can be left blank to follow the options set on the plugin "Settings page" - Settings > YouTube Playlists with Schema')),

                        el('p', {}, i18n.__('Color settings can be overridden by adding a custom class under "Advanced" (below), then overwriting with custom css')),
                        // Width number field option.
                        el(TextControl, {
                            type: 'text',
                            label: i18n.__('Width (use unit - % strongly recommended)'),
                            value: width,
                            onChange: function(newwidth) {
                                props.setAttributes({
                                    width: newwidth
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('The maximum number of characters for YouTube item titles'),
                            value: item_font_length,
                            onChange: function(newitem_font_length) {
                                props.setAttributes({
                                    item_font_length: newitem_font_length
                                })
                            }
                        }),
                        // Width number field option.
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Start time in seconds (optional on a single item by item basis)'),
                            value: start,
                            onChange: function(newstart) {
                                props.setAttributes({
                                    start: newstart
                                })
                            }
                        })

                    )
                ),
                el(ServerSideRender, {
                    block: 'jmayt-single/block',
                    attributes: props.attributes,
                })
            ]
        },

        save: function() {
            return null;
        },
    })

})(
    window.wp.blocks,
    window.wp.editor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element
)