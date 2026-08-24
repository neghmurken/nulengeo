import { createContext, type Dispatch } from 'react'
import type { Game, ProblemError } from '../api/types.ts'

export type GameState =
  | { status: 'loading' }
  | { status: 'ready'; game: Game }
  | { status: 'error'; error: ProblemError }

export type GameAction =
  | { type: 'game_loaded'; game: Game }
  | { type: 'error'; error: ProblemError }
  | { type: 'reset_to_menu' }

export function reducer(_state: GameState, action: GameAction): GameState {
  switch (action.type) {
    case 'game_loaded':
      return { status: 'ready', game: action.game }
    case 'error':
      return { status: 'error', error: action.error }
    case 'reset_to_menu':
      return { status: 'ready', game: { status: 'idle' } }
  }
}

export const GameStateContext = createContext<GameState | null>(null)
export const GameDispatchContext = createContext<Dispatch<GameAction> | null>(
  null,
)
