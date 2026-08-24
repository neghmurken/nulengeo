import { useEffect, useReducer, type ReactNode } from 'react'
import type { ProblemError } from '../api/types.ts'
import { getCurrentGame } from '../api/client.ts'
import {
  GameDispatchContext,
  GameStateContext,
  reducer,
} from './GameContext.ts'

export function GameProvider({ children }: { children: ReactNode }) {
  const [state, dispatch] = useReducer(reducer, { status: 'loading' })

  useEffect(() => {
    getCurrentGame()
      .then((game) => dispatch({ type: 'game_loaded', game }))
      .catch((error: ProblemError) => dispatch({ type: 'error', error }))
  }, [])

  return (
    <GameStateContext.Provider value={state}>
      <GameDispatchContext.Provider value={dispatch}>
        {children}
      </GameDispatchContext.Provider>
    </GameStateContext.Provider>
  )
}
