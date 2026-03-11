<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: Rename existing files/directories with accented (non-ASCII) characters.
 *
 * Sanitizes filenames stored in the database and renames the corresponding
 * files/directories on disk. This prevents failures during SCORM copy operations
 * and other modules that cannot handle non-ASCII filenames.
 *
 * Tables processed:
 * - learning_scorm_package.path (directories)
 * - learning_course_file.path (files)
 * - learning_forummessage.attach (files)
 * - learning_materials_lesson.path (files)
 * - learning_testtrack_answer.more_info (files, filtered for actual filenames)
 */
final class Version20260305084456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename existing files with accented characters to ASCII-safe names';
    }

    public function up(Schema $schema): void
    {
        // Backup log of all original paths with non-ASCII characters
        $this->write('=== BACKUP LOG: Original paths with non-ASCII characters ===');
        $tables = [
            ['learning_scorm_package', 'idscorm_package', 'path'],
            ['learning_course_file', 'id_file', 'path'],
            ['learning_forummessage', 'idMessage', 'attach'],
            ['learning_materials_lesson', 'idLesson', 'path'],
            ['learning_testtrack_answer', 'idTrack', 'more_info'],
        ];
        foreach ($tables as [$table, $pk, $col]) {
            $rows = $this->connection->fetchAllAssociative(
                "SELECT $pk, $col FROM $table WHERE $col REGEXP '[^\x00-\x7F]'"
            );
            foreach ($rows as $row) {
                $this->write(sprintf('  BACKUP [%s] %s=%s : %s', $table, $pk, $row[$pk], $row[$col]));
            }
        }
        $this->write('=== END BACKUP LOG ===');

        $filesBasePath = dirname(__DIR__) . '/files/appLms/';

        // Read path settings from core_setting
        $pathSettings = $this->getPathSettings();

        // Process each table
        $this->processScormPackages($filesBasePath, $pathSettings['pathscorm']);
        $this->processCourseFiles($filesBasePath, $pathSettings['pathcourse']);
        $this->processForumMessages($filesBasePath, $pathSettings['pathforum']);
        $this->processMaterialsLessons($filesBasePath, $pathSettings['pathlesson']);
        $this->processTestTrackAnswers($filesBasePath, $pathSettings['pathtest']);
    }

    public function down(Schema $schema): void
    {
        // This migration is not reversible for filesystem operations.
        // Renamed files cannot be automatically restored to their original accented names.
        $this->write('WARNING: Filesystem renames cannot be reverted automatically.');
    }

    private function getPathSettings(): array
    {
        $defaults = [
            'pathscorm' => 'scorm/',
            'pathcourse' => 'course/',
            'pathforum' => 'forum/',
            'pathlesson' => 'item/',
            'pathtest' => 'test/',
        ];

        $settings = [];
        foreach ($defaults as $key => $default) {
            $value = $this->connection->fetchOne(
                'SELECT param_value FROM core_setting WHERE param_name = ?',
                [$key]
            );
            $settings[$key] = $value !== false && $value !== '' ? $value : $default;
        }

        return $settings;
    }

    /**
     * Process learning_scorm_package: path column contains directory names (e.g. "1_42_123_età_test.zip_content")
     */
    private function processScormPackages(string $filesBasePath, string $subPath): void
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT idscorm_package, path FROM learning_scorm_package WHERE path REGEXP '[^\x00-\x7F]'"
        );

        if (empty($rows)) {
            $this->write('  [scorm_package] No records with non-ASCII paths found.');
            return;
        }

        $this->write(sprintf('  [scorm_package] Found %d records with non-ASCII paths.', count($rows)));
        $basePath = $filesBasePath . $subPath;

        foreach ($rows as $row) {
            $oldPath = $row['path'];
            $newPath = $this->sanitizeScormPath($oldPath);

            if ($oldPath === $newPath) {
                continue;
            }

            $oldFullPath = $basePath . $oldPath;
            $newFullPath = $basePath . $newPath;

            if (is_dir($oldFullPath)) {
                if (is_dir($newFullPath)) {
                    $this->write(sprintf('    SKIP (target exists): %s -> %s', $oldPath, $newPath));
                    continue;
                }
                if (!@rename($oldFullPath, $newFullPath)) {
                    $this->write(sprintf('    ERROR (rename failed): %s -> %s', $oldPath, $newPath));
                    continue;
                }
                $this->write(sprintf('    RENAMED DIR: %s -> %s', $oldPath, $newPath));
            } else {
                $this->write(sprintf('    SKIP (not found on disk): %s', $oldPath));
                continue;
            }

            // Also rename the .zip file if it exists (path without _content suffix)
            $oldZipPath = $this->getZipPathFromContentPath($oldPath);
            $newZipPath = $this->getZipPathFromContentPath($newPath);
            if ($oldZipPath !== null && $newZipPath !== null) {
                $oldZipFullPath = $basePath . $oldZipPath;
                $newZipFullPath = $basePath . $newZipPath;
                if (is_file($oldZipFullPath) && !file_exists($newZipFullPath)) {
                    if (@rename($oldZipFullPath, $newZipFullPath)) {
                        $this->write(sprintf('    RENAMED ZIP: %s -> %s', $oldZipPath, $newZipPath));
                    }
                }
            }

            $this->connection->executeStatement(
                'UPDATE learning_scorm_package SET path = ? WHERE idscorm_package = ?',
                [$newPath, $row['idscorm_package']]
            );
        }
    }

    /**
     * Process learning_course_file: path column contains filenames
     */
    private function processCourseFiles(string $filesBasePath, string $subPath): void
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT id_file, path FROM learning_course_file WHERE path REGEXP '[^\x00-\x7F]'"
        );

        if (empty($rows)) {
            $this->write('  [course_file] No records with non-ASCII paths found.');
            return;
        }

        $this->write(sprintf('  [course_file] Found %d records with non-ASCII paths.', count($rows)));
        $basePath = $filesBasePath . $subPath;

        foreach ($rows as $row) {
            $this->renameFileAndUpdateRecord(
                $basePath,
                $row['path'],
                'learning_course_file',
                'path',
                'id_file',
                $row['id_file'],
                'course_file'
            );
        }
    }

    /**
     * Process learning_forummessage: attach column contains filenames
     */
    private function processForumMessages(string $filesBasePath, string $subPath): void
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT idMessage, attach FROM learning_forummessage WHERE attach != '' AND attach REGEXP '[^\x00-\x7F]'"
        );

        if (empty($rows)) {
            $this->write('  [forummessage] No records with non-ASCII attach found.');
            return;
        }

        $this->write(sprintf('  [forummessage] Found %d records with non-ASCII attach.', count($rows)));
        $basePath = $filesBasePath . $subPath;

        foreach ($rows as $row) {
            $this->renameFileAndUpdateRecord(
                $basePath,
                $row['attach'],
                'learning_forummessage',
                'attach',
                'idMessage',
                $row['idMessage'],
                'forummessage'
            );
        }
    }

    /**
     * Process learning_materials_lesson: path column contains filenames
     */
    private function processMaterialsLessons(string $filesBasePath, string $subPath): void
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT idLesson, path FROM learning_materials_lesson WHERE path REGEXP '[^\x00-\x7F]'"
        );

        if (empty($rows)) {
            $this->write('  [materials_lesson] No records with non-ASCII paths found.');
            return;
        }

        $this->write(sprintf('  [materials_lesson] Found %d records with non-ASCII paths.', count($rows)));
        $basePath = $filesBasePath . $subPath;

        foreach ($rows as $row) {
            $this->renameFileAndUpdateRecord(
                $basePath,
                $row['path'],
                'learning_materials_lesson',
                'path',
                'idLesson',
                $row['idLesson'],
                'materials_lesson'
            );
        }
    }

    /**
     * Process learning_testtrack_answer: more_info may contain filenames or error messages.
     * Filter for records that look like filenames (contain a file extension).
     */
    private function processTestTrackAnswers(string $filesBasePath, string $subPath): void
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT idTrack, idQuest, idAnswer, number_time, more_info
             FROM learning_testtrack_answer
             WHERE more_info REGEXP '[^\x00-\x7F]'
             AND more_info REGEXP '\.[a-zA-Z0-9]{2,10}$'"
        );

        if (empty($rows)) {
            $this->write('  [testtrack_answer] No records with non-ASCII filenames found.');
            return;
        }

        $this->write(sprintf('  [testtrack_answer] Found %d records with non-ASCII filenames.', count($rows)));
        $basePath = $filesBasePath . $subPath;

        foreach ($rows as $row) {
            $oldPath = $row['more_info'];
            $newPath = $this->sanitizePathSegment($oldPath);

            if ($oldPath === $newPath) {
                continue;
            }

            $oldFullPath = $basePath . $oldPath;
            $newFullPath = $basePath . $newPath;

            if (is_file($oldFullPath)) {
                if (file_exists($newFullPath)) {
                    $this->write(sprintf('    SKIP (target exists): %s -> %s', $oldPath, $newPath));
                    continue;
                }
                if (!@rename($oldFullPath, $newFullPath)) {
                    $this->write(sprintf('    ERROR (rename failed): %s -> %s', $oldPath, $newPath));
                    continue;
                }
                $this->write(sprintf('    RENAMED: %s -> %s', $oldPath, $newPath));
            } else {
                $this->write(sprintf('    SKIP (not found on disk): %s', $oldPath));
                continue;
            }

            $this->connection->executeStatement(
                'UPDATE learning_testtrack_answer SET more_info = ? WHERE idTrack = ? AND idQuest = ? AND idAnswer = ? AND number_time = ?',
                [$newPath, $row['idTrack'], $row['idQuest'], $row['idAnswer'], $row['number_time']]
            );
        }
    }

    /**
     * Generic helper: sanitize filename, rename on disk, update DB record.
     */
    private function renameFileAndUpdateRecord(
        string $basePath,
        string $oldPath,
        string $table,
        string $column,
        string $pkColumn,
        $pkValue,
        string $label
    ): void {
        $newPath = $this->sanitizePathSegment($oldPath);

        if ($oldPath === $newPath) {
            return;
        }

        $oldFullPath = $basePath . $oldPath;
        $newFullPath = $basePath . $newPath;

        if (is_file($oldFullPath)) {
            if (file_exists($newFullPath)) {
                $this->write(sprintf('    SKIP (target exists) [%s]: %s -> %s', $label, $oldPath, $newPath));
                return;
            }
            if (!@rename($oldFullPath, $newFullPath)) {
                $this->write(sprintf('    ERROR (rename failed) [%s]: %s -> %s', $label, $oldPath, $newPath));
                return;
            }
            $this->write(sprintf('    RENAMED [%s]: %s -> %s', $label, $oldPath, $newPath));
        } else {
            $this->write(sprintf('    SKIP (not found on disk) [%s]: %s', $label, $oldPath));
            return;
        }

        $this->connection->executeStatement(
            sprintf('UPDATE %s SET %s = ? WHERE %s = ?', $table, $column, $pkColumn),
            [$newPath, $pkValue]
        );
    }

    /**
     * Sanitize a filename path segment, preserving directory structure.
     * Replicates Util::sanitizeFilename() logic without depending on application classes.
     */
    private function sanitizePathSegment(string $path): string
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

    /**
     * Sanitize SCORM path which has format: "userid_rand_time_originalname.zip_content"
     * pathinfo sees "zip_content" as the extension and everything before as the filename.
     * We sanitize the filename part and preserve the extension (including underscore).
     */
    private function sanitizeScormPath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        // SCORM paths end with .zip_content - pathinfo handles this correctly:
        // pathinfo('1_42_123_età_test.zip_content') => extension='zip_content', filename='1_42_123_età_test'
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
            // Preserve underscores in extension (e.g. "zip_content")
            $extension = preg_replace('/[^a-zA-Z0-9_]/', '', $extension);

            return $name . '.' . $extension;
        }

        return $name;
    }

    /**
     * Derive the .zip file path from a _content directory path.
     * E.g. "1_42_123_test.zip_content" -> "1_42_123_test.zip"
     */
    private function getZipPathFromContentPath(string $contentPath): ?string
    {
        $suffix = '_content';
        if (substr($contentPath, -strlen($suffix)) === $suffix) {
            return substr($contentPath, 0, -strlen($suffix));
        }

        return null;
    }
}
