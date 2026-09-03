import { describe, expect, it } from 'vitest'
import { circlePolygon, type LatLng } from './geo.ts'

const EARTH_RADIUS_KM = 6371

function haversineDistanceKm(a: LatLng, b: LatLng): number {
  const lat1 = (a.latitude * Math.PI) / 180
  const lat2 = (b.latitude * Math.PI) / 180
  const deltaLat = ((b.latitude - a.latitude) * Math.PI) / 180
  const deltaLon = ((b.longitude - a.longitude) * Math.PI) / 180

  const h =
    Math.sin(deltaLat / 2) ** 2 +
    Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLon / 2) ** 2

  return 2 * EARTH_RADIUS_KM * Math.asin(Math.sqrt(h))
}

describe('circlePolygon', () => {
  const center: LatLng = { latitude: 46.6, longitude: 2.5 }

  it('returns a closed ring', () => {
    const ring = circlePolygon(center, 10)

    expect(ring[0]).toEqual(ring[ring.length - 1])
  })

  it('places every point at the given radius from the center', () => {
    const radiusKm = 10
    const ring = circlePolygon(center, radiusKm)

    for (const [longitude, latitude] of ring) {
      expect(haversineDistanceKm(center, { latitude, longitude })).toBeCloseTo(
        radiusKm,
        1,
      )
    }
  })
})
