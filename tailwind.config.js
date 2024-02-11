/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./templates/**/*.html.twig"],
    theme: {
        extend: {
            colors: {
                'brand': '#f97316', // orange-500
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('tailwindcss-hero-patterns'),
        require('@tailwindcss/typography'),
    ],
}
