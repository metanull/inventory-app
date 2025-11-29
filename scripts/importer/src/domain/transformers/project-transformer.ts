/**
 * Project Transformer
 *
 * Transforms legacy project data to the new format.
 * Creates Context, Collection, and Project entities.
 * This is pure business logic with no dependencies on write strategy.
 */

import type { LegacyProject, LegacyProjectName } from '../types/index.js';
import type {
  ContextData,
  ContextTranslationData,
  CollectionData,
  CollectionTranslationData,
  ProjectData,
  ProjectTranslationData,
} from '../../core/types.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';

/**
 * Transformed project bundle (context + collection + project)
 */
export interface TransformedProjectBundle {
  context: {
    data: ContextData;
    backwardCompatibility: string;
  };
  collection: {
    data: Omit<CollectionData, 'context_id'>; // context_id added after context creation
    backwardCompatibility: string;
  };
  project: {
    data: Omit<ProjectData, 'context_id'>; // context_id added after context creation
    backwardCompatibility: string;
  };
}

/**
 * Transformed project translation bundle
 */
export interface TransformedProjectTranslationBundle {
  contextTranslation: Omit<ContextTranslationData, 'context_id'>;
  collectionTranslation: Omit<CollectionTranslationData, 'collection_id'>;
  projectTranslation: Omit<ProjectTranslationData, 'project_id' | 'context_id'>;
  languageId: string;
}

/**
 * Transform a legacy project to context + collection + project bundle
 */
export function transformProject(legacy: LegacyProject): TransformedProjectBundle {
  const baseBackwardCompat = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'projects',
    pkValues: [legacy.project_id],
  });

  // Context
  const contextBackwardCompat = baseBackwardCompat;
  const contextData: ContextData = {
    internal_name: legacy.project_id,
    backward_compatibility: contextBackwardCompat,
    is_default: false,
  };

  // Collection (root collection for project)
  const collectionBackwardCompat = `${baseBackwardCompat}:collection`;
  const collectionData: Omit<CollectionData, 'context_id'> = {
    internal_name: `${legacy.project_id}_collection`,
    backward_compatibility: collectionBackwardCompat,
    parent_id: null,
  };

  // Project
  const projectBackwardCompat = `${baseBackwardCompat}:project`;
  const projectData: Omit<ProjectData, 'context_id'> = {
    internal_name: legacy.project_id,
    backward_compatibility: projectBackwardCompat,
    start_date: legacy.start_date || null,
    end_date: legacy.end_date || null,
    is_launched: legacy.active === 1 || legacy.active === true,
  };

  return {
    context: { data: contextData, backwardCompatibility: contextBackwardCompat },
    collection: { data: collectionData, backwardCompatibility: collectionBackwardCompat },
    project: { data: projectData, backwardCompatibility: projectBackwardCompat },
  };
}

/**
 * Transform a legacy project name to translations bundle
 */
export function transformProjectTranslation(
  legacy: LegacyProjectName
): TransformedProjectTranslationBundle {
  const languageId = mapLanguageCode(legacy.lang);

  const name = legacy.name ? convertHtmlToMarkdown(legacy.name) : legacy.project_id;
  const description = legacy.description ? convertHtmlToMarkdown(legacy.description) : null;

  return {
    contextTranslation: {
      language_id: languageId,
      name: name,
      description: description,
    },
    collectionTranslation: {
      language_id: languageId,
      name: name,
      description: description,
    },
    projectTranslation: {
      language_id: languageId,
      name: name,
      description: description,
    },
    languageId,
  };
}
