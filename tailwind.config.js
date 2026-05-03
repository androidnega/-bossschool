/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#3E8A8E',
                secondary: '#7FA58C',
                accent: '#B7C49A',
                soft: '#E6EEC9',
                page: '#FFFFFF',
                'page-soft': '#F8FAF7',
                /** Marketing homepage background — light neutral grey (reference swatch) */
                canvas: '#f0f0f0',
                // Warm single-fill surfaces for cards (no gradients)
                'card-mist': '#f4f1ec',
                'card-sage': '#ecf0e8',
                'card-sand': '#f3efe8',
                'card-shell': '#efeae4',
            },
            fontFamily: {
                sans: [
                    'ui-sans-serif',
                    'system-ui',
                    'Segoe UI',
                    'Roboto',
                    'Helvetica Neue',
                    'Arial',
                    'Noto Sans',
                    'sans-serif',
                    'Apple Color Emoji',
                    'Segoe UI Emoji',
                    'Segoe UI Symbol',
                    'Noto Color Emoji',
                ],
            },
        },
    },
    plugins: [],
};
