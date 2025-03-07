wp.blocks.registerBlockType('custom/soundslice', {
    title: 'Soundslice Mini PLayer Embed',
    icon: 'video-alt3',
    category: 'embed',
    attributes: {
        soundsliceId: { type: 'string', default: '' }
    },
    edit: function(props) {
        function updateId(event) {
            props.setAttributes({ soundsliceId: event.target.value });
        }
        return wp.element.createElement('div', {},
            wp.element.createElement('input', {
                type: 'text',
                placeholder: 'Enter Soundslice ID...',
                value: props.attributes.soundsliceId,
                onChange: updateId
            })
        );
    },
    save: function() {
        return null; 
    }
});