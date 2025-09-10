module.exports = {
    content: [
        './input.css',
        // '../templates/**/*.phtml',
        // '../layout/*.xml',
        '../../../../../**/src/view/frontend/templates/**/*.phtml',
        '../../../../../**/src/view/frontend/layout/*.xml',
    ],
    safelist: [
        'input-error', 'select-error'
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('daisyui'),
    ],
    daisyui: {
        themes: ['fantasy'], // only include the fantasy theme
        darkTheme: false,  // disable dark mode completely
    },
};
