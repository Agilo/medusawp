/* eslint-disable no-undef */
/* eslint-disable @typescript-eslint/no-var-requires */
const defaultTheme = require("tailwindcss/defaultTheme");

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./admin/src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    fontFamily: {
      sans: ["Inter", ...defaultTheme.fontFamily.sans],
    },
    borderRadius: {
      xxs: "0.125rem",
      xs: "0.25rem",
      sm: "0.5rem",
      md: "1rem",
      full: "9999px",
    },
    colors: {
      blue: {
        200: "#DFEEF2",
        300: "#BADAE3",
      },
      red: {
        50: "#FEF4F1",
        200: "#FAD2C7",
        500: "#DF4718",
        700: "#9D3211",
      },
      orange: {
        50: "#FEFAF6",
        200: "#FADDC2",
        500: "#EE8E36",
      },
      green: {
        200: "#C8F9ED",
        500: "#13AF89",
      },
      grayscale: {
        white: "#fff",
        black: "#000",
        50: "#F5F7F7",
        100: "#EAEEEE",
        200: "#D5DCDD",
        300: "#BFCACC",
        400: "#A6AFB1",
        500: "#8C9496",
        600: "#73797B",
        700: "#595E5F",
        800: "#404344",
        900: "#262829",
        1000: "#0D0D0D",
      },
      transparent: "transparent",
    },
    fontSize: {
      "2xs": "0.75rem",
      xs: "0.875rem",
      sm: "1rem",
      md: "1.25rem",
      lg: "1.5rem",
      xl: "2rem",
      xxl: "2.5rem",
    },
    maxWidth: {
      none: "none",
      10: "2.5rem",
      120: "30rem",
      126: "31.5rem",
      148: "37rem",
      158: "39.5rem",
      288: "72rem",
    },
    minHeight: {
      14: "3.5rem",
      64: "16rem",
      225: "56.25rem",
    },
    boxShadow: {
      none: "0 0 #0000",
      md: "0px 0px 2.5rem -1.25rem rgba(0, 0, 0, 0.25)",
    },
    extend: {
      width: {
        15: "3.75rem",
        90: "22.5rem",
      },
      height: {
        15: "3.75rem",
      },
      lineHeight: {
        normal: "1.4",
        12: "3rem",
      },
      backgroundSize: {
        6: "1.5rem",
      },
      backgroundPosition: {
        "right-3": "right 0.75rem center",
      },
      zIndex: {
        "popover-content": 1000,
        "alert-dialog": 2000,
      },
      keyframes: {
        slideUpAndFade: {
          from: { opacity: 0, transform: "translateY(2px)" },
          to: { opacity: 1, transform: "translateY(0)" },
        },
        slideRightAndFade: {
          from: { opacity: 0, transform: "translateX(-2px)" },
          to: { opacity: 1, transform: "translateX(0)" },
        },
        slideDownAndFade: {
          from: { opacity: 0, transform: "translateY(-2px)" },
          to: { opacity: 1, transform: "translateY(0)" },
        },
        slideLeftAndFade: {
          from: { opacity: 0, transform: "translateX(2px)" },
          to: { opacity: 1, transform: "translateX(0)" },
        },
      },
      animation: {
        slideUpAndFade: "slideUpAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)",
        slideRightAndFade:
          "slideRightAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)",
        slideDownAndFade:
          "slideDownAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)",
        slideLeftAndFade:
          "slideLeftAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)",
      },
    },
  },
  plugins: [],
  corePlugins: {
    preflight: false,
  },
  important: true,
  prefix: "mwp-",
};
