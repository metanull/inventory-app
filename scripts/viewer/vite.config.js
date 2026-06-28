import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { createReadStream, existsSync, statSync } from 'fs'
import { join, resolve } from 'path'

export default defineConfig(({ mode }) => {
  // Load all env vars (DATA_DIR has no VITE_ prefix so we use '' as prefix)
  const env = loadEnv(mode, process.cwd(), '')
  const dataDir = env.DATA_DIR ? resolve(env.DATA_DIR) : null

  return {
    plugins: [
      vue(),
      {
        name: 'data-server',
        configureServer(server) {
          // Intercept /data/* requests and serve them from DATA_DIR
          server.middlewares.use((req, res, next) => {
            const url = req.url ?? ''
            if (!url.startsWith('/data/') && url !== '/data') return next()

            if (!dataDir) {
              res.writeHead(500, { 'Content-Type': 'text/plain' })
              res.end('DATA_DIR is not set in .env')
              return
            }

            // Strip leading /data prefix to get the relative file path
            const relativePath = url.replace(/^\/data\/?/, '')
            const file = join(dataDir, relativePath)

            // Security: ensure the resolved path stays inside dataDir
            if (!file.startsWith(dataDir)) {
              res.writeHead(403, { 'Content-Type': 'text/plain' })
              res.end('Forbidden')
              return
            }

            if (!existsSync(file) || !statSync(file).isFile()) {
              res.writeHead(404, { 'Content-Type': 'text/plain' })
              res.end(`Not found: ${file}`)
              return
            }

            res.setHeader('Content-Type', 'application/json; charset=utf-8')
            createReadStream(file).pipe(res)
          })
        },
      },
    ],
  }
})
