import "vite/modulepreload-polyfill";
import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App.tsx";
import "@fontsource/inter/400.css";
import "@fontsource/inter/600.css";
import "@fontsource/inter/700.css";
import "./preflight.css";
import "./index.css";

ReactDOM.createRoot(document.getElementById("mwp-root")!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
);
