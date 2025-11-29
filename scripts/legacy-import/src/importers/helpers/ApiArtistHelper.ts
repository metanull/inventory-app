/* eslint-disable @typescript-eslint/no-explicit-any */
import type { InventoryApiClient } from '../../api/InventoryApiClient.js';
import type { BackwardCompatibilityTracker } from '../../utils/BackwardCompatibilityTracker.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

/**
 * Helper class for managing Artist entities through the API
 * Handles find-or-create logic with backward compatibility tracking
 *
 * Matches the pattern of SQL importer's ArtistHelper but uses API calls
 */
export class ApiArtistHelper {
  private apiClient: InventoryApiClient;
  private tracker: BackwardCompatibilityTracker;

  constructor(apiClient: InventoryApiClient, tracker: BackwardCompatibilityTracker) {
    this.apiClient = apiClient;
    this.tracker = tracker;
  }

  /**
   * Find or create Artist records from the artist field
   * Artists field can contain multiple artists separated by semicolons
   * @param artistField The artist field (may contain multiple semicolon-separated artists)
   * @param birthplace Birth place (optional)
   * @param deathplace Death place (optional)
   * @param birthdate Birth date (optional)
   * @param deathdate Death date (optional)
   * @param periodActivity Period of activity (optional)
   * @returns Array of artist UUIDs
   */
  async findOrCreateList(
    artistField: string | null,
    birthplace: string | null | undefined,
    deathplace: string | null | undefined,
    birthdate: string | null | undefined,
    deathdate: string | null | undefined,
    periodActivity: string | null | undefined
  ): Promise<string[]> {
    if (!artistField || artistField.trim() === '') {
      return [];
    }

    // Split by semicolon to handle multiple artists
    const artistNames = artistField
      .split(';')
      .map((name) => name.trim())
      .filter((name) => name !== '');

    const artistIds: string[] = [];

    for (const artistName of artistNames) {
      const artistId = await this.findOrCreate(
        artistName,
        birthplace,
        deathplace,
        birthdate,
        deathdate,
        periodActivity
      );
      if (artistId) {
        artistIds.push(artistId);
      }
    }

    return artistIds;
  }

  /**
   * Find or create a single Artist record
   * @param artistName The artist's name
   * @param birthplace Birth place (optional)
   * @param deathplace Death place (optional)
   * @param birthdate Birth date (optional)
   * @param deathdate Death date (optional)
   * @param periodActivity Period of activity (optional)
   * @returns Artist UUID or null if creation fails
   */
  async findOrCreate(
    artistName: string,
    birthplace: string | null | undefined,
    deathplace: string | null | undefined,
    birthdate: string | null | undefined,
    deathdate: string | null | undefined,
    periodActivity: string | null | undefined
  ): Promise<string | null> {
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'artists',
      pkValues: [artistName],
    });

    // Check if already exists in tracker
    const existingId = this.tracker.getUuid(backwardCompat);
    if (existingId) {
      return existingId;
    }

    // Create new artist
    const response = await (this.apiClient as any).artist.artistStore({
      name: artistName,
      internal_name: artistName,
      place_of_birth: birthplace || null,
      place_of_death: deathplace || null,
      date_of_birth: birthdate || null,
      date_of_death: deathdate || null,
      period_of_activity: periodActivity || null,
      backward_compatibility: backwardCompat,
    });

    const artistId = response.data.data.id;

    // Register in tracker
    this.tracker.register({
      uuid: artistId,
      backwardCompatibility: backwardCompat,
      entityType: 'item' as any,
      createdAt: new Date(),
    });

    return artistId;
  }

  /**
   * Attach artists to an item using the artist_item pivot table
   * @param itemId The item UUID
   * @param artistIds Array of artist UUIDs
   */
  async attachToItem(itemId: string, artistIds: string[]): Promise<void> {
    if (artistIds.length === 0) {
      return;
    }

    // Use the updateArtists endpoint to attach artists
    await (this.apiClient.item as any).itemUpdateArtists(itemId, {
      attach: artistIds,
    });
  }
}
