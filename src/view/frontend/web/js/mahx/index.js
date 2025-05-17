import formDataJson from '../../../node_modules/form-data-json-convert/dist/form-data-json.es6'

import mahxCheckoutValidator from './app/validator';

window.mahxCheckout = {
    validator: mahxCheckoutValidator,
    utils: {
        formDataJson,
    },
};
