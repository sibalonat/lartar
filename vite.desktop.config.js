import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    clearScreen: false,
    server: {
        port: 50003,
        strictPort: true,
        host: true,
        hmr: {
            host: 'localhost',
            protocol: 'ws'
        },
    },
});
