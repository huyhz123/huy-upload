<?php

namespace App\Services\CICD;

use Illuminate\Support\Facades\File;

class LayoutMergerService
{
    protected array $report = [];

    /**
     * Merge layout, CSS, and JS for all modules
     */
    public function merge(array $modules): array
    {
        $this->report = [
            'timestamp' => now()->toDateTimeString(),
            'modules_processed' => 0,
            'layout_merged' => 0,
            'css_merged' => 0,
            'js_merged' => 0,
            'files_modified' => [],
        ];

        foreach ($modules as $module) {
            if ($module['type'] !== 'frontend') {
                continue; // Admin modules usually already have layout
            }

            $filePath = base_path($module['path']);
            if (!File::exists($filePath)) {
                continue;
            }

            $modified = false;

            // Merge layout
            if (!$module['layout_merged']) {
                $modified = $this->mergeLayout($filePath) || $modified;
            }

            // Merge CSS
            if (!$module['css_merged']) {
                $modified = $this->mergeCSS($filePath) || $modified;
            }

            // Merge JS
            if (!$module['js_merged']) {
                $modified = $this->mergeJS($filePath) || $modified;
            }

            if ($modified) {
                $this->report['files_modified'][] = $module['path'];
            }

            $this->report['modules_processed']++;
        }

        return $this->report;
    }

    /**
     * Merge layout structure
     */
    protected function mergeLayout(string $filePath): bool
    {
        $content = File::get($filePath);
        $original = $content;

        // If already has @extends, skip
        if (preg_match('/@extends/', $content)) {
            return false;
        }

        // Check if it's a full HTML document
        if (preg_match('/<html[^>]*>/i', $content)) {
            // Extract body content
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $matches)) {
                $bodyContent = $matches[1];

                // Create new content with layout
                $newContent = "@extends('layouts.app')\n\n@section('content')\n";
                $newContent .= trim($bodyContent) . "\n";
                $newContent .= "@endsection\n";

                File::put($filePath, $newContent);
                $this->report['layout_merged']++;
                return true;
            }
        }

        // If it's a partial, wrap it with layout
        if (!preg_match('/@extends|@section/', $content)) {
            $newContent = "@extends('layouts.app')\n\n@section('content')\n";
            $newContent .= $content . "\n";
            $newContent .= "@endsection\n";

            File::put($filePath, $newContent);
            $this->report['layout_merged']++;
            return true;
        }

        return false;
    }

    /**
     * Merge CSS
     */
    protected function mergeCSS(string $filePath): bool
    {
        $content = File::get($filePath);
        $modified = false;

        // Extract inline styles and convert to CSS classes
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $content, $matches)) {
            // Remove style tags from content
            $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);

            // Add reference to app.css at the top (after @extends)
            if (preg_match('/@extends/', $content)) {
                if (!preg_match('/@vite|asset\([\'"]css/', $content)) {
                    $content = preg_replace(
                        '/(@extends[^\n]+\n)/',
                        "$1\n@push('styles')\n<link rel=\"stylesheet\" href=\"{{ asset('css/app.css') }}\">\n@endpush\n",
                        $content
                    );
                    $modified = true;
                    $this->report['css_merged']++;
                }
            }

            File::put($filePath, $content);
        }

        // Ensure proper asset loading
        if (!preg_match('/@vite|asset\([\'"]css/', $content) && !preg_match('/<link[^>]+\.css/', $content)) {
            // Add CSS after @extends
            if (preg_match('/@extends/', $content)) {
                $content = preg_replace(
                    '/(@extends[^\n]+\n)/',
                    "$1\n@push('styles')\n<link rel=\"stylesheet\" href=\"{{ asset('css/app.css') }}\">\n@endpush\n",
                    $content
                );
                File::put($filePath, $content);
                $modified = true;
                $this->report['css_merged']++;
            }
        }

        return $modified;
    }

    /**
     * Merge JS
     */
    protected function mergeJS(string $filePath): bool
    {
        $content = File::get($filePath);
        $modified = false;

        // Extract inline scripts
        if (preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $content, $matches)) {
            // Keep the scripts but ensure proper asset loading
            if (!preg_match('/@vite|asset\([\'"]js/', $content)) {
                // Add JS before closing @section
                if (preg_match('/@endsection/', $content)) {
                    $content = preg_replace(
                        '/(@endsection)/',
                        "\n@push('scripts')\n<script src=\"{{ asset('js/app.js') }}\"></script>\n@endpush\n\n$1",
                        $content
                    );
                    File::put($filePath, $content);
                    $modified = true;
                    $this->report['js_merged']++;
                }
            }
        }

        return $modified;
    }

    /**
     * Get report
     */
    public function getReport(): array
    {
        return $this->report;
    }
}
