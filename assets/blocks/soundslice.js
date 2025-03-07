wp.blocks.registerBlockType('custom/soundslice', {
    title: 'Soundslice Mini Player Embed',
    icon: 'video-alt3',
    category: 'embed',
    attributes: {
        soundsliceId: { type: 'string', default: '' }
    },
    edit: function(props) {
        function updateId(event) {
            props.setAttributes({ soundsliceId: event.target.value });
        }

        return wp.element.createElement('div', { style: { border: '1px solid #ccc', padding: '10px' } },
            wp.element.createElement('p', { style: { fontWeight: 'bold' } }, '🎵 Soundslice Player Embed'),

            wp.element.createElement('input', {
                type: 'text',
                placeholder: 'Enter Soundslice ID...',
                value: props.attributes.soundsliceId,
                onChange: updateId,
                style: { width: '100%', padding: '5px', marginBottom: '10px' }
            }),

            props.attributes.soundsliceId
                ? wp.element.createElement('iframe', {
                    src: `https://www.soundslice.com/slices/${props.attributes.soundsliceId}/embed-mini/`,
                    width: '100%',
                    height: '293',
                    frameBorder: '0'
                })
                : wp.element.createElement('div', {
                    style: {
                        width: '100%',
                        height: '293px',
                        backgroundColor: '#f4f4f4',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: '16px',
                        color: '#666'
                    }
                }, '🔲 Soundslice Preview (Enter an ID above)')
        );
    },

    save: function() {
        return null; // Uses PHP `render_callback`
    }
});