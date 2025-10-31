import { marked } from 'marked'

// Configure marked.js options
// marked v16 uses a modern API - breaks and gfm are the main options we need
marked.setOptions({
  breaks: true,
  gfm: true,
})

// Expose marked globally for use in Blade templates and Alpine components
window.marked = marked
