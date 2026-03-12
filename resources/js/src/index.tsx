import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App";
import "../../css/app.css";
import { initTheme } from './theme';

// initialize theme before mounting the app so initial render matches
initTheme();

ReactDOM.createRoot(document.getElementById("app")!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
