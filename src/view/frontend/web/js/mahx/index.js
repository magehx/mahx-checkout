import formDataJson from '../../../node_modules/form-data-json-convert/dist/form-data-json.es6'

import validator from './app/new_validator';

window.mahxCheckout = {
    validator: validator,
    utils: {
        formDataJson,
    },
};
