export default {
    build: {
        lib: {
            entry: 'index.js',
            name: 'RktCheckoutJS',
            fileName: 'mahxcheckout-js',
            formats: ['iife'] // for browser usage
        },
        minify: 'esbuild',
        outDir: 'dist',
    }
}
