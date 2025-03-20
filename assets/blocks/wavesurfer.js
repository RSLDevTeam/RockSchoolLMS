wp.blocks.registerBlockType('custom/wavesurfer', {
    title: 'Wavesurfer Audio Player',
    icon: 'media-audio',
    category: 'embed',
    attributes: {
        audioUrl: { type: 'string', default: '' }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        const { audioUrl } = attributes;
        const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
        const { Button } = wp.components;

        function onSelectAudio(media) {
            setAttributes({ audioUrl: media.url });
        }

        return wp.element.createElement('div', { style: { border: '1px solid #ccc', padding: '10px' } },
            wp.element.createElement('p', { style: { fontWeight: 'bold' } }, '🎵 Wavesurfer Audio Player'),

            wp.element.createElement(MediaUploadCheck, {},
                wp.element.createElement(MediaUpload, {
                    onSelect: onSelectAudio,
                    allowedTypes: ['audio'],
                    render: ({ open }) => wp.element.createElement(Button, { onClick: open, isPrimary: true },
                        audioUrl ? 'Change Audio File' : 'Upload Audio File'
                    )
                })
            ),

            audioUrl
                ? wp.element.createElement('p', {}, `📂 Selected Audio: ${audioUrl}`)
                : wp.element.createElement('p', {}, 'No audio file selected.')
        );
    },

    save: function() {
        return null; // Uses PHP `render_callback`
    }
});