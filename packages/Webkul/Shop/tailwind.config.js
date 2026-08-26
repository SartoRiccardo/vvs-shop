/** @type {import('tailwindcss').Config} */

/**
 * Reads a brand color from the `--color-*` custom properties set at runtime
 * from Admin > Settings > General > Design > Theme Colors, falling back to
 * the CSS variable's own default (see src/Resources/assets/css/app.css)
 * when Tailwind's opacity modifier isn't used.
 */
function withOpacity(variable) {
    return ({ opacityValue }) => (
        opacityValue === undefined
            ? `rgb(var(${variable}))`
            : `rgb(var(${variable}) / ${opacityValue})`
    );
}

module.exports = {
    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1440px",
            },

            padding: {
                DEFAULT: "90px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            1180: "1180px",
            1060: "1060px",
            991: "991px",
            868: "868px",
        },

        extend: {
            colors: {
                navyBlue: withOpacity("--color-primary"),
                lightOrange: withOpacity("--color-bg-brand"),
                pageBg: withOpacity("--color-page-bg"),
                darkGreen: withOpacity("--color-success"),
                darkBlue: withOpacity("--color-link"),
                darkPink: withOpacity("--color-danger"),
                mutedText: withOpacity("--color-neutral"),
                // Border and Subtle Background are `color-mix()` results, not raw
                // R-G-B triplets, so they can't go through withOpacity()'s rgb(var() / a)
                // wrapper — reference them directly instead.
                divider: "var(--color-border)",
                subtleBg: "var(--color-subtle-bg)",
            },

            fontFamily: {
                poppins: ["Poppins", "sans-serif"],
                dmserif: ["DM Serif Display", "serif"],
            },
        }
    },

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,
        }
    ]
};
