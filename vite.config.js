import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { readFileSync } from "fs";

// Read assets configuration from single source of truth
// To add/remove assets, edit resources/assets.json only
const assets = JSON.parse(readFileSync("./resources/assets.json", "utf-8"));

// Flatten all entry points for Vite
const inputs = [
    ...Object.values(assets.css),
    ...assets.js.shared,
    ...assets.js.app,
    ...assets.js.auth,
];

export default defineConfig({
    plugins: [
        laravel({
            input: inputs,
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
