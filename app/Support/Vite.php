<?php

namespace App\Support;

class Vite
{
    /**
     * @param  string|array  $entrypoints
     */
    public function tags($entrypoints): string
    {
        $entrypoints = is_array($entrypoints) ? $entrypoints : [$entrypoints];

        if ($this->isHot()) {
            return $this->hotTags($entrypoints);
        }

        return $this->buildTags($entrypoints);
    }

    protected function isHot(): bool
    {
        return is_file(public_path('hot'));
    }

    /**
     * @param  array  $entrypoints
     */
    protected function hotTags(array $entrypoints): string
    {
        $url = trim((string) file_get_contents(public_path('hot')));
        $url = rtrim($url, '/');

        $tags = [
            '<script type="module" src="'.$url.'/@vite/client"></script>',
        ];

        foreach ($entrypoints as $entry) {
            $entry = ltrim((string) $entry, '/');

            if (substr($entry, -4) === '.css') {
                $tags[] = '<link rel="stylesheet" href="'.$url.'/'.$entry.'">';
                continue;
            }

            $tags[] = '<script type="module" src="'.$url.'/'.$entry.'"></script>';
        }

        return implode("\n", array_unique($tags));
    }

    /**
     * @param  array  $entrypoints
     */
    protected function buildTags(array $entrypoints): string
    {
        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            return '';
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        if (! is_array($manifest)) {
            return '';
        }

        $tags = [];
        $processed = [];

        foreach ($entrypoints as $entry) {
            $this->appendEntryTags($manifest, (string) $entry, $tags, $processed);
        }

        return implode("\n", array_unique($tags));
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @param  array<int, string>  $tags
     * @param  array<string, bool>  $processed
     */
    protected function appendEntryTags(array $manifest, string $entry, array &$tags, array &$processed): void
    {
        if (! isset($manifest[$entry]) || isset($processed[$entry])) {
            return;
        }

        $processed[$entry] = true;
        $chunk = $manifest[$entry];

        if (isset($chunk['css']) && is_array($chunk['css'])) {
            foreach ($chunk['css'] as $cssFile) {
                $tags[] = '<link rel="stylesheet" href="'.asset('build/'.$cssFile).'">';
            }
        }

        if (isset($chunk['imports']) && is_array($chunk['imports'])) {
            foreach ($chunk['imports'] as $import) {
                $this->appendEntryTags($manifest, (string) $import, $tags, $processed);
            }
        }

        if (isset($chunk['file'])) {
            $file = (string) $chunk['file'];

            if (substr($file, -4) === '.css') {
                $tags[] = '<link rel="stylesheet" href="'.asset('build/'.$file).'">';
            } else {
                $tags[] = '<script type="module" src="'.asset('build/'.$file).'"></script>';
            }
        }
    }
}
