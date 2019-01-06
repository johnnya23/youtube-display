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
        icon: 'video-alt3', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
        category: 'common', // The category of the block.

        edit: function(props) {
            var attributes = props.attributes

            var yt_list_id = props.attributes.yt_list_id
            var query_max = props.attributes.query_max
            var query_offset = props.attributes.query_offset
            var item_font_length = props.attributes.item_font_length
            var item_gutter = props.attributes.item_gutter
            var item_spacing = props.attributes.item_spacing
            var lg_cols = props.attributes.lg_cols
            var md_cols = props.attributes.md_cols
            var sm_cols = props.attributes.sm_cols
            var xs_cols = props.attributes.xs_cols

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
                        el('p', {}, i18n.__('The values below are all optional and can be left blank to follow the options set on the plugin "Settings page" - Settings > YouTube Playlists with Schema')),

                        el('p', {}, i18n.__('Color settings can be overridden by adding a custom class under "Advanced" (below), then overwriting with custom css')),

                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Max to Display (blank for all)'),
                            value: query_max,
                            onChange: function(newquery_max) {
                                props.setAttributes({
                                    query_max: newquery_max
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Display list offset (blank for 0)'),
                            value: query_offset,
                            onChange: function(newquery_offset) {
                                props.setAttributes({
                                    query_offset: newquery_offset
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('The maximun number of characters for YouTube item titles'),
                            value: item_font_length,
                            onChange: function(newitem_font_length) {
                                props.setAttributes({
                                    item_font_length: newitem_font_length
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Grid horizontal spacing - in px between YouTube grid items - best results even number between 0 and 30'),
                            value: item_gutter,
                            onChange: function(newitem_gutter) {
                                props.setAttributes({
                                    item_gutter: newitem_gutter
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Grid vertical spacing - in px between YouTube grid items'),
                            value: item_spacing,
                            onChange: function(newitem_spacing) {
                                props.setAttributes({
                                    item_spacing: newitem_spacing
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Large device columns - for window width 1200+ px - blank uses value from setting below.'),
                            value: lg_cols,
                            onChange: function(newlg_cols) {
                                props.setAttributes({
                                    lg_cols: newlg_cols
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Medium device columns - for window width 992+ px - blank uses value from setting below.'),
                            value: md_cols,
                            onChange: function(newmd_cols) {
                                props.setAttributes({
                                    md_cols: newmd_cols
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Small device columns - for window width 768+ px - blank uses value from setting below.'),
                            value: sm_cols,
                            onChange: function(newsm_cols) {
                                props.setAttributes({
                                    sm_cols: newsm_cols
                                })
                            }
                        }),
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Extra small device columns - for window width -768 px.'),
                            value: xs_cols,
                            onChange: function(newxs_cols) {
                                props.setAttributes({
                                    xs_cols: newxs_cols
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