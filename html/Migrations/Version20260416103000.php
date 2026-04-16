<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Repair SCORM path drift and residual unsanitized historical SCORM paths.
 *
 * Version20260305084456 renames a subset of historical SCORM package
 * directories on disk and then updates learning_scorm_package.path.
 * Version20260319120000 later updates residual DB paths without
 * checking whether the filesystem rename really happened.
 *
 * If the first migration failed to rename the directory but the second one still
 * sanitized the DB path, the package becomes unreadable because DB and disk point
 * to different names.
 *
 * This migration repairs both scenarios:
 * - DB still contains an unsanitized historical path
 * - DB already contains the sanitized path, but disk still uses the old one
 *
 * The repair scope is broader than non-ASCII only: any path that differs from
 * sanitizeScormPath() is considered unsanitized, including spaces,
 * parentheses, dots in the filename part, accented characters and similar cases.
 *
 * The repair is intentionally conservative:
 * - rename disk only when the sanitized target does not already exist
 * - update DB only when the sanitized target is confirmed on disk
 * - skip ambiguous conflicts for manual inspection
 */
final class Version20260416103000 extends AbstractMigration
{
    private array $stats = [
        'db_paths_checked' => 0,
        'disk_paths_checked' => 0,
        'dirs_renamed' => 0,
        'zips_renamed' => 0,
        'db_rows_updated' => 0,
        'conflicts' => 0,
        'missing' => 0,
        'skipped' => 0,
    ];

    public function getDescription(): string
    {
        return 'Repair SCORM DB/filesystem drift and residual unsanitized historical paths';
    }

    public function up(Schema $schema): void
    {
        $basePath = $this->getScormBasePath();
        $this->write(sprintf('  SCORM base path: %s', $basePath));

        if (!is_dir($basePath)) {
            $this->write('  SCORM base path not found on disk. Nothing to repair.');

            return;
        }

        $packageRows = $this->connection->fetchAllAssociative(
            "SELECT idscorm_package, path FROM learning_scorm_package WHERE path IS NOT NULL AND path != ''"
        );

        if (empty($packageRows)) {
            $this->write('  No SCORM packages found in database. Nothing to repair.');

            return;
        }

        $packageIdsByPath = [];
        foreach ($packageRows as $row) {
            $path = (string) $row['path'];
            $packageIdsByPath[$path][] = (int) $row['idscorm_package'];
        }

        $processedPaths = [];

        foreach (array_keys($packageIdsByPath) as $path) {
            if ($path === '' || isset($processedPaths[$path])) {
                continue;
            }

            if ($path === $this->sanitizeScormPath($path)) {
                continue;
            }

            $processedPaths[$path] = true;
            ++$this->stats['db_paths_checked'];

            $this->repairLegacyPath($basePath, $path, $packageIdsByPath);
        }

        $entries = @scandir($basePath);
        if ($entries === false) {
            $this->write('  Unable to scan SCORM directory on disk. Repair completed using DB paths only.');
            $this->write($this->formatSummary());

            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (!$this->looksLikeScormContentPath($entry)) {
                continue;
            }

            $fullPath = $basePath . $entry;
            if (!is_dir($fullPath) || isset($processedPaths[$entry])) {
                continue;
            }

            if ($entry === $this->sanitizeScormPath($entry)) {
                continue;
            }

            $processedPaths[$entry] = true;
            ++$this->stats['disk_paths_checked'];

            $this->repairLegacyPath($basePath, $entry, $packageIdsByPath);
        }

        $this->write($this->formatSummary());
    }

    public function down(Schema $schema): void
    {
        $this->write('WARNING: This repair migration is not automatically reversible.');
        $this->write('It may rename SCORM directories and zip files to restore DB/filesystem alignment.');
    }

    private function repairLegacyPath(string $basePath, string $legacyPath, array &$packageIdsByPath): void
    {
        $sanitizedPath = $this->sanitizeScormPath($legacyPath);
        if ($legacyPath === $sanitizedPath) {
            ++$this->stats['skipped'];
            $this->write(sprintf('  SKIP (already sanitized): %s', $legacyPath));

            return;
        }

        $legacyIds = $packageIdsByPath[$legacyPath] ?? [];
        $sanitizedIds = $packageIdsByPath[$sanitizedPath] ?? [];

        if (empty($legacyIds) && empty($sanitizedIds)) {
            ++$this->stats['skipped'];
            $this->write(sprintf('  SKIP (path not referenced by DB): %s', $legacyPath));

            return;
        }

        $legacyDir = $basePath . $legacyPath;
        $sanitizedDir = $basePath . $sanitizedPath;
        $legacyExists = is_dir($legacyDir);
        $sanitizedExists = is_dir($sanitizedDir);

        $this->write(sprintf(
            '  CHECK: %s -> %s | DB old=%s DB new=%s | FS old=%s FS new=%s',
            $legacyPath,
            $sanitizedPath,
            $this->formatIds($legacyIds),
            $this->formatIds($sanitizedIds),
            $legacyExists ? 'yes' : 'no',
            $sanitizedExists ? 'yes' : 'no'
        ));

        if ($legacyExists && !$sanitizedExists) {
            if (!@rename($legacyDir, $sanitizedDir)) {
                ++$this->stats['conflicts'];
                $this->write(sprintf('    ERROR (rename failed): %s -> %s', $legacyPath, $sanitizedPath));

                return;
            }

            ++$this->stats['dirs_renamed'];
            $this->write(sprintf('    RENAMED DIR: %s -> %s', $legacyPath, $sanitizedPath));

            $this->renameZipIfNeeded($basePath, $legacyPath, $sanitizedPath);

            if (!empty($legacyIds)) {
                $this->updatePackagePathReferences($legacyPath, $sanitizedPath, $packageIdsByPath);
            }

            return;
        }

        if (!$legacyExists && $sanitizedExists) {
            $this->renameZipIfNeeded($basePath, $legacyPath, $sanitizedPath);

            if (!empty($legacyIds)) {
                $this->updatePackagePathReferences($legacyPath, $sanitizedPath, $packageIdsByPath);
            } else {
                ++$this->stats['skipped'];
                $this->write(sprintf('    OK (already aligned on disk): %s', $sanitizedPath));
            }

            return;
        }

        if ($legacyExists && $sanitizedExists) {
            ++$this->stats['conflicts'];
            $this->write(sprintf(
                '    SKIP CONFLICT (both dirs exist): old IDs=%s, new IDs=%s',
                $this->formatIds($legacyIds),
                $this->formatIds($sanitizedIds)
            ));

            return;
        }

        ++$this->stats['missing'];
        $this->write(sprintf(
            '    SKIP MISSING (neither dir exists): old IDs=%s, new IDs=%s',
            $this->formatIds($legacyIds),
            $this->formatIds($sanitizedIds)
        ));
    }

    private function renameZipIfNeeded(string $basePath, string $legacyPath, string $sanitizedPath): void
    {
        $legacyZipPath = $this->getZipPathFromContentPath($legacyPath);
        $sanitizedZipPath = $this->getZipPathFromContentPath($sanitizedPath);

        if ($legacyZipPath === null || $sanitizedZipPath === null) {
            return;
        }

        $legacyZipFullPath = $basePath . $legacyZipPath;
        $sanitizedZipFullPath = $basePath . $sanitizedZipPath;

        if (!is_file($legacyZipFullPath)) {
            return;
        }

        if (file_exists($sanitizedZipFullPath)) {
            ++$this->stats['conflicts'];
            $this->write(sprintf('    SKIP ZIP CONFLICT: %s -> %s', $legacyZipPath, $sanitizedZipPath));

            return;
        }

        if (!@rename($legacyZipFullPath, $sanitizedZipFullPath)) {
            ++$this->stats['conflicts'];
            $this->write(sprintf('    ERROR (zip rename failed): %s -> %s', $legacyZipPath, $sanitizedZipPath));

            return;
        }

        ++$this->stats['zips_renamed'];
        $this->write(sprintf('    RENAMED ZIP: %s -> %s', $legacyZipPath, $sanitizedZipPath));
    }

    private function updatePackagePathReferences(string $legacyPath, string $sanitizedPath, array &$packageIdsByPath): void
    {
        $updatedRows = $this->connection->executeStatement(
            'UPDATE learning_scorm_package SET path = ? WHERE path = ?',
            [$sanitizedPath, $legacyPath]
        );

        if ($updatedRows <= 0) {
            $this->write(sprintf('    DB already updated: %s', $sanitizedPath));

            return;
        }

        $legacyIds = $packageIdsByPath[$legacyPath] ?? [];
        $sanitizedIds = $packageIdsByPath[$sanitizedPath] ?? [];

        $packageIdsByPath[$sanitizedPath] = array_values(array_unique(array_merge($sanitizedIds, $legacyIds)));
        unset($packageIdsByPath[$legacyPath]);

        $this->stats['db_rows_updated'] += $updatedRows;
        $this->write(sprintf('    UPDATED DB: %d rows now point to %s', $updatedRows, $sanitizedPath));
    }

    private function getScormBasePath(): string
    {
        $subPath = $this->connection->fetchOne(
            'SELECT param_value FROM core_setting WHERE param_name = ?',
            ['pathscorm']
        );

        if ($subPath === false || $subPath === '') {
            $subPath = 'scorm/';
        }

        return rtrim(dirname(__DIR__) . '/files/appLms/' . trim((string) $subPath, '/'), '/') . '/';
    }

    private function sanitizeScormPath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $name = pathinfo($path, PATHINFO_FILENAME);

        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        if ($transliterated !== false) {
            $name = $transliterated;
        }

        $name = preg_replace('/\s+/', '_', $name);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);

        if (empty($name)) {
            $name = 'file_' . time() . '_' . bin2hex(random_bytes(4));
        }

        if ($extension !== '') {
            $extension = preg_replace('/[^a-zA-Z0-9_]/', '', $extension);

            return $name . '.' . $extension;
        }

        return $name;
    }

    private function getZipPathFromContentPath(string $contentPath): ?string
    {
        $suffix = '_content';
        if (substr($contentPath, -strlen($suffix)) !== $suffix) {
            return null;
        }

        return substr($contentPath, 0, -strlen($suffix));
    }

    private function looksLikeScormContentPath(string $path): bool
    {
        return substr($path, -strlen('_content')) === '_content';
    }

    private function formatIds(array $ids): string
    {
        if (empty($ids)) {
            return 'none';
        }

        sort($ids);

        return implode(',', $ids);
    }

    private function formatSummary(): string
    {
        return sprintf(
            '  Summary: checked %d DB unsanitized paths, checked %d unsanitized dirs on disk, renamed %d dirs, renamed %d zips, updated %d DB rows, %d conflicts, %d missing, %d skipped.',
            $this->stats['db_paths_checked'],
            $this->stats['disk_paths_checked'],
            $this->stats['dirs_renamed'],
            $this->stats['zips_renamed'],
            $this->stats['db_rows_updated'],
            $this->stats['conflicts'],
            $this->stats['missing'],
            $this->stats['skipped']
        );
    }
}
