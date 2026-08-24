import { useContext, type Dispatch } from 'react'
import type { Game, Mode, ProblemError } from '../api/types.ts'
import { nextRound, startGame, submitGuess } from '../api/client.ts'
import {
  GameDispatchContext,
  GameStateContext,
  type GameAction,
} from './GameContext.ts'

export function useGame() {
  const state = useContext(GameStateContext)
  const maybeDispatch = useContext(GameDispatchContext)

  if (!state || !maybeDispatch) {
    throw new Error('useGame must be used within a GameProvider.')
  }

  const dispatch: Dispatch<GameAction> = maybeDispatch

  async function run(action: Promise<Game>) {
    try {
      dispatch({ type: 'game_loaded', game: await action })
    } catch (error) {
      dispatch({ type: 'error', error: error as ProblemError })
    }
  }

  return {
    state,
    startGame: (mode: Mode) => run(startGame(mode)),
    submitGuess: (latitude: number, longitude: number) =>
      run(submitGuess(latitude, longitude)),
    nextRound: () => run(nextRound()),
    returnToMenu: () => dispatch({ type: 'reset_to_menu' }),
  }
}
