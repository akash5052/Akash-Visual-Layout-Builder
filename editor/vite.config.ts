import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [react()],
  base: "./",
  worker: {
    format: "es",
  },
  build: {
    outDir: "../assets/build",
    emptyOutDir: true,
    modulePreload: false,
    rollupOptions: {
      output: {
        format: "iife",
        name: "avWebStudioBuilderApp",
        entryFileNames: "index.js",
        assetFileNames: "index.[ext]",
        inlineDynamicImports: true,
      },
    },
  },
});