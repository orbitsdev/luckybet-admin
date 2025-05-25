import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import preset from './vendor/filament/support/tailwind.config.preset'
const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
export default {
     presets: [
        preset,



    ],
    content: [


        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',

        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    '50': '#eaf1fb',
                    '100': '#d4e3f7',
                    '200': '#a8c7ef',
                    '300': '#7cabe7',
                    '400': '#4f8fdc',
                    '500': '#337ab7', // Main Lucky Bet blue
                    '600': '#28629a',
                    '700': '#204d7a',
                    '800': '#18395a',
                    '900': '#132d47',
                    '950': '#0d1f2e',
                },
                red: {
                    '50': '#fff0f0',
                    '100': '#ffdddd',
                    '200': '#ffc1c1',
                    '300': '#ff9596',
                    '400': '#ff595a',
                    '500': '#ff2527',
                    '600': '#fc0204',
                    '700': '#d50002',
                    '800': '#b00405',
                    '900': '#910b0c',
                    '950': '#500001',
                },
            },
        },
    },

    plugins: [forms, typography],
};
