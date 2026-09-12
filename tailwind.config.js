import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
        './app/View/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#2563EB',
                    600: '#1D4ED8',
                    700: '#1E40AF',
                    800: '#1E3A8A',
                    900: '#1E3A8A',
                },
                accent: {
                    50: '#FEFCE8',
                    100: '#FEF9C3',
                    400: '#FACC15',
                    500: '#EAB308',
                    600: '#CA8A04',
                },
                danger: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    500: '#DC2626',
                    600: '#B91C1C',
                    700: '#991B1B',
                },
                success: {
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    500: '#16A34A',
                    600: '#15803D',
                    700: '#166534',
                },
                warning: {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    500: '#F59E0B',
                    600: '#D97706',
                    700: '#B45309',
                },
            },
            minHeight: {
                touch: '44px',
            },
            spacing: {
                touch: '44px',
            },
        },
    },
    plugins: [forms],
};
