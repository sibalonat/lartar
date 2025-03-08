import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

/** @type {import('vite').UserConfig} */
export default defineConfig( {
    plugins : [
        laravel( {
            input : 'resources/js/app.js',
            refresh : true,
        } ),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    clearScreen : false,
    server : {
        port : 50003
    }
} );
