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
        icon: 'video', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
        category: 'common', // The category of the block.
        attributes: { // Necessary for saving block content.
            /*title: {
                type: 'array',
                source: 'children',
                selector: 'h3'
            },*/
            alignment: {
                type: 'string',
                default: 'center'
            },
            width: {
                type: 'number'
            },
            video_id: {
                type: 'text'
            }
        },

        edit: function(props) {
            var attributes = props.attributes
            var title = props.attributes.title
            var alignment = props.attributes.alignment
            var width = props.attributes.width
            var video_id = props.attributes.video_id

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
                        // Width number field option.
                        el(TextControl, {
                            type: 'number',
                            label: i18n.__('Width (use unit - % strongly recommended)'),
                            value: width,
                            onChange: function(newwidth) {
                                props.setAttributes({
                                    width: newwidth
                                })
                            }
                        })

                    )
                ),
                /*el('div', {
                        className: props.className
                    },
                    el('div', {
                            className: attributes.mediaID ? 'organic-profile-image image-active' : 'organic-profile-image image-inactive',
                            style: attributes.mediaID ? {
                                backgroundImage: 'url(' + attributes.mediaURL + ')'
                            } : {}
                        },
                        el(MediaUpload, {
                            onSelect: onSelectImage,
                            type: 'image',
                            value: attributes.mediaID,
                            render: function(obj) {
                                return el(components.Button, {
                                    className: attributes.mediaID ? 'image-button' : 'button button-large',
                                    onClick: obj.open
                                }, !attributes.mediaID ? i18n.__('Upload Image') : el('img', {
                                    src: attributes.mediaURL
                                }))
                            }
                        })
                    ),
                    el('div', {
                            className: 'organic-profile-content',
                            style: {
                                textAlign: alignment
                            }
                        },
                        el(RichText, {
                            key: 'editable',
                            tagName: 'h3',
                            placeholder: 'Profile Name',
                            keepPlaceholderOnFocus: true,
                            value: attributes.title,
                            onChange: function(newTitle) {
                                props.setAttributes({
                                    title: newTitle
                                })
                            }
                        }),
                        el(RichText, {
                            tagName: 'h5',
                            placeholder: i18n.__('Subtitle'),
                            keepPlaceholderOnFocus: true,
                            value: attributes.subtitle,
                            onChange: function(newSubtitle) {
                                props.setAttributes({
                                    subtitle: newSubtitle
                                })
                            }
                        }),
                        el(RichText, {
                            key: 'editable',
                            tagName: 'p',
                            placeholder: i18n.__('Write a brief bio...'),
                            keepPlaceholderOnFocus: true,
                            value: attributes.bio,
                            onChange: function(newBio) {
                                props.setAttributes({
                                    bio: newBio
                                })
                            }
                        }),
                        el('div', {
                                className: 'organic-profile-social'
                            },
                            attributes.facebookURL && el('a', {
                                    className: 'social-link',
                                    href: attributes.facebookURL,
                                    target: '_blank'
                                },
                                el('i', {
                                    className: 'fa fa-facebook'
                                })
                            ),
                            attributes.twitterURL && el('a', {
                                    className: 'social-link',
                                    href: attributes.twitterURL,
                                    target: '_blank'
                                },
                                el('i', {
                                    className: 'fa fa-twitter'
                                })
                            ),
                            attributes.instagramURL && el('a', {
                                    className: 'social-link',
                                    href: attributes.instagramURL,
                                    target: '_blank'
                                },
                                el('i', {
                                    className: 'fa fa-instagram'
                                })
                            ),
                            attributes.linkedURL && el('a', {
                                    className: 'social-link',
                                    href: attributes.linkedURL,
                                    target: '_blank'
                                },
                                el('i', {
                                    className: 'fa fa-linkedin'
                                })
                            ),
                            attributes.emailAddress && el('a', {
                                    className: 'social-link',
                                    href: 'mailto:' + attributes.emailAddress,
                                    target: '_blank'
                                },
                                el('i', {
                                    className: 'fa fa-envelope'
                                })
                            )
                        )
                    )
                )*/
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