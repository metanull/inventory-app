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
  collectionTranslation: Omit<CollectionTranslationData, 'collection_id' | 'context_id'>;
  projectTranslation: Omit<ProjectTranslationData, 'project_id' | 'context_id'>;
  languageId: string;
}

/**
 * Transform a legacy project to context + collection + project bundle
 */
export function transformProject(legacy: LegacyProject, defaultLanguageId: string): TransformedProjectBundle {
  const baseBackwardCompat = formatBackwardCompatibility({
    schema: 'mwnf3',
    table: 'projects',
    pkValues: [legacy.project_id],
  });

  // Context - internal_name must always be legacy.project_id - no fallback
  if (!legacy.project_id) {
    throw new Error('Project missing required project_id field');
  }
  const contextBackwardCompat = baseBackwardCompat;
  const contextData: ContextData = {
    internal_name: legacy.project_id,
    backward_compatibility: contextBackwardCompat,
    is_default: false,
  };

  // Collection (root collection for project) - internal_name derived from required legacy.project_id
  // Use same backward_compatibility as context - tracker composite key (entityType:backwardCompat) handles uniqueness
  const collectionBackwardCompat = baseBackwardCompat;
  const collectionData: Omit<CollectionData, 'context_id'> = {
    internal_name: `${legacy.project_id}_collection`,
    backward_compatibility: collectionBackwardCompat,
    parent_id: null,
    language_id: defaultLanguageId,
  };

  // Project - use same backward_compatibility as context
  const projectBackwardCompat = baseBackwardCompat;
  const projectData: Omit<ProjectData, 'context_id'> = {
    internal_name: legacy.project_id,
    backward_compatibility: projectBackwardCompat,
    language_id: defaultLanguageId,
    launch_date: legacy.launchdate || null,
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

  // Translation name is required - no fallback
  if (!legacy.name) {
    throw new Error(`Project translation ${legacy.project_id}:${legacy.lang} missing required name field`);
  }
  const name = convertHtmlToMarkdown(legacy.name);
  const description = legacy.description ? convertHtmlToMarkdown(legacy.description) : null;

  return {
    contextTranslation: {
      language_id: languageId,
      name: name,
      description: description,
    },
    collectionTranslation: {
      language_id: languageId,
      title: name,
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
