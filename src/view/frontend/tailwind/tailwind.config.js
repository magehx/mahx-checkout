module.exports = {
    content: [
        '../templates/**/*.phtml',
        '../layout/*.xml',
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('daisyui'),
    ],
};
