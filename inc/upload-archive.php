<?php
/** Strict, bounded ZIP reader for browser-ready static websites. */
defined('ABSPATH') || exit;

final class Monty_Portfolio_Archive
{
    public const MAX_ZIP = 20971520;
    public const MAX_TOTAL = 67108864;
    public const MAX_FILE = 8388608;
    public const MAX_ENTRIES = 1000;
    private $zip;
    private $files = array();
    private $entry = '';

    public static function open($path)
    {
        if (!class_exists('ZipArchive')) {
            return new WP_Error('zip_extension', 'Your host needs the PHP ZIP extension enabled to import a project ZIP.');
        }
        $size = is_file($path) ? filesize($path) : 0;
        if (!$size || $size > self::MAX_ZIP) {
            return new WP_Error('zip_size', 'Choose a ZIP smaller than 20 MB.');
        }
        $reader = new self();
        $reader->zip = new ZipArchive();
        if (true !== $reader->zip->open($path, ZipArchive::CHECKCONS)) {
            $reader->zip = null;
            return new WP_Error('invalid_zip', 'This file is not a readable ZIP. Create a fresh ZIP of your website folder.');
        }
        $result = $reader->inspect();
        if (is_wp_error($result)) {
            $reader->close();
            return $result;
        }
        return $reader;
    }

    public static function mime_types()
    {
        return array(
            'html' => 'text/html',
            'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'text/javascript',
            'mjs' => 'text/javascript',
            'json' => 'application/json',
            'map' => 'application/json',
            'webmanifest' => 'application/manifest+json',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'ico' => 'image/x-icon',
            'bmp' => 'image/bmp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
            'txt' => 'text/plain',
            'md' => 'text/plain',
            'csv' => 'text/csv',
            'xml' => 'application/xml',
            'pdf' => 'application/pdf',
            'glb' => 'model/gltf-binary',
            'gltf' => 'model/gltf+json',
            'bin' => 'application/octet-stream',
            'vtt' => 'text/vtt',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
            'wasm' => 'application/wasm',
        );
    }

    public static function safe_path($path)
    {
        if (!is_string($path) || '' === $path || strlen($path) > 400 || !preg_match('//u', $path) || preg_match('/[\x00-\x1f\x7f\\\\:*?"<>|#%]/u', $path) || '/' === $path[0]) {
            return false;
        }
        $parts = explode('/', rtrim($path, '/'));
        foreach ($parts as $part) {
            if ('' === $part || '.' === $part || '..' === $part || strlen($part) > 160 || preg_match('/[. ]$/', $part)) {
                return false;
            }
        }
        return implode('/', $parts);
    }

    private function inspect()
    {
        if ($this->zip->numFiles > self::MAX_ENTRIES) {
            return new WP_Error('zip_count', 'The ZIP contains too many files. Upload only the finished website, without node_modules or development tools.');
        }
        $candidates = array();
        $seen = array();
        $total = 0;
        $types = self::mime_types();
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $stat = $this->zip->statIndex($i);
            $name = $stat['name'] ?? '';
            $path = self::safe_path($name);
            if (false === $path) {
                return new WP_Error('zip_path', 'The ZIP contains an unsafe or unsupported file path. ZIP one website folder with ordinary file names.');
            }
            $system = 0;
            $attributes = 0;
            if ($this->zip->getExternalAttributesIndex($i, $system, $attributes) && 0120000 === (($attributes >> 16) & 0170000)) {
                return new WP_Error('zip_link', 'Symbolic links are not supported. Include the actual website files in your ZIP.');
            }
            $segments = explode('/', $path);
            if (array_intersect($segments, array('__MACOSX', 'node_modules', '.git', '.github', '.vscode', '.idea')) || '.DS_Store' === basename($path)) {
                continue;
            }
            if ('/' === substr($name, -1)) {
                continue;
            }
            foreach ($segments as $part) {
                if ('.' === $part[0]) {
                    return new WP_Error('zip_hidden', 'Remove hidden configuration files, such as .env and .htaccess, from the website ZIP.');
                }
            }
            if (preg_match('/^(readme|licen[cs]e|changelog)(\.|$)/i', basename($path))) {
                continue;
            }
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!isset($types[$extension])) {
                return new WP_Error('zip_type', 'This uploader accepts finished HTML/CSS/JavaScript websites. Remove server files and development source, or ZIP the built dist/build folder.');
            }
            if (isset($seen[strtolower($path)])) {
                return new WP_Error('zip_duplicate', 'The ZIP contains duplicate file paths. Create a ZIP with one copy of each website file.');
            }
            $seen[strtolower($path)] = true;
            $bytes = (int) ($stat['size'] ?? 0);
            $total += $bytes;
            if ($bytes < 0 || $bytes > self::MAX_FILE || $total > self::MAX_TOTAL) {
                return new WP_Error('zip_expansion', 'Keep each website file under 8 MB and the unzipped website under 64 MB.');
            }
            if (!empty($stat['encryption_method']) || ($bytes > 1048576 && $bytes / max(1, (int) $stat['comp_size']) > 500)) {
                return new WP_Error('zip_compression', 'Password-protected or unusually compressed ZIPs are not supported. Create a normal ZIP of the website.');
            }
            $this->files[] = array('index' => $i, 'path' => $path, 'bytes' => $bytes, 'mime' => $types[$extension], 'crc' => $stat['crc'] ?? 0);
            if ('index.html' === strtolower(basename($path)) || 'index.htm' === strtolower(basename($path))) {
                $candidates[] = $path;
            }
        }
        if (!$candidates) {
            return new WP_Error('zip_entry', 'The ZIP needs an index.html file. For React or Vite, build the project first and ZIP its finished output folder.');
        }
        usort($candidates, static function ($a, $b) {
            return substr_count($a, '/') <=> substr_count($b, '/'); });
        if (isset($candidates[1]) && substr_count($candidates[0], '/') === substr_count($candidates[1], '/')) {
            return new WP_Error('zip_ambiguous', 'The ZIP contains more than one website. Upload one project at a time.');
        }
        $entry = $candidates[0];
        $prefix = false === strpos($entry, '/') ? '' : substr($entry, 0, strrpos($entry, '/') + 1);
        foreach ($this->files as $file) {
            if ('' !== $prefix && 0 !== strpos($file['path'], $prefix)) {
                return new WP_Error('zip_layout', 'Keep index.html and all website assets inside the same project folder before creating the ZIP.');
            }
        }
        $this->entry = substr($entry, strlen($prefix));
        $this->files = array_values(array_filter($this->files, static function ($file) use ($prefix) {
            return '' === $prefix || 0 === strpos($file['path'], $prefix); }));
        foreach ($this->files as &$file) {
            $file['path'] = substr($file['path'], strlen($prefix));
        }
        unset($file);
        return true;
    }

    public function files()
    {
        return $this->files;
    }
    public function entry()
    {
        return $this->entry;
    }
    public function read($file)
    {
        $contents = $this->zip->getFromIndex($file['index'], self::MAX_FILE + 1);
        if (false === $contents || strlen($contents) !== $file['bytes'] || sprintf('%u', crc32($contents)) !== sprintf('%u', $file['crc'])) {
            return new WP_Error('zip_read', 'A file in the ZIP could not be read correctly. Create a fresh ZIP and try again.');
        }
        return $contents;
    }
    public function close()
    {
        if ($this->zip) {
            $this->zip->close();
            $this->zip = null;
        }
    }
    public function __destruct()
    {
        $this->close();
    }
}
