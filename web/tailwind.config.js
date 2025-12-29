/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        midnight: '#050714',
        aurora: '#8cf1ff',
        blush: '#ff7fb0',
        sand: '#ffe7d1',
        slate: '#0f172a',
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'Bahnschrift', 'sans-serif'],
        body: ['"IBM Plex Sans"', 'Segoe UI', 'sans-serif'],
      },
      boxShadow: {
        halo: '0 30px 120px rgba(92, 175, 255, 0.35)',
      },
    },
  },
  plugins: [],
};


