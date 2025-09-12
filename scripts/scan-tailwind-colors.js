#!/usr/bin/env node
import fs from 'fs'
import path from 'path'

const root = path.resolve('./resources/js')
const exts = ['.vue', '.ts', '.js']

const colorPattern = /\b(?:bg|text|border)-(?:[a-z]+)-(?:50|100|200|300|400|500|600|700|800|900)\b/g

function walk(dir) {
  const results = []
  for (const name of fs.readdirSync(dir)) {
    const full = path.join(dir, name)
    const stat = fs.statSync(full)
    if (stat.isDirectory()) {
      results.push(...walk(full))
    } else if (exts.includes(path.extname(name))) {
      results.push(full)
    }
  }
  return results
}

const files = walk(root)
const report = []

for (const f of files) {
  const src = fs.readFileSync(f, 'utf8')
  const matches = src.match(colorPattern)
  if (matches && matches.length) {
    const uniques = Array.from(new Set(matches))
    report.push({ file: path.relative(process.cwd(), f), matches: uniques, count: uniques.length })
  }
}

// Sort by descending count
report.sort((a, b) => b.count - a.count)

const out = { generated_at: new Date().toISOString(), files: report, total_files: report.length }
const outPath = path.resolve('./tmp/tailwind-color-report.json')
fs.mkdirSync(path.dirname(outPath), { recursive: true })
fs.writeFileSync(outPath, JSON.stringify(out, null, 2), 'utf8')
console.log('Tailwind color scan complete. Report:', outPath)
