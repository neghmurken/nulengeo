import { useEffect, useRef } from 'react'
import { LngLatBounds, MapLibreMap, Marker, type LngLatLike } from 'maplibre-gl'
import type { StyleSpecification } from '@maplibre/maplibre-gl-style-spec'
import 'maplibre-gl/dist/maplibre-gl.css'
import { buildStyle } from './buildStyle.ts'

const FRANCE_CENTER: [number, number] = [2.5, 46.6]
const FRANCE_ZOOM = 5

const GUESS_MARKER_COLOR = '#d9780b'
const ACTUAL_MARKER_COLOR = '#dd5b61'
const GUESS_LINE_SOURCE_ID = 'guess-line'
const FIT_BOUNDS_PADDING = 80

const MAPTILER_KEY = import.meta.env.VITE_MAPTILER_KEY
const AQUARELLE_STYLE_URL = `https://api.maptiler.com/maps/aquarelle-v4/style.json?key=${MAPTILER_KEY}`
const TOPO_STYLE_URL = `https://api.maptiler.com/maps/topo-v2/style.json?key=${MAPTILER_KEY}`

async function fetchStyle(url: string): Promise<StyleSpecification> {
  const response = await fetch(url)
  return (await response.json()) as StyleSpecification
}

export type LatLng = { latitude: number; longitude: number }

type MapViewProps = {
  onGuessChange: (guess: LatLng) => void
  actualPosition?: LatLng
}

export function MapView({ onGuessChange, actualPosition }: MapViewProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const mapRef = useRef<MapLibreMap | null>(null)
  const guessMarkerRef = useRef<Marker | null>(null)
  const answeredRef = useRef(false)

  useEffect(() => {
    if (!containerRef.current) {
      return
    }

    let cancelled = false

    function placeMarker(lngLat: LngLatLike) {
      const map = mapRef.current
      if (!map || answeredRef.current) {
        return
      }

      if (guessMarkerRef.current) {
        guessMarkerRef.current.setLngLat(lngLat)
      } else {
        const marker = new Marker({
          color: GUESS_MARKER_COLOR,
          draggable: true,
        })
          .setLngLat(lngLat)
          .addTo(map)
        marker.on('dragend', () => {
          const position = marker.getLngLat()
          onGuessChange({ latitude: position.lat, longitude: position.lng })
        })
        guessMarkerRef.current = marker
      }

      const position = guessMarkerRef.current.getLngLat()
      onGuessChange({ latitude: position.lat, longitude: position.lng })
    }

    Promise.all([
      fetchStyle(AQUARELLE_STYLE_URL),
      fetchStyle(TOPO_STYLE_URL),
    ]).then(([aquarelle, topo]) => {
      if (cancelled || !containerRef.current) {
        return
      }

      const map = new MapLibreMap({
        container: containerRef.current,
        style: buildStyle(aquarelle, topo),
        center: FRANCE_CENTER,
        zoom: FRANCE_ZOOM,
      })

      map.doubleClickZoom.disable()
      map.on('dblclick', (event) => placeMarker(event.lngLat))

      mapRef.current = map
    })

    return () => {
      cancelled = true
      mapRef.current?.remove()
      mapRef.current = null
      guessMarkerRef.current = null
      answeredRef.current = false
    }
  }, [onGuessChange])

  useEffect(() => {
    const map = mapRef.current
    const guessPosition = guessMarkerRef.current?.getLngLat()

    if (!actualPosition || !map || !guessPosition) {
      return
    }

    answeredRef.current = true
    guessMarkerRef.current?.setDraggable(false)

    const actualLngLat: [number, number] = [
      actualPosition.longitude,
      actualPosition.latitude,
    ]
    const guessLngLat: [number, number] = [guessPosition.lng, guessPosition.lat]

    new Marker({ color: ACTUAL_MARKER_COLOR })
      .setLngLat(actualLngLat)
      .addTo(map)

    map.addSource(GUESS_LINE_SOURCE_ID, {
      type: 'geojson',
      data: {
        type: 'Feature',
        properties: {},
        geometry: {
          type: 'LineString',
          coordinates: [guessLngLat, actualLngLat],
        },
      },
    })
    map.addLayer({
      id: GUESS_LINE_SOURCE_ID,
      type: 'line',
      source: GUESS_LINE_SOURCE_ID,
      paint: {
        'line-color': '#1f2937',
        'line-width': 2,
        'line-dasharray': [2, 2],
      },
    })

    const bounds = new LngLatBounds()
    bounds.extend(guessLngLat)
    bounds.extend(actualLngLat)
    map.fitBounds(bounds, { padding: FIT_BOUNDS_PADDING })
  }, [actualPosition])

  return <div ref={containerRef} style={{ width: '100%', height: '100svh' }} />
}
