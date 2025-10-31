#!/usr/bin/env node
const { execSync } = require('child_process')
const { readdirSync, statSync } = require('fs')
const { join } = require('path')

function listYamlFiles(dir) {
  const result = []
  const files = readdirSync(dir)
  for (const f of files) {
    const full = join(dir, f)
    const st = statSync(full)
    if (st.isDirectory()) {
      result.push(...listYamlFiles(full))
    } else if (/\.ya?ml$/i.test(f)) {
      result.push(full)
    }
  }
  return result
}

const workflowsDir = '.github/workflows'
try {
  const files = listYamlFiles(workflowsDir)
  if (files.length === 0) {
    console.log('No workflow yaml files found under', workflowsDir)
    process.exit(0)
  }

  let failed = false
  for (const f of files) {
    console.log('\n=== Validating', f, '===')
    try {
      execSync(`npx action-validator "${f}"`, { stdio: 'inherit' })
    } catch (e) {
      failed = true
      console.error('Validation failed for', f)
    }
  }

  if (failed) process.exit(2)
  console.log('\nAll workflow YAML files validated successfully.')
} catch (err) {
  console.error('Error while validating workflows:', err.message || err)
  process.exit(1)
}
