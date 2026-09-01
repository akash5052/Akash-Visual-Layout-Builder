import ReactDOM from "react-dom/client";
import App from "./App";
import "./styles/editor.css";
import "./styles/animations.css";

const rootElement = document.getElementById("epb-root");

if (rootElement) {
  ReactDOM.createRoot(rootElement).render(<App />);
}
