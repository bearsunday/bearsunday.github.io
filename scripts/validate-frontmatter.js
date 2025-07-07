#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

function validateFrontmatter(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  
  // Check if file starts with frontmatter
  if (!content.startsWith('---\n')) {
    throw new Error(`Missing opening frontmatter delimiter in ${filePath}`);
  }
  
  // Find the closing delimiter
  const lines = content.split('\n');
  let closingDelimiterIndex = -1;
  
  for (let i = 1; i < lines.length; i++) {
    if (lines[i] === '---') {
      closingDelimiterIndex = i;
      break;
    }
  }
  
  if (closingDelimiterIndex === -1) {
    throw new Error(`Missing closing frontmatter delimiter in ${filePath}`);
  }
  
  // Extract frontmatter content
  const frontmatterContent = lines.slice(1, closingDelimiterIndex).join('\n');
  
  // Basic YAML validation - check for required fields
  const requiredFields = ['layout', 'title', 'category', 'permalink'];
  
  for (const field of requiredFields) {
    if (!frontmatterContent.includes(`${field}:`)) {
      throw new Error(`Missing required field '${field}' in ${filePath}`);
    }
  }
  
  console.log(`✓ ${filePath} - Valid frontmatter`);
}

function findMarkdownFiles(dir) {
  const files = [];
  const items = fs.readdirSync(dir);
  
  for (const item of items) {
    const fullPath = path.join(dir, item);
    const stat = fs.statSync(fullPath);
    
    if (stat.isDirectory()) {
      files.push(...findMarkdownFiles(fullPath));
    } else if (item.endsWith('.md')) {
      files.push(fullPath);
    }
  }
  
  return files;
}

function main() {
  const files = findMarkdownFiles('manuals');
  let errorCount = 0;
  
  for (const file of files) {
    // Skip generated files
    if (file.includes('1page.md') || file.includes('onepage.md')) {
      continue;
    }
    
    try {
      validateFrontmatter(file);
    } catch (error) {
      console.error(`✗ ${error.message}`);
      errorCount++;
    }
  }
  
  if (errorCount > 0) {
    console.error(`\nValidation failed: ${errorCount} errors found`);
    process.exit(1);
  } else {
    console.log(`\nAll documentation files validated successfully`);
  }
}

if (require.main === module) {
  main();
}