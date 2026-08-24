import type { Game, Mode, ProblemError } from './types.ts'

async function request(path: string, init?: RequestInit): Promise<Game> {
  const response = await fetch(path, {
    ...init,
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...init?.headers },
  })

  const body = await response.json()

  if (!response.ok) {
    throw body as ProblemError
  }

  return body as Game
}

export function getCurrentGame(): Promise<Game> {
  return request('/api/games/current')
}

export function startGame(mode: Mode): Promise<Game> {
  return request('/api/games', {
    method: 'POST',
    body: JSON.stringify({ mode }),
  })
}

export function submitGuess(
  latitude: number,
  longitude: number,
): Promise<Game> {
  return request('/api/games/guess', {
    method: 'POST',
    body: JSON.stringify({ latitude, longitude }),
  })
}

export function nextRound(): Promise<Game> {
  return request('/api/games/next', { method: 'POST' })
}
