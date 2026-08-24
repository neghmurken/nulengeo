import type { StyleSpecification } from '@maplibre/maplibre-gl-style-spec'

const AQUARELLE_SOURCE_IDS = [
  'land',
  'maptiler_planet_v4',
  'maptiler_attribution',
]
const TOPO_SOURCE_IDS = ['terrain_rgb', 'contours']

// Render order: background/land, then biome fills, then relief shading+contours
// (so terrain shading falls under the land, not the water), then water on top
// so lakes/rivers stay clean and unshaded.
const LAYER_ORDER: { id: string; from: 'aquarelle' | 'topo' }[] = [
  { id: 'Background', from: 'aquarelle' },
  { id: 'Land', from: 'aquarelle' },
  { id: 'Glacier', from: 'aquarelle' },
  { id: 'Forest', from: 'aquarelle' },
  { id: 'Sand', from: 'aquarelle' },
  { id: 'Grass', from: 'aquarelle' },
  { id: 'Wood', from: 'aquarelle' },
  { id: 'Hillshade', from: 'topo' },
  { id: 'Contour index', from: 'topo' },
  { id: 'Glacier contour index', from: 'topo' },
  { id: 'Contour', from: 'topo' },
  { id: 'Glacier contour', from: 'topo' },
  { id: 'Water outline', from: 'aquarelle' },
  { id: 'Water', from: 'aquarelle' },
  { id: 'Water intermittent', from: 'aquarelle' },
  { id: 'River', from: 'aquarelle' },
  { id: 'River intermittent', from: 'aquarelle' },
]

function pickSources(
  style: StyleSpecification,
  ids: string[],
): StyleSpecification['sources'] {
  const sources: StyleSpecification['sources'] = {}

  for (const id of ids) {
    const source = style.sources[id]
    if (!source) {
      throw new Error(
        `Source "${id}" not found in style "${style.name ?? '?'}".`,
      )
    }

    sources[id] = source
  }

  return sources
}

function findLayer(
  style: StyleSpecification,
  id: string,
): StyleSpecification['layers'][number] {
  const layer = style.layers.find((candidate) => candidate.id === id)

  if (!layer) {
    throw new Error(`Layer "${id}" not found in style "${style.name ?? '?'}".`)
  }

  return layer
}

/**
 * Merges aquarelle-v4's land/water/biome art style with topo-v2's relief
 * (hillshade + contours), dropping every road/building/boundary/label layer
 * from both — per the functional spec, only altitude/relief/water/biome may
 * remain visible.
 */
export function buildStyle(
  aquarelle: StyleSpecification,
  topo: StyleSpecification,
): StyleSpecification {
  const styles = { aquarelle, topo }

  return {
    version: aquarelle.version,
    sprite: aquarelle.sprite,
    glyphs: aquarelle.glyphs,
    sources: {
      ...pickSources(aquarelle, AQUARELLE_SOURCE_IDS),
      ...pickSources(topo, TOPO_SOURCE_IDS),
    },
    layers: LAYER_ORDER.map(({ id, from }) => findLayer(styles[from], id)),
  }
}
