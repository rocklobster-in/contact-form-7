import { createBlock } from '@wordpress/blocks';

const transforms = {
  from: [
    {
      type: 'shortcode',
      tag: 'contact-form-7',
      attributes: {
        id: {
          type: 'integer',
          shortcode: ( { named: { id } } ) => {
            return parseInt( id );
          },
        },
        title: {
          type: 'string',
          shortcode: ( { named: { title } } ) => {
            return title;
          },
        },
      },
    },
  ],
  to: [
    {
      type: 'block',
      blocks: [ 'core/shortcode' ],
      transform: ( attributes ) => {
        return createBlock(
          'core/shortcode',
          {
            text: `[contact-form-7 id="${ attributes.id }" title="${ attributes.title }"]`,
          }
        );
      },
    },
  ],
};

export default transforms;
