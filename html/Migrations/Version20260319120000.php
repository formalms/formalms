<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Safely repair residual non-ASCII SCORM paths after Version20260305084456.
 *
 * This migration used to assume that if learning_scorm_package.path still
 * contained a non-ASCII value, then the corresponding directory had already
 * been renamed on disk and only the DB update was missing.
 *
 * That assumption is unsafe: if the filesystem rename failed in
 * Version20260305084456, blindly sanitizing the DB path creates drift between
 * DB and disk and makes the package unreachable.
 *
 * The fix implemented here is conservative:
 * - rename disk first when the legacy directory still exists
 * - update DB only after the sanitized directory is confirmed on disk
 * - skip ambiguous conflicts for manual inspection
 */
final class Version20260319120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Safely repair residual non-ASCII SCORM package paths without creating DB/filesystem drift';
    }

    public function up(Schema $schema): void
    {
        $basePath = $this->getScormBasePath();
        $this->write(sprintf('  SCORM base path: %s', $basePath));

        if (!is_dir($basePath)) {
            $this->write('  SCORM base path not found on disk. Nothing to fix safely.');

            return;
        }

        $rows = $this->connection->fetchAllAssociative(
            "SELECT idscorm_package, path FROM learning_scorm_package WHERE path REGEXP '[^\x00-\x7F]'"
        );

        if (empty($rows)) {
            $this->write('  No records with stale non-ASCII paths remaining. Nothing to fix.');

            return;
        }

        $this->write(sprintf('  Found %d records with stale non-ASCII paths.', count($rows)));

        $processedPaths = [];
        $fixedRows = 0;
        foreach ($rows as $row) {
            $oldPath = (string) $row['path'];
            if ($oldPath === '' || isset($processedPaths[$oldPath])) {
                continue;
            }

            $processedPaths[$oldPath] = true;
            $newPath = $this->sanitizeScormPath($oldPath);

            if ($oldPath === $newPath) {
                continue;
            }

            $oldFullPath = $basePath . $oldPath;
            $newFullPath = $basePath . $newPath;
            $oldExists = is_dir($oldFullPath);
            $newExists = is_dir($newFullPath);

            $this->write(sprintf(
                '    CHECK: %s -> %s | FS old=%s FS new=%s',
                $oldPath,
                $newPath,
                $oldExists ? 'yes' : 'no',
                $newExists ? 'yes' : 'no'
            ));

            if ($oldExists && !$newExists) {
                if (!@rename($oldFullPath, $newFullPath)) {
                    $this->write(sprintf('    ERROR (rename failed): %s -> %s', $oldPath, $newPath));

                    continue;
                }

                $this->write(sprintf('    RENAMED DIR: %s -> %s', $oldPath, $newPath));
                $this->renameZipIfNeeded($basePath, $oldPath, $newPath);
            } elseif (!$oldExists && $newExists) {
                $this->renameZipIfNeeded($basePath, $oldPath, $newPath);
            } elseif ($oldExists && $newExists) {
                $this->write(sprintf('    SKIP CONFLICT (both dirs exist): %s / %s', $oldPath, $newPath));

                continue;
            } else {
                $this->write(sprintf('    SKIP MISSING (neither dir exists): %s / %s', $oldPath, $newPath));

                continue;
            }

            $updated = $this->connection->executeStatement(
                'UPDATE learning_scorm_package SET path = ? WHERE path = ?',
                [$newPath, $oldPath]
            );

            $fixedRows += $updated;
            $this->write(sprintf('    UPDATED DB: %d rows now point to %s', $updated, $newPath));
        }

        $this->write(sprintf('  Fixed %d rows.', $fixedRows));
    }

    public function down(Schema $schema): void
    {
        $this->write('WARNING: This migration cannot be automatically reverted.');
        $this->write('It may rename SCORM directories and zip files to restore DB/filesystem alignment.');
    }

    /**
     * Replicate the exact sanitization logic from Version20260305084456::sanitizeScormPath().
     */
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

    private function renameZipIfNeeded(string $basePath, string $oldPath, string $newPath): void
    {
        $oldZipPath = $this->getZipPathFromContentPath($oldPath);
        $newZipPath = $this->getZipPathFromContentPath($newPath);

        if ($oldZipPath === null || $newZipPath === null) {
            return;
        }

        $oldZipFullPath = $basePath . $oldZipPath;
        $newZipFullPath = $basePath . $newZipPath;

        if (!is_file($oldZipFullPath)) {
            return;
        }

        if (file_exists($newZipFullPath)) {
            $this->write(sprintf('    SKIP ZIP CONFLICT: %s -> %s', $oldZipPath, $newZipPath));

            return;
        }

        if (@rename($oldZipFullPath, $newZipFullPath)) {
            $this->write(sprintf('    RENAMED ZIP: %s -> %s', $oldZipPath, $newZipPath));
        } else {
            $this->write(sprintf('    ERROR (zip rename failed): %s -> %s', $oldZipPath, $newZipPath));
        }
    }

    private function getZipPathFromContentPath(string $contentPath): ?string
    {
        $suffix = '_content';
        if (substr($contentPath, -strlen($suffix)) !== $suffix) {
            return null;
        }

        return substr($contentPath, 0, -strlen($suffix));
    }
}
