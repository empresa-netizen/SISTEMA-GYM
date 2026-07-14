import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import fs from 'fs-extra';
import path from 'path';

const folder = {
    src: 'resources/',
    src_assets: 'resources/',
    dist: 'public/',
    dist_assets: 'public/build/',
};

const isProd = process.env.NODE_ENV === 'production';

export default defineConfig({
    build: {
        manifest: true,
        outDir: 'public/build/',
        emptyOutDir: true,
        cssCodeSplit: true,
        minify: 'esbuild',
        sourcemap: false,
        reportCompressedSize: false,
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    const name = assetInfo.name || 'asset';
                    if (name.split('.').pop() === 'css') {
                        return 'css/[name].min.css';
                    }

                    return 'icons/' + name;
                },
                entryFileNames: 'js/[name].js',
                chunkFileNames: 'js/[name]-[hash].js',
            },
        },
    },
    esbuild: {
        // Remove console.* e debugger no build de produção
        drop: isProd ? ['console', 'debugger'] : [],
        legalComments: 'none',
    },
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: [
                    'import',
                    'mixed-decls',
                    'color-functions',
                    'global-builtin',
                ],
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/scss/bootstrap.scss',
                'resources/scss/icons.scss',
                'resources/scss/app.scss',
                'resources/scss/custom.scss',
                'resources/js/chat-realtime.js',
            ],
            refresh: [
                ...refreshPaths,
                'resources/views/**',
            ],
        }),
        {
            name: 'copy-specific-packages',
            async writeBundle() {
                // Laravel espera public/build/manifest.json; Vite 5 pode emitir em .vite/
                try {
                    const viteManifest = path.join(folder.dist_assets, '.vite', 'manifest.json');
                    const laravelManifest = path.join(folder.dist_assets, 'manifest.json');
                    if (await fs.pathExists(viteManifest)) {
                        await fs.copy(viteManifest, laravelManifest);
                    }
                } catch (error) {
                    console.error('Error copying Vite manifest:', error);
                }

                try {
                    await Promise.all([
                        fs.copy(folder.src_assets + 'fonts', folder.dist_assets + 'fonts'),
                        fs.copy(folder.src_assets + 'images', folder.dist_assets + 'images'),
                        fs.copy(folder.src_assets + 'js', folder.dist_assets + 'js'),
                        fs.copy(folder.src_assets + 'json', folder.dist_assets + 'json'),
                    ]);
                } catch (error) {
                    console.error('Error copying assets:', error);
                }

                const outputPath = path.resolve(__dirname, folder.dist_assets);
                const configPath = path.resolve(__dirname, 'package-copy-config.json');

                try {
                    const configContent = await fs.readFile(configPath, 'utf-8');
                    const { packagesToCopy } = JSON.parse(configContent);

                    for (const packageName of packagesToCopy) {
                        const destPackagePath = path.join(outputPath, 'libs', packageName);
                        const sourcePath = fs.existsSync(path.join(__dirname, 'node_modules', packageName + '/dist'))
                            ? path.join(__dirname, 'node_modules', packageName + '/dist')
                            : path.join(__dirname, 'node_modules', packageName);

                        try {
                            await fs.access(sourcePath, fs.constants.F_OK);
                            await fs.copy(sourcePath, destPackagePath);
                        } catch {
                            console.error(`Package ${packageName} does not exist.`);
                        }
                    }
                } catch (error) {
                    console.error('Error copying and renaming packages:', error);
                }
            },
        },
    ],
});
