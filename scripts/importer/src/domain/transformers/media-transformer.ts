/**
 * Media & Document Transformers
 *
 * Pure data reshaping functions for audio/video URLs and documents.
 */

import { mapLanguageCode } from '../../utils/code-mappings.js';
import type {
  LegacyObjectVideo,
  ShLegacyObjectVideoAudio,
  ShLegacyObjectDocument,
  ThgLegacyExhibitionMedia,
} from '../types/index.js';

// ============================================================================
// Transformed Types
// ============================================================================

export interface TransformedItemMedia {
  data: {
    item_id: string; // placeholder — resolved by importer
    language_id: string | null;
    type: 'audio' | 'video';
    title: string;
    description: string | null;
    url: string;
    display_order: number;
    backward_compatibility: string;
  };
}

export interface TransformedCollectionMedia {
  data: {
    collection_id: string; // placeholder — resolved by importer
    language_id: string | null;
    type: 'audio' | 'video';
    title: string;
    description: string | null;
    url: string;
    display_order: number;
    extra: string | null;
    backward_compatibility: string;
  };
}

export interface TransformedItemDocument {
  data: {
    item_id: string; // placeholder — resolved by importer
    language_id: string | null;
    path: string;
    original_name: string;
    mime_type: string;
    size: number;
    title: string | null;
    display_order: number;
    extra: string | null;
    backward_compatibility: string;
  };
}

// ============================================================================
// mwnf3.objects_video / monuments_video → ItemMedia
// ============================================================================

export function transformObjectVideo(
  legacy: LegacyObjectVideo,
  entityType: 'objects' | 'monuments'
): TransformedItemMedia {
  const langId = mapLanguageCode(legacy.lang);
  const table = entityType === 'objects' ? 'objects_video' : 'monuments_video';
  return {
    data: {
      item_id: '', // resolved by importer
      language_id: langId,
      type: 'video',
      title: legacy.video_title,
      description: legacy.video_description || null,
      url: legacy.video_url,
      display_order: legacy.video_id,
      backward_compatibility: `mwnf3:${table}:${legacy.project_id}:${legacy.country}:${legacy.museum_id}:${legacy.number}:${legacy.lang}:${legacy.video_id}`,
    },
  };
}

// ============================================================================
// sh.sh_objects_video_audio → ItemMedia
// ============================================================================

export function transformShObjectVideoAudio(
  legacy: ShLegacyObjectVideoAudio
): TransformedItemMedia {
  return {
    data: {
      item_id: '', // resolved by importer
      language_id: null, // SH media has no language column
      type: legacy.type,
      title: legacy.title,
      description: null, // SH schema has no description column
      url: normalizeVideoUrl(legacy.path),
      display_order: legacy.id,
      backward_compatibility: `mwnf3_sharing_history:sh_objects_video_audio:${legacy.id}`,
    },
  };
}

// ============================================================================
// THG exhibition_audio/video + theme_audio/video → CollectionMedia
// ============================================================================

export function transformThgThemeMedia(
  exhibition: ThgLegacyExhibitionMedia,
  themeId: number,
  sortOrder: number,
  overviewPage: 'Y' | 'N',
  mediaType: 'audio' | 'video'
): TransformedCollectionMedia {
  const langId = mapLanguageCode(exhibition.lang);
  const table = mediaType === 'audio' ? 'theme_audio' : 'theme_video';
  const extra = JSON.stringify({ overview_page: overviewPage === 'Y' });
  return {
    data: {
      collection_id: '', // resolved by importer
      language_id: langId,
      type: mediaType,
      title: exhibition.title,
      description: exhibition.description || null,
      url: exhibition.url,
      display_order: sortOrder,
      extra,
      backward_compatibility: `mwnf3_thematic_gallery:${table}:${exhibition.media_id}:${exhibition.gallery_id}:${themeId}`,
    },
  };
}

// ============================================================================
// sh.sh_objects_document → ItemDocument
// ============================================================================

export function transformShObjectDocument(legacy: ShLegacyObjectDocument): TransformedItemDocument {
  const langId = mapLanguageCode(legacy.lang);
  const extra = JSON.stringify({ img_count: legacy.img_count });
  const originalName = legacy.path.split('/').pop() || legacy.path;
  return {
    data: {
      item_id: '', // resolved by importer
      language_id: langId,
      path: legacy.path,
      original_name: originalName,
      mime_type: 'application/pdf',
      size: 1, // placeholder — resolved by DocumentSyncTool
      title: null,
      display_order: legacy.img_count,
      extra,
      backward_compatibility: `mwnf3_sharing_history:sh_objects_document:${legacy.project_id}:${legacy.country}:${legacy.number}:${legacy.lang}:${legacy.img_count}`,
    },
  };
}

// ============================================================================
// Helpers
// ============================================================================

/**
 * Normalize video URL — SH stores YouTube video IDs or full URLs.
 * Ensure we always store a full URL.
 */
function normalizeVideoUrl(path: string): string {
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  // Assume it's a YouTube video ID
  return `https://www.youtube.com/watch?v=${path}`;
}
