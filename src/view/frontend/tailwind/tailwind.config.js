module.exports = {
    content: [
        './input.css',
        '../templates/**/*.phtml',
        '../layout/*.xml',
    ],
    safelist: [
        'input-error',
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
