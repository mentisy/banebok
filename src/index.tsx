import React from "react";
import ReactDOM from "react-dom/client";
import "./index.css";
import "milligram/dist/milligram.min.css";
import App from "./App";
import { ErrorBoundary } from "react-error-boundary";

const root = ReactDOM.createRoot(document.getElementById("root") as HTMLElement);
root.render(
    <React.StrictMode>
        <ErrorBoundary fallback={<div>Something went wrong</div>}>
            <App />
        </ErrorBoundary>
    </React.StrictMode>,
);
