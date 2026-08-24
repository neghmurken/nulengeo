import { useEffect, useRef } from 'react'
import { MapLibreMap, Marker, type LngLatLike } from 'maplibre-gl'
import type { StyleSpecification } from '@maplibre/maplibre-gl-style-spec'
import 'maplibre-gl/dist/maplibre-gl.css'
import { buildStyle } from './buildStyle.ts'

const FRANCE_CENTER: [number, number] = [2.5, 46.6]
const FRANCE_ZOOM = 5

const MAPTILER_KEY = import.meta.env.VITE_MAPTILER_KEY
const AQUARELLE_STYLE_URL = `https://api.maptiler.com/maps/aquarelle-v4/style.json?key=${MAPTILER_KEY}`
const TOPO_STYLE_URL = `https://api.maptiler.com/maps/topo-v2/style.json?key=${MAPTILER_KEY}`

async function fetchStyle(url: string): Promise<StyleSpecification> {
  const response = await fetch(url)
  return (await response.json()) as StyleSpecification
}

export type Guess = { latitude: number; longitude: number }

type MapViewProps = {
  onGuessChange: (guess: Guess) => void
}

export function MapView({ onGuessChange }: MapViewProps) {
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    if (!containerRef.current) {
      return
    }

    let map: MapLibreMap | undefined
    let marker: Marker | undefined
    let cancelled = false

    function placeMarker(lngLat: LngLatLike) {
      if (!map) {
        return
      }

      if (marker) {
        marker.setLngLat(lngLat)
      } else {
        marker = new Marker({ draggable: true }).setLngLat(lngLat).addTo(map)
        marker.on('dragend', () => {
          const position = marker!.getLngLat()
          onGuessChange({ latitude: position.lat, longitude: position.lng })
        })
      }

      const position = marker.getLngLat()
      onGuessChange({ latitude: position.lat, longitude: position.lng })
    }

    Promise.all([
      fetchStyle(AQUARELLE_STYLE_URL),
      fetchStyle(TOPO_STYLE_URL),
    ]).then(([aquarelle, topo]) => {
      if (cancelled || !containerRef.current) {
        return
      }

      map = new MapLibreMap({
        container: containerRef.current,
        style: buildStyle(aquarelle, topo),
        center: FRANCE_CENTER,
        zoom: FRANCE_ZOOM,
      })

      map.doubleClickZoom.disable()
      map.on('dblclick', (event) => placeMarker(event.lngLat))
    })

    return () => {
      cancelled = true
      map?.remove()
    }
  }, [onGuessChange])

  return <div ref={containerRef} style={{ width: '100%', height: '100svh' }} />
}
