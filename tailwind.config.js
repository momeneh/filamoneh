const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
    "./app/Filament/**/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/views/components/**/*.blade.php",
    "./resources/views/layouts/**/*.blade.php",
    "./vendor/filament/**/*.blade.php",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        danger: colors.rose,
        primary: colors.amber,
        success: colors.emerald,
        warning: colors.orange,
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
} 