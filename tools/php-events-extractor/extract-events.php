<?php
/**
 * Event Extractor Script
 * Extracts all Events::trigger calls from PHP files
 */

class EventExtractor
{
    private $baseDir;
    private $events = [];
    private $pattern = "/Events::trigger\s*\(\s*['\"]([^'\"]+)['\"]/"; // Only Events::trigger

    public function __construct($baseDir = null)
    {
        $this->baseDir = $baseDir ?: dirname(__DIR__) . '/html';
    }

    public function extract()
    {
        $this->events = [];
        $this->scanDirectory($this->baseDir);
        return $this->events;
    }

    private function scanDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php' && $file->isFile()) {
                $this->extractFromFile($file);
            }
        }
    }

    private function extractFromFile(SplFileInfo $file)
    {
        $content = file_get_contents($file->getPathname());

        preg_match_all($this->pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as $index => $match) {
            $eventName = $match[0];
            $offset = $matches[0][$index][1];
            $lineNumber = $this->getLineNumber($content, $offset);

            // Parse event segments for grouping
            $segments = $this->parseEventSegments($eventName);

            $this->events[] = [
                'event' => $eventName,
                'file' => $this->getRelativePath($file->getPathname()),
                'line' => $lineNumber,
                'module' => $segments['module'],
                'feature' => $segments['feature'],
                'action' => $segments['action'],
                'sort_key' => $segments['sort_key']
            ];
        }
    }

    private function getLineNumber($content, $offset)
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }

    private function getRelativePath($fullPath)
    {
        return str_replace($this->baseDir . '/', '', $fullPath);
    }

    private function parseEventSegments($eventName)
    {
        $parts = explode('.', $eventName);

        $module = $parts[0] ?? '';
        $feature = $parts[1] ?? '';
        $action = implode('.', array_slice($parts, 2));

        // Create sort key: module.feature
        $sortKey = $module . '.' . $feature;

        return [
            'module' => $module,
            'feature' => $feature,
            'action' => $action,
            'sort_key' => $sortKey
        ];
    }

    private function sortAndGroupEvents($events)
    {
        // Sort events by sort_key, then by event name
        usort($events, function ($a, $b) {
            $sortKeyCompare = strcmp($a['sort_key'], $b['sort_key']);
            if ($sortKeyCompare === 0) {
                return strcmp($a['event'], $b['event']);
            }
            return $sortKeyCompare;
        });

        // Group by sort_key (module.feature)
        $grouped = [];
        foreach ($events as $event) {
            $key = $event['sort_key'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'module' => $event['module'],
                    'feature' => $event['feature'],
                    'events' => []
                ];
            }
            $grouped[$key]['events'][] = $event;
        }

        return $grouped;
    }

    public function exportToConsole($events)
    {
        $groupedEvents = $this->sortAndGroupEvents($events);

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "EVENTS FOUND: " . count($events) . "\n";
        echo str_repeat('=', 60) . "\n\n";

        foreach ($groupedEvents as $sortKey => $group) {
            echo "📦 {$group['module']}.{$group['feature']}\n";
            echo str_repeat('-', 40) . "\n";

            // Group by event name within the module.feature group
            $eventGroups = [];
            foreach ($group['events'] as $event) {
                $eventGroups[$event['event']][] = $event;
            }

            foreach ($eventGroups as $eventName => $occurrences) {
                echo "  🎯 {$eventName}\n";

                foreach ($occurrences as $occurrence) {
                    echo "     📁 {$occurrence['file']}:{$occurrence['line']}\n";
                }
                echo "\n";
            }
            echo "\n";
        }

        echo str_repeat('=', 60) . "\n";
        echo "TOTAL: " . count($events) . " events in " . count($groupedEvents) . " groups\n";
        echo str_repeat('=', 60) . "\n\n";
    }

    public function exportToReadme($events, $outputFile)
    {
        $groupedEvents = $this->sortAndGroupEvents($events);

        $content = "# FormaLMS System Events\n\n";
        $content .= "Automatically generated on " . date('Y-m-d H:i:s') . "_\n\n";
        $content .= "Total events: **" . count($events) . "** grouped in **" . count($groupedEvents) . "** modules\n\n";

        foreach ($groupedEvents as $sortKey => $group) {
            $content .= "## 📦 {$group['module']}.{$group['feature']}\n\n";

            // Group by event name within the module.feature group
            $eventGroups = [];
            foreach ($group['events'] as $event) {
                $eventGroups[$event['event']][] = $event;
            }

            foreach ($eventGroups as $eventName => $occurrences) {
                $content .= "### 🎯 `{$eventName}`\n\n";

                $occurrencesFiles = [];
                foreach ($occurrences as $occurrence) {
                    if (!isset($occurrencesFiles[$occurrence['file']])) {
                        $content .= "- **{$occurrence['file']}**\n";
                        $occurrencesFiles[$occurrence['file']] = true;
                    }
                }
                $content .= "\n";
            }
            $content .= "---\n\n";
        }

        file_put_contents($outputFile, $content);
        echo "README events generated: {$outputFile}\n";
    }
}

// Main execution
function main($argc, $argv)
{
    $format = 'console';
    $output = dirname(__DIR__, 2) . '/EVENTS.md';
    $baseDir = dirname(__DIR__, 2) . '/html';

    for ($i = 1; $i < $argc; $i++) {
        if (str_starts_with($argv[$i], '--readme')) {
            $format = 'readme';
            if (str_contains($argv[$i], '=')) {
                $output = substr($argv[$i], strpos($argv[$i], '=') + 1);
            }
        } elseif (str_starts_with($argv[$i], '--output=')) {
            $output = substr($argv[$i], 9);
        }
    }

    $extractor = new EventExtractor($baseDir);

    echo "Scanning directory: " . $baseDir . "\n";
    echo "Looking for Events::trigger events...\n\n";

    $events = $extractor->extract();

    if (empty($events)) {
        echo "❌ No events found!\n";
        return 1;
    }

    if ($format === 'readme') {
        echo "Exporting...\n";
        $outputFile = $output ?: 'EVENTS.md';
        $extractor->exportToReadme($events, $outputFile);
    } else {
        $extractor->exportToConsole($events);
    }

    return 0;
}

// Execute if run directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    exit(main($argc, $argv));
}
