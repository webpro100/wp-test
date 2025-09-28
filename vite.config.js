// vite.config.js
import { defineConfig, loadEnv } from 'vite';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'url';

/**
 * Detect LocalWP certs directory depending on OS
 */
function localwpCertBase() {
  if (process.platform === 'darwin') {
    // LocalWP on macOS
    return path.join(
      process.env.HOME,
      'Library',
      'Application Support',
      'Local',
      'run',
      'router',
      'nginx',
      'certs'
    );
  }
  if (process.platform === 'win32') {
    // LocalWP on Windows
    return path.join(process.env.APPDATA, 'Local', 'Local', 'certs');
  }
  // Default for Linux or others
  return path.join(process.env.HOME || '', '.config', 'Local', 'certs');
}

/**
 * Small debounce helper so multiple rapid writes trigger a single reload
 */
function debounce(fn, ms = 120) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), ms);
  };
}

export default defineConfig(({ mode, command }) => {
  const env = loadEnv(mode, process.cwd(), '');

  // WP site domain must match your LocalWP domain (e.g. testwpsite.local)
  const domain = env.VITE_DOMAIN || 'testwp1.local';
  const port = Number(env.VITE_PORT || 5173);

  // Resolve absolute theme dir (where this config lives)
  const __filename = fileURLToPath(import.meta.url);
  const __dirname = path.dirname(__filename);
  const themeDir = __dirname; // adjust if vite.config.js is not at theme root

  // Load certs for HTTPS if available
  const certBase = localwpCertBase();
  const https = (() => {
    const keyPath = path.join(certBase, `${domain}.key`);
    const crtPath = path.join(certBase, `${domain}.crt`);
    if (fs.existsSync(keyPath) && fs.existsSync(crtPath)) {
      return {
        key: fs.readFileSync(keyPath),
        cert: fs.readFileSync(crtPath),
      };
    }
    return undefined;
  })();

  return {
    base: '',

    css: {
      // Useful when inspecting source lines in devtools
      devSourcemap: true,
    },

    build: {
      manifest: true,
      outDir: 'dist',
      emptyOutDir: true,
      assetsDir: 'assets',
      assetsInlineLimit: 0,
      esbuild: { legalComments: 'none' },
      rollupOptions: {
        input:
          command === 'build'
            ? { main: './scss/main.scss', app: './js/init.js' }
            : { app: './js/init.js' },
        output: {
          entryFileNames: 'js/[name].js',
          assetFileNames: (assetInfo) => {
            const name = assetInfo.name ?? '';
            if (name.endsWith('.css')) return 'css/[name][extname]';
            if (/\.(woff2?|ttf|otf|eot)$/i.test(name))
              return 'assets/fonts/[name][extname]';
            if (/\.(png|jpe?g|gif|svg|webp|avif)$/i.test(name))
              return 'assets/img/[name][extname]';
            return 'assets/[name][extname]';
          },
        },
      },
    },

    server: {
      https,
      host: '0.0.0.0',
      port,
      strictPort: true,

      // Allow Vite to access theme (and parent) paths if needed
      fs: {
        allow: [themeDir, path.resolve(themeDir, '..')],
      },

      // Enable CORS for ES modules loaded from a different port
      cors: {
        origin: `https://${domain}`,
        credentials: false,
        methods: ['GET', 'HEAD', 'OPTIONS'],
        allowedHeaders: ['Content-Type', 'Accept', 'Range'],
      },

      // Explicit headers to satisfy Firefox/Safari module requests
      headers: {
        'Access-Control-Allow-Origin': `https://${domain}`,
        'Access-Control-Allow-Methods': 'GET,HEAD,OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type, Accept, Range',
        'Cross-Origin-Resource-Policy': 'cross-origin',
      },

      // Use secure WebSocket for HMR on https sites
      hmr: {
        host: domain,
        protocol: https ? 'wss' : 'ws',
        clientPort: port,
      },

      // Correct origin for generated URLs in @vite/client
      origin: `${https ? 'https' : 'http'}://${domain}:${port}`,
    },

    // Pre-bundle these to keep a single instance and speed up dev
    optimizeDeps: {
      include: ['gsap', 'gsap/ScrollTrigger', 'lenis'],
    },

    // Watch PHP/HTML template files and trigger a full page reload on change (dev only)
    plugins: [
      {
        name: 'php-full-reload',
        apply: 'serve',
        configureServer(server) {
          const log = (...args) =>
            server.config.logger.info(args.join(' '), {
              clear: false,
              timestamp: true,
            });

          // Globs to watch (adjust to your theme structure)
          const files = [
            // Theme PHP files
            path.join(themeDir, '**/*.php'),

            // Static/template files
            path.join(themeDir, '**/*.html'),
            path.join(themeDir, '**/*.htm'),
            path.join(themeDir, '**/*.twig'),
            path.join(themeDir, '**/*.mustache'),
            path.join(themeDir, '**/*.blade.php'),

            // Example: if you have a templates dir:
            // path.join(themeDir, 'templates/**/*.{php,html,twig,mustache,blade.php}'),

            // If templates live one level above:
            // path.resolve(themeDir, '../**/*.{php,html,twig,mustache,blade.php}'),
          ];

          // Add globs to Vite's own watcher
          server.watcher.add(files);

          const reload = debounce((file) => {
            log('[template-reload] full-reload for:', file);
            server.ws.send({ type: 'full-reload' });
          }, 150);

          server.watcher.on('add', (f) => f.endsWith('.php') && reload(f));
          server.watcher.on('change', (f) => f.endsWith('.php') && reload(f));
          server.watcher.on('unlink', (f) => f.endsWith('.php') && reload(f));
        },
      },
    ],
  };
});
