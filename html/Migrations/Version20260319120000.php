<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix stale paths in learning_scorm_package left behind by Version20260305084456.
 *
 * The original migration renamed filesystem directories with non-ASCII characters
 * and updated DB records one-by-one (WHERE idscorm_package = ?). When multiple
 * records shared the same path, only the first was updated — the directory was
 * renamed successfully, but subsequent records were skipped ("not found on disk")
 * because the old directory no longer existed under the original name.
 *
 * This migration fixes those orphaned records by computing the sanitized path
 * (same logic as the original) and updating all remaining non-ASCII paths.
 * No filesystem operations are needed — directories were already renamed.
 */
final class Version20260319120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix stale non-ASCII paths in learning_scorm_package left by Version20260305084456';
    }

    public function up(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT idscorm_package, path FROM learning_scorm_package WHERE path REGEXP '[^\x00-\x7F]'"
        );

        if (empty($rows)) {
            $this->write('  No records with stale non-ASCII paths remaining. Nothing to fix.');

            return;
        }

        $this->write(sprintf('  Found %d records with stale non-ASCII paths.', count($rows)));

        $fixed = 0;
        foreach ($rows as $row) {
            $oldPath = $row['path'];
            $newPath = $this->sanitizeScormPath($oldPath);

            if ($oldPath === $newPath) {
                continue;
            }

            $this->write(sprintf('    FIX [%d]: %s -> %s', $row['idscorm_package'], $oldPath, $newPath));

            $this->connection->executeStatement(
                'UPDATE learning_scorm_package SET path = ? WHERE idscorm_package = ?',
                [$newPath, $row['idscorm_package']]
            );
            ++$fixed;
        }

        $this->write(sprintf('  Fixed %d records.', $fixed));
    }

    public function down(Schema $schema): void
    {
        $this->write('WARNING: This migration cannot be automatically reverted.');
        $this->write('The original non-ASCII paths no longer exist on the filesystem.');
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
}
