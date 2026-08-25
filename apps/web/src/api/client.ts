import type { Game, Mode, ModesResponse, ProblemError } from './types.ts'

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const response = await fetch(path, {
    ...init,
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...init?.headers },
  })

  const body = await response.json()

  if (!response.ok) {
    throw body as ProblemError
  }

  return body as T
}

export function getCurrentGame(): Promise<Game> {
  return request<Game>('/api/games/current')
}

export function getModes(): Promise<ModesResponse> {
  return request<ModesResponse>('/api/games/modes')
}

export function startGame(mode: Mode): Promise<Game> {
  return request<Game>('/api/games', {
    method: 'POST',
    body: JSON.stringify({ mode }),
  })
}

export function submitGuess(
  latitude: number,
  longitude: number,
): Promise<Game> {
  return request<Game>('/api/games/guess', {
    method: 'POST',
    body: JSON.stringify({ latitude, longitude }),
  })
}

export function nextRound(): Promise<Game> {
  return request<Game>('/api/games/next', { method: 'POST' })
}
