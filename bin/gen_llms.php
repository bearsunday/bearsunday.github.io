<?php

/**
 * PHP script to expand links in llms.txt (Markdown files) and save the expanded content to llms-full.txt.
 *
 * This script reads the contents of 'llms.txt', interprets each line as a link to a
 * local Markdown file, reads the content of each Markdown file, and recursively
 * expands internal links within those files. The expanded content is saved to a
 * new file called 'llms-full.txt'.
 *
 * **Important:**
 *  - This script is designed for *local* files and assumes the URLs in llms.txt
 *    are relative to or part of a known base URL that points to markdown files on disk.
 *  - The base URL and directory structure of your Markdown files are *crucial* for this
 *    script to work correctly.
 *  - This script is designed for Jekyll-style markdown files.
 *  - Error handling is more robust in this version, but still needs thorough review.
 *  - This script includes more checks for URLs that start with leading or trailing
 *    problematic characters ('>', etc.) and also handles cases where the URL
 *    and description are combined on the same line. It's tailored for robustness.
 *
 * Usage:
 *  1. Save this script as `expand_md_links.php`.
 *  2. Place `llms.txt` in the same directory as `expand_md_links.php`.
 *  3. Adjust `$baseUrl` and `$baseDir` in the script to match your site structure.
 *  4. Run the script from the command line: `php expand_md_links.php`
 *  5. The expanded content will be saved in `llms-full.txt`.
 */

class MdLinkExpander {

    private string $baseUrl;
    private string $baseDir;
    private string $llmsTxt;
    private string $llmsFullTxt;

    public function __construct(string $baseUrl, string $baseDir, string $llmsTxt, string $llmsFullTxt) {
        $this->baseUrl = $baseUrl;
        $this->baseDir = rtrim($baseDir, '/'); // Ensure no trailing slash
        $this->llmsTxt = $llmsTxt;
        $this->llmsFullTxt = $llmsFullTxt;
    }

 public function expand(): bool
    {
        try {
            // Verify the source file exists.
            if (!file_exists($this->llmsTxt)) {
                throw new Exception("Source file {$this->llmsTxt} not found.");
            }

            // Read the lines from llms.txt
            $lines = file($this->llmsTxt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            $output = fopen($this->llmsFullTxt, 'w');
            if (!$output) {
                throw new Exception("Unable to open output file {$this->llmsFullTxt} for writing.");
            }

            foreach ($lines as $line) {
                $processedLine = $this->processLinks($line);
                fwrite($output, $processedLine . "\n");
            }

            fclose($output);
            echo "Successfully generated {$this->llmsFullTxt}\n";
            return true;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function processUrl(string $line, $output) : void {
        $line = trim($line);

        // Extract URL from Markdown link syntax: [text](URL)
        if (preg_match('/\[([^\]]+)\]\(([^)]+)\)/', $line, $matches)) {
            $url = trim($matches[2]);

            // Further clean up potential leading characters (>, [, etc.) in extracted URL
            $url = ltrim($url, ">[](){}`*+- ");

            echo "Processing: $url\n";

            try {
                $localPath = $this->urlToLocalPath($url);
                $content = $this->readLocalFile($localPath);
                $processedContent = $this->processLinks($content);

                fwrite($output, "# Source: $url\n\n");
                fwrite($output, $processedContent . "\n\n");
                fwrite($output, "--------------------\n\n");

            } catch (Exception $e) {
                echo "  Error processing $url: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  Skipping line: $line (Not a valid Markdown link)\n";
        }
    }

    private function urlToLocalPath(string $url) : string {
        $url = trim($url);

        //Check base URL first
        if (strpos($url, $this->baseUrl) !== 0 && strpos($url, '/') !== 0) {
            throw new Exception("URL '$url' is not an internal URL.");
        }

        $relativePath = (strpos($url, '/') === 0) ? ltrim($url, '/') : str_replace($this->baseUrl, '', $url);

        $relativePath = trim($relativePath, '/'); // Trim multiple slashes

        // Remove URL fragment
        $parsedUrl = parse_url($relativePath);
        if (isset($parsedUrl['path'])) {
            $relativePath = $parsedUrl['path'];
        }

        //Append filename if necessary
        if (empty(pathinfo($relativePath, PATHINFO_EXTENSION))) {
            $relativePath .= '/index.md';
        }

        //Convert extension
        if (strtolower(pathinfo($relativePath, PATHINFO_EXTENSION)) === 'html') {
            $relativePath = substr($relativePath, 0, -5) . '.md';
        }

        return $this->baseDir . '/' . $relativePath;

    }

    private function readLocalFile(string $path) : string {
        $path = trim($path);

        if (!file_exists($path)) {
            throw new Exception("File not found: $path");
        }

        return file_get_contents($path);
    }

   private function processLinks(string $line): string
    {
        $pattern = '/\[([^\]]+)\]\(([^)]+)\)/';

        // Check if the line is a Markdown link
        if (preg_match($pattern, $line, $matches)) {
            $linkText = $matches[1];
            $linkUrl = trim($matches[2]);

            // Process only internal URLs
            if (strpos($linkUrl, $this->baseUrl) === 0 || strpos($linkUrl, '/') === 0) {
                try {
                    $localPath = $this->urlToLocalPath($linkUrl);
                    $content = $this->readLocalFile($localPath);

                    // Remove front matter
                    $content = preg_replace('/^---.*?---/s', '', $content, 1);
                    return $content;

                } catch (Exception $e) {
                    echo "  Error processing linked URL " . $linkUrl . ": " . $e->getMessage() . "\n";
                    return $line; // Return original line on error
                }
            }
        }

        return $line; // Return original line if not a matching link
    }
}

// **Configuration - EDIT THESE TO MATCH YOUR SITE**
$baseUrl = 'https://bearsunday.github.io/manuals/1.0/en/';
$baseDir = __DIR__ . '/../'; // The directory where the Markdown files are stored.
$llmsTxt = __DIR__ . '/../llms.txt';
$llmsFullTxt = __DIR__ . '/../llms-full.txt';

//Run
$expander = new MdLinkExpander($baseUrl, $baseDir, $llmsTxt, $llmsFullTxt);
$success = $expander->expand();

if (!$success) {
    exit(1); // Indicate failure
}

exit(0); // Indicate successful execution
