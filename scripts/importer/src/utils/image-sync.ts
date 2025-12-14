/**
 * Image Synchronization Utilities
 *
 * Utilities for copying or symlinking legacy images to the new storage structure.
 * Handles path normalization, file operations, and metadata extraction.
 */

import { promises as fs } from 'fs';
import path from 'path';

/**
 * Normalize a path by removing leading slashes and backslashes
 * Both "/x/y/z.jpg" and "x/y/z.jpg" should become "x/y/z.jpg"
 */
export function normalizePath(imagePath: string): string {
  return imagePath.replace(/^[/\\]+/, '');
}

/**
 * Get file extension from a path (including the dot)
 * e.g., "/path/to/image.jpg" returns ".jpg"
 */
export function getFileExtension(filePath: string): string {
  return path.extname(filePath).toLowerCase();
}

/**
 * Build the full legacy image path
 */
export function getLegacyImagePath(legacyRoot: string, relativePath: string): string {
  const normalized = normalizePath(relativePath);
  return path.join(legacyRoot, normalized);
}

/**
 * Build the new storage path using UUID
 */
export function getNewImagePath(storageRoot: string, uuid: string, extension: string): string {
  // Ensure extension starts with a dot
  const ext = extension.startsWith('.') ? extension : `.${extension}`;
  return path.join(storageRoot, `${uuid}${ext}`);
}

/**
 * Copy a file from source to destination
 */
export async function copyFile(sourcePath: string, destPath: string): Promise<void> {
  await fs.copyFile(sourcePath, destPath);
}

/**
 * Create a symbolic link from source to destination
 * On Windows, requires admin privileges or developer mode
 */
export async function symlinkFile(sourcePath: string, destPath: string): Promise<void> {
  // Resolve to absolute paths
  const absoluteSource = path.resolve(sourcePath);
  const absoluteDest = path.resolve(destPath);

  // Remove existing file/link if it exists
  try {
    await fs.unlink(absoluteDest);
  } catch {
    // File doesn't exist, that's fine
  }

  // Create symlink (use 'file' type for cross-platform compatibility)
  await fs.symlink(absoluteSource, absoluteDest, 'file');
}

/**
 * Get file size in bytes
 */
export async function getFileSize(filePath: string): Promise<number> {
  const stats = await fs.stat(filePath);
  return stats.size;
}

/**
 * Check if a file exists
 */
export async function fileExists(filePath: string): Promise<boolean> {
  try {
    await fs.access(filePath);
    return true;
  } catch {
    return false;
  }
}

/**
 * Ensure a directory exists, creating it if necessary
 */
export async function ensureDirectory(dirPath: string): Promise<void> {
  try {
    await fs.mkdir(dirPath, { recursive: true });
  } catch (error) {
    // Ignore if directory already exists
    if ((error as NodeJS.ErrnoException).code !== 'EEXIST') {
      throw error;
    }
  }
}
