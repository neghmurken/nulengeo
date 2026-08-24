import { describe, expect, it } from 'vitest'
import type { StyleSpecification } from '@maplibre/maplibre-gl-style-spec'
import { buildStyle } from './buildStyle.ts'

function makeStyle(overrides: Partial<StyleSpecification>): StyleSpecification {
  return {
    version: 8,
    sources: {},
    layers: [],
    ...overrides,
  }
}

const aquarelle = makeStyle({
  sprite: 'https://example.com/sprite',
  glyphs: 'https://example.com/glyphs/{fontstack}/{range}.pbf',
  sources: {
    land: { type: 'vector', url: 'https://example.com/land' },
    maptiler_planet_v4: { type: 'vector', url: 'https://example.com/planet' },
    maptiler_attribution: {
      type: 'vector',
      url: 'https://example.com/attribution',
    },
  },
  layers: [
    { id: 'Background', type: 'background' },
    { id: 'Land', type: 'fill', source: 'land' },
    { id: 'Glacier', type: 'fill', source: 'maptiler_planet_v4' },
    { id: 'Forest', type: 'fill', source: 'maptiler_planet_v4' },
    { id: 'Sand', type: 'fill', source: 'maptiler_planet_v4' },
    { id: 'Grass', type: 'fill', source: 'maptiler_planet_v4' },
    { id: 'Wood', type: 'fill', source: 'maptiler_planet_v4' },
    { id: 'Water outline', type: 'line', source: 'maptiler_planet_v4' },
    { id: 'Water', type: 'fill', source: 'maptiler_planet_v4' },
    { id: 'Water intermittent', type: 'fill', source: 'maptiler_planet_v4' },
    { id: 'River', type: 'line', source: 'maptiler_planet_v4' },
    { id: 'River intermittent', type: 'line', source: 'maptiler_planet_v4' },
    { id: 'Road network', type: 'line', source: 'maptiler_planet_v4' },
    { id: 'Building', type: 'fill', source: 'maptiler_planet_v4' },
  ],
})

const topo = makeStyle({
  sources: {
    terrain_rgb: { type: 'raster-dem', url: 'https://example.com/terrain' },
    contours: { type: 'vector', url: 'https://example.com/contours' },
    maptiler_planet: {
      type: 'vector',
      url: 'https://example.com/other-planet',
    },
  },
  layers: [
    { id: 'Hillshade', type: 'hillshade', source: 'terrain_rgb' },
    { id: 'Contour index', type: 'line', source: 'contours' },
    { id: 'Glacier contour index', type: 'line', source: 'contours' },
    { id: 'Contour', type: 'line', source: 'contours' },
    { id: 'Glacier contour', type: 'line', source: 'contours' },
    { id: 'Contour labels', type: 'symbol', source: 'contours' },
    { id: 'Place labels', type: 'symbol', source: 'maptiler_planet' },
  ],
})

describe('buildStyle', () => {
  it('merges land/water/biome with relief, dropping roads/buildings/labels', () => {
    const merged = buildStyle(aquarelle, topo)

    expect(Object.keys(merged.sources)).toEqual([
      'land',
      'maptiler_planet_v4',
      'maptiler_attribution',
      'terrain_rgb',
      'contours',
    ])
    expect(merged.sprite).toBe('https://example.com/sprite')
    expect(merged.layers.map((layer) => layer.id)).toEqual([
      'Background',
      'Land',
      'Glacier',
      'Forest',
      'Sand',
      'Grass',
      'Wood',
      'Hillshade',
      'Contour index',
      'Glacier contour index',
      'Contour',
      'Glacier contour',
      'Water outline',
      'Water',
      'Water intermittent',
      'River',
      'River intermittent',
    ])
  })

  it('throws when an expected layer is missing from an upstream style', () => {
    const incompleteTopo = makeStyle({ sources: topo.sources })

    expect(() => buildStyle(aquarelle, incompleteTopo)).toThrow(
      'Layer "Hillshade" not found',
    )
  })

  it('throws when an expected source is missing from an upstream style', () => {
    const incompleteAquarelle = makeStyle({ layers: aquarelle.layers })

    expect(() => buildStyle(incompleteAquarelle, topo)).toThrow(
      'Source "land" not found',
    )
  })
})
