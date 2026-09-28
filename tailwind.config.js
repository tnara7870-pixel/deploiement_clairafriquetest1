/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2E7D52',
          dark:    '#1A4731',
          light:   '#4CAF80',
          pale:    '#D6EDDF',
          bg:      '#F7FAF8',
        },
        amber: {
          ca: '#E8A020',
        },
        paper: {
          DEFAULT: '#FBF9F4',
          line:    '#E8E2D3',
        },
        ink: '#1F2A24',
      },
      fontFamily: {
        sans: ['"IBM Plex Sans"', 'sans-serif'],
        serif: ['Fraunces', 'serif'],
        mono: ['"IBM Plex Mono"', 'monospace'],
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}