export type Mode = string

export type ModesResponse = {
  modes: Mode[]
}

export type Coordinates = {
  latitude: number
  longitude: number
}

export type IdleGame = {
  status: 'idle'
}

export type PlayingGame = {
  status: 'playing'
  mode: Mode
  round: number
  totalRounds: number
  city: { name: string; population: number }
  runningScore: number
  guess?: Coordinates
  actual?: Coordinates
  distanceKm?: number
  score?: number
}

export type FinishedGame = {
  status: 'finished'
  totalScore: number
  maxScore: number
  results: {
    inseeCode: string
    name: string
    distanceKm: number
    score: number
  }[]
}

export type Game = IdleGame | PlayingGame | FinishedGame

export type ProblemError = {
  type: string
  title: string
  status: number
}
