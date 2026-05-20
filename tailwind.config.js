/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Http/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#f0f4ff',
                    100: '#dbe4f8',
                    200: '#b8caf0',
                    300: '#89a8e0',
                    400: '#5b82cc',
                    500: '#3563b0',
                    600: '#1e3f7a',
                    700: '#152d5c',
                    800: '#0f1d35',
                    900: '#0a1628',
                },
            },
            fontFamily: {
                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
