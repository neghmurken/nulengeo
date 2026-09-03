export type LatLng = { latitude: number; longitude: number }

const EARTH_RADIUS_KM = 6371
const CIRCLE_STEPS = 64

/**
 * Point reached by travelling `distanceKm` from `origin` along `bearingDeg` (0 = north, clockwise),
 * using the spherical-earth destination-point formula.
 */
function destinationPoint(
  origin: LatLng,
  bearingDeg: number,
  distanceKm: number,
): LatLng {
  const angularDistance = distanceKm / EARTH_RADIUS_KM
  const bearing = (bearingDeg * Math.PI) / 180
  const lat1 = (origin.latitude * Math.PI) / 180
  const lon1 = (origin.longitude * Math.PI) / 180

  const lat2 = Math.asin(
    Math.sin(lat1) * Math.cos(angularDistance) +
      Math.cos(lat1) * Math.sin(angularDistance) * Math.cos(bearing),
  )
  const lon2 =
    lon1 +
    Math.atan2(
      Math.sin(bearing) * Math.sin(angularDistance) * Math.cos(lat1),
      Math.cos(angularDistance) - Math.sin(lat1) * Math.sin(lat2),
    )

  return {
    latitude: (lat2 * 180) / Math.PI,
    longitude: (((lon2 * 180) / Math.PI + 540) % 360) - 180,
  }
}

/**
 * Closed GeoJSON polygon ring approximating a circle of `radiusKm` centered on `center`.
 * Built from real geographic coordinates, so it renders at the correct ground size at any zoom.
 */
export function circlePolygon(
  center: LatLng,
  radiusKm: number,
): [number, number][] {
  const ring: [number, number][] = []

  for (let step = 0; step <= CIRCLE_STEPS; step++) {
    const bearing = (step * 360) / CIRCLE_STEPS
    const point = destinationPoint(center, bearing, radiusKm)
    ring.push([point.longitude, point.latitude])
  }

  return ring
}
