(function(blocks, editor, components, i18n, element) {

    var el = wp.element.createElement
    var registerBlockType = wp.blocks.registerBlockType
    var RichText = wp.editor.RichText
    var BlockControls = wp.editor.BlockControls
    var AlignmentToolbar = wp.editor.AlignmentToolbar
    var MediaUpload = wp.editor.MediaUpload
    var InspectorControls = wp.editor.InspectorControls
    var TextControl = components.TextControl

    registerBlockType('jmayt-list/block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
        title: i18n.__('List YouTube Responsive Videos'), // The title of our block.
        description: i18n.__('A custom block for displaying lists responsive YouTube videos.'), // The description of our block.
        icon: 'video', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
        category: 'common', // The category of the block.

        edit: function(props) {
            var attributes = props.attributes
            var yt_list_id = props.attributes.yt_list_id
            var query_max = props.attributes.query_max
            var ServerSideRender = wp.components.ServerSideRender

            function onChangeAlignment(newAlignment) {
                props.setAttributes({
                    alignment: newAlignment
                })
            }

            return [
                el(BlockControls, {
                    key: 'controls'
                }),
                el(InspectorControls, {
                        key: 'inspector'
                    }, // Display the block options in the inspector panel.
                    el(components.PanelBody, {
                            title: i18n.__('YouTube Video Values'),
                            className: 'jmaty-values',
                            initialOpen: true
                        },
                        el('p', {}, i18n.__('Values for display of lists of responsive YouTube Videos.')),
                        // Video id text field option.
                        el(TextControl, {
                            type: 'text',
                            label: i18n.__('YouTube List ID'),
                            value: yt_list_id,
                            onChange: function(newyt_list_id) {
                                props.setAttributes({
                                    yt_list_id: newyt_list_id
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'text',
                            label: i18n.__('Max to Diplay (blank for all)'),
                            value: query_max,
                            onChange: function(newquery_max) {
                                props.setAttributes({
                                    query_max: newquery_max
                                })
                            }
                        })

                    )
                ),
                el(ServerSideRender, {
                    block: 'jmayt-list/block',
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